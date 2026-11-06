import { Hono } from 'hono';
import { getCookie, setCookie, deleteCookie } from 'hono/cookie';
import { HTTPException } from 'hono/http-exception';
import { digest, equal, hashPassword, randomToken, today, clock } from './auth.js';
const app = new Hono();
const fail = (message, status = 400) => { throw new HTTPException(status, {message}); };
const text = (value, max) => typeof value === 'string' && value.trim().length > 0 && value.trim().length <= max ? value.trim() : fail('Data tidak valid.');
const id = value => /^\d+$/.test(String(value)) && Number.isSafeInteger(Number(value)) && Number(value) > 0 ? Number(value) : fail('ID tidak valid.');
const credentials = b => {
  const username = text(b.username, 50);
  if (username.length < 3 || typeof b.password !== 'string' || b.password.length < 12 || b.password.length > 128) fail('Username minimal 3 karakter; password 12–128 karakter.');
  return {username, password: b.password};
};
app.onError((err, c) => {
  if (err instanceof HTTPException) return c.json({error: err.message}, err.status);
  console.error('Request failed:', err.message);
  return c.json({error: 'Terjadi kesalahan server.'}, 500);
});
app.use('*', async (c, next) => {
  c.header('X-Content-Type-Options', 'nosniff');
  c.header('Referrer-Policy', 'same-origin');
  c.header('Content-Security-Policy', "default-src 'self'; script-src 'self'; style-src 'self'; img-src 'self' data:; frame-ancestors 'none'; base-uri 'none'; form-action 'self'");
  if (c.req.path.startsWith('/api/')) {
    c.header('Cache-Control', 'no-store');
    if (!['GET', 'HEAD'].includes(c.req.method)) {
      if (c.req.header('Origin') !== new URL(c.req.url).origin) fail('Asal permintaan tidak valid.', 403);
      if (!c.req.header('Content-Type')?.startsWith('application/json')) fail('Gunakan JSON.', 415);
      const body = await c.req.text();
      if (body.length > 100000) fail('Permintaan terlalu besar.', 413);
      try { const data = JSON.parse(body); if (!data || Array.isArray(data) || typeof data !== 'object') fail('JSON tidak valid.'); c.set('body', data); }
      catch { fail('JSON tidak valid.'); }
    }
  }
  await next();
});
app.post('/api/setup', async c => {
  if (!c.env.SETUP_TOKEN || c.env.SETUP_TOKEN.length < 32) fail('Setup belum dikonfigurasi.', 503);
  const b = c.get('body');
  if (!equal(b.token, c.env.SETUP_TOKEN)) fail('Token setup salah.', 403);
  const {username, password} = credentials(b);
  const hash = await hashPassword(password);
  const result = await c.env.DB.prepare('INSERT INTO admins (id, username, password_hash) SELECT 1, ?, ? WHERE NOT EXISTS (SELECT 1 FROM admins) ON CONFLICT DO NOTHING').bind(username, hash).run();
  if (!result.meta.changes) fail('Admin sudah tersedia.', 409);
  return c.json({message: 'Admin berhasil dibuat. Silakan login.'}, 201);
});
app.post('/api/login', async c => {
  const b = c.get('body');
  const {username, password} = credentials(b);
  const now = Date.now();
  // Limit by client IP, not user input, so rotating usernames cannot bypass the limit.
  const key = await digest(c.req.header('CF-Connecting-IP') || 'local');
  await c.env.DB.prepare('INSERT INTO login_attempts (key, attempts, reset_at) VALUES (?, 1, ?) ON CONFLICT(key) DO UPDATE SET attempts = CASE WHEN reset_at < ? THEN 1 ELSE attempts + 1 END, reset_at = CASE WHEN reset_at < ? THEN excluded.reset_at ELSE reset_at END').bind(key, now + 900000, now, now).run();
  const limit = await c.env.DB.prepare('SELECT attempts FROM login_attempts WHERE key = ?').bind(key).first();
  if (limit.attempts > 10) fail('Terlalu banyak percobaan. Tunggu 15 menit.', 429);
  const admin = await c.env.DB.prepare('SELECT * FROM admins WHERE username = ?').bind(username).first();
  const hash = await hashPassword(password, admin?.password_hash.split(':')[0] || 'dummy-salt');
  if (!admin || !equal(hash, admin.password_hash)) fail('Username atau password salah.', 401);
  const token = randomToken();
  await c.env.DB.batch([
    c.env.DB.prepare('DELETE FROM sessions WHERE expires_at < ?').bind(now),
    c.env.DB.prepare('INSERT INTO sessions VALUES (?, ?, ?)').bind(await digest(token), admin.id, now + 28800000),
  ]);
  setCookie(c, 'session', token, {httpOnly: true, secure: new URL(c.req.url).protocol === 'https:', sameSite: 'Strict', path: '/', maxAge: 28800});
  return c.json({username: admin.username, role: admin.role, assigned_class: admin.assigned_class});
});
app.use('/api/*', async (c, next) => {
  const token = getCookie(c, 'session');
  const session = token && await c.env.DB.prepare('SELECT a.id, a.username, a.role, a.assigned_class FROM sessions s JOIN admins a ON s.admin_id = a.id WHERE s.token_hash = ? AND s.expires_at > ?').bind(await digest(token), Date.now()).first();
  if (!session) fail('Silakan login.', 401);
  c.set('admin', session);
  await next();
});
const assignedClass = c => c.get('admin').role === 'petugas' ? c.get('admin').assigned_class : null;
app.use('/api/*', async (c, next) => {
  const path = c.req.path;
  const studentsEndpoint = path === '/api/students' || path.startsWith('/api/students/');
  const accountsEndpoint = path === '/api/accounts' || path.startsWith('/api/accounts/');
  if ((accountsEndpoint || (studentsEndpoint && !['GET', 'HEAD'].includes(c.req.method))) && c.get('admin').role !== 'admin') fail('Akses ini hanya tersedia untuk pengelola.', 403);
  await next();
});
app.get('/api/me', c => c.json(c.get('admin')));
app.post('/api/logout', async c => {
  await c.env.DB.prepare('DELETE FROM sessions WHERE token_hash = ?').bind(await digest(getCookie(c, 'session'))).run();
  deleteCookie(c, 'session', {path: '/'});
  return c.json({message: 'Berhasil keluar.'});
});
app.get('/api/students', async c => {
  const scope = assignedClass(c);
  return c.json((await c.env.DB.prepare('SELECT id, name, class, major FROM students WHERE (? IS NULL OR class=?) ORDER BY class, name, id').bind(scope, scope).all()).results);
});
const student = b => [text(b.name, 100), text(b.class, 30), text(b.major, 50)];
app.post('/api/students', async c => {
  const result = await c.env.DB.prepare('INSERT INTO students (name, class, major) VALUES (?, ?, ?)').bind(...student(c.get('body'))).run();
  return c.json({id: result.meta.last_row_id}, 201);
});
app.put('/api/students/:id', async c => {
  const result = await c.env.DB.prepare('UPDATE students SET name=?, class=?, major=? WHERE id=?').bind(...student(c.get('body')), id(c.req.param('id'))).run();
  if (!result.meta.changes) fail('Siswa tidak ditemukan.', 404);
  return c.json({message: 'Data diperbarui.'});
});
app.delete('/api/students/:id', async c => {
  const studentId = id(c.req.param('id'));
  try {
    const result = await c.env.DB.prepare('DELETE FROM students WHERE id=?').bind(studentId).run();
    if (!result.meta.changes) fail('Siswa tidak ditemukan.', 404);
  } catch (err) {
    if (String(err.message).includes('FOREIGN KEY')) fail('Siswa memiliki riwayat absensi dan tidak dapat dihapus.', 409);
    throw err;
  }
  return c.json({message: 'Siswa dihapus.'});
});
app.post('/api/attendance', async c => {
  const b = c.get('body'); const className = text(b.class, 30);
  const scope = assignedClass(c);
  if (scope !== null && scope !== className) fail('Kamu hanya dapat mengisi presensi kelas yang ditugaskan.', 403);
  const students = (await c.env.DB.prepare('SELECT id FROM students WHERE class=?').bind(className).all()).results;
  if (!students.length || students.length > 500 || !Array.isArray(b.records) || b.records.length !== students.length) fail('Isi absensi seluruh siswa dalam kelas (maksimal 500).');
  const records = new Map(b.records.map(r => [id(r?.student_id), r?.status]));
  if (records.size !== students.length || students.some(s => !['Hadir', 'Izin', 'Sakit', 'Alpa'].includes(records.get(s.id)))) fail('Status atau siswa tidak valid.');
  const date = today(), time = clock();
  await c.env.DB.batch(students.map(s => c.env.DB.prepare('INSERT INTO attendance (student_id,date,time,status,recorded_by) VALUES (?,?,?,?,?) ON CONFLICT(student_id,date) DO UPDATE SET time=excluded.time,status=excluded.status,recorded_by=excluded.recorded_by').bind(s.id, date, time, records.get(s.id), c.get('admin').id)));
  return c.json({message: 'Absensi kelas berhasil disimpan.', date});
});
app.get('/api/attendance', async c => {
  const date = c.req.query('date') || today();
  if (!/^\d{4}-\d{2}-\d{2}$/.test(date) || Number.isNaN(Date.parse(date)) || new Date(date).toISOString().slice(0, 10) !== date) fail('Tanggal tidak valid.');
  let className = c.req.query('class') || '';
  const scope = assignedClass(c);
  if (scope !== null) {
    if (className && className !== scope) fail('Kamu hanya dapat melihat presensi kelas yang ditugaskan.', 403);
    className = scope;
  }
  const rows = await c.env.DB.prepare(`SELECT a.*, s.name, s.class, s.major, p.username AS recorded_by_name FROM attendance a JOIN students s ON s.id=a.student_id LEFT JOIN admins p ON p.id=a.recorded_by WHERE a.date=? AND (?='' OR s.class=?) ORDER BY s.class,s.name,s.id`).bind(date, className, className).all();
  return c.json({date, records: rows.results});
});
app.get('/api/dashboard', async c => {
  const date = today();
  const scope = assignedClass(c);
  const total = await c.env.DB.prepare('SELECT COUNT(*) AS count FROM students WHERE (? IS NULL OR class=?)').bind(scope, scope).first();
  const counts = (await c.env.DB.prepare('SELECT a.status,COUNT(*) AS count FROM attendance a JOIN students s ON s.id=a.student_id WHERE a.date=? AND (? IS NULL OR s.class=?) GROUP BY a.status').bind(date, scope, scope).all()).results;
  const statuses = Object.fromEntries(['Hadir', 'Izin', 'Sakit', 'Alpa'].map(s => [s, 0]));
  for (const row of counts) statuses[row.status] = row.count;
  return c.json({date, total: total.count, statuses, pending: Math.max(0, total.count - counts.reduce((n, r) => n + r.count, 0))});
});
app.get('/api/accounts', async c => c.json((await c.env.DB.prepare("SELECT id,username,role,assigned_class FROM admins WHERE role='petugas' ORDER BY username,id").all()).results));
app.post('/api/accounts', async c => {
  const b = c.get('body');
  const {username, password} = credentials(b);
  let scope = null;
  if (b.assigned_class !== undefined && b.assigned_class !== null && b.assigned_class !== '') {
    scope = text(b.assigned_class, 30);
    if (!await c.env.DB.prepare('SELECT id FROM students WHERE class=? LIMIT 1').bind(scope).first()) fail('Kelas belum terdaftar. Tambahkan siswa ke kelas tersebut terlebih dahulu.');
  }
  const result = await c.env.DB.prepare("INSERT INTO admins (username,password_hash,role,assigned_class) VALUES (?,?,'petugas',?) ON CONFLICT(username) DO NOTHING").bind(username, await hashPassword(password), scope).run();
  if (!result.meta.changes) fail('Username sudah digunakan. Pilih username lain.', 409);
  return c.json({message:'Akun petugas berhasil dibuat.', id:result.meta.last_row_id, username, role:'petugas', assigned_class:scope}, 201);
});
app.all('/api/*', c => c.json({error: 'Endpoint tidak ditemukan.'}, 404));
async function serveApplication(c) {
  const url = new URL(c.req.url); url.pathname = '/'; url.search = '';
  return c.env.ASSETS.fetch(new Request(url, c.req.raw));
}
app.get('/admin', serveApplication);
app.get('/presensi', serveApplication);
app.get('/admin/', c => c.redirect('/admin', 308));
app.get('/presensi/', c => c.redirect('/presensi', 308));
app.all('*', c => c.env.ASSETS.fetch(c.req.raw));
export default app;
