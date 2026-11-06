const view = document.querySelector('#view');
const notice = document.querySelector('#notice');
const logout = document.querySelector('#logout');
let user = null;
let generation = 0;
let navigationMessage = '';
const statuses = ['Hadir', 'Izin', 'Sakit', 'Alpa'];
const esc = value => String(value ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#39;'}[c]));
const paths = {
  dashboard: '<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>',
  students: '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2m20 0v-2a4 4 0 0 0-3-3.87M15 3.13a4 4 0 0 1 0 7.75"/><circle cx="9" cy="7" r="4"/>',
  check: '<rect x="4" y="4" width="16" height="17" rx="2"/><path d="M8 2v4m8-4v4M8 13l3 3 5-6"/>',
  history: '<path d="M3 11a9 9 0 1 1 2.5 7M3 4v7h7m2-4v5l3 2"/>',
  search: '<circle cx="10.5" cy="10.5" r="6.5"/><path d="m16 16 4 4"/>',
  plus: '<path d="M12 5v14M5 12h14"/>',
  book: '<path d="M12 5C8 2 3 4 3 4v15s5-2 9 1c4-3 9-1 9-1V4s-5-2-9 1Zm0 0v15"/>',
  tick: '<path d="m5 12 4 4L19 6"/>',
};
const icon = name => `<svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">${paths[name] || paths.book}</svg>`;
const statusBadge = status => `<span class="status-badge ${status.toLowerCase()}"><i class="dot ${status.toLowerCase()}" aria-hidden="true"></i>${esc(status)}</span>`;
const message = (text = '', error = false) => { notice.textContent = text; notice.className = error ? 'error' : ''; };
const options = values => values.map(value => `<option value="${esc(value)}">${esc(value)}</option>`).join('');
const classes = students => [...new Set(students.map(s => s.class))].sort((a, b) => a.localeCompare(b, 'id', {numeric: true}));
const field = (label, name, max = 100, placeholder = '') => `<label>${label}<input name="${name}" maxlength="${max}" placeholder="${esc(placeholder)}" required></label>`;
const empty = (title, description, link = '', action = '', symbol = 'book') => `<div class="empty">${icon(symbol)}<h2>${esc(title)}</h2><p>${esc(description)}</p>${link ? `<a class="button primary" href="${link}">${esc(action)}</a>` : ''}</div>`;
const heading = (title, subtitle, action = '') => `<div class="heading"><div><h1>${esc(title)}</h1><p>${esc(subtitle)}</p></div>${action}</div>`;

for (const link of document.querySelectorAll('nav a')) link.insertAdjacentHTML('afterbegin', icon(link.dataset.icon));
document.querySelector('#current-date').textContent = new Intl.DateTimeFormat('id-ID', {timeZone: 'Asia/Jakarta', weekday: 'long', day: 'numeric', month: 'long', year: 'numeric'}).format(new Date());

async function api(path, method = 'GET', data) {
  const response = await fetch('/api/' + path, {
    method, credentials: 'same-origin',
    headers: data ? {'Content-Type': 'application/json'} : {},
    body: data ? JSON.stringify(data) : undefined,
  });
  const result = await response.json();
  if (!response.ok) {
    if (response.status === 401 && user && !['login', 'setup'].includes(path)) {
      user = null;
      location.hash = 'login';
    }
    throw Error(result.error || 'Permintaan gagal. Coba lagi.');
  }
  return result;
}

function onForm(selector, handler) {
  document.querySelector(selector).addEventListener('submit', async event => {
    event.preventDefault();
    const button = event.target.querySelector('button[type=submit]');
    if (button) button.disabled = true;
    try { await handler(new FormData(event.target)); }
    catch (error) { message(error.message, true); }
    finally { if (button) button.disabled = false; }
  });
}

function authPage(setup) {
  const mark = document.querySelector('.brand').outerHTML;
  view.innerHTML = `<div class="auth-layout">
    <div class="auth-intro">${mark}<h2>Buku presensi sekolah.</h2><p>Kelola data siswa dan catat kehadiran kelas dalam satu buku presensi digital.</p><div class="register-graphic" aria-hidden="true"><span>Presensi</span><span>${icon('tick')}</span><span>${icon('tick')}</span><span>${icon('tick')}</span><span>—</span></div></div>
    <div class="auth"><h1>${setup ? 'Siapkan akun admin' : 'Masuk ke administrasi'}</h1><p>${setup ? 'Daftarkan admin pertama menggunakan token setup pribadi.' : 'Gunakan akun admin untuk mengelola presensi sekolah.'}</p>
      <form id="auth">${setup ? '<label>Token setup<input type="password" name="token" autocomplete="off" required><small class="muted">Token yang kamu simpan saat menyiapkan aplikasi.</small></label>' : ''}
        <label>Username<input name="username" minlength="3" maxlength="50" autocomplete="username" placeholder="Username admin" required></label>
        <label>Password<input name="password" type="password" minlength="12" maxlength="128" autocomplete="${setup ? 'new-password' : 'current-password'}" placeholder="${setup ? 'Minimal 12 karakter' : 'Masukkan password'}" required></label>
        <button type="submit" class="primary">${setup ? 'Buat akun admin' : 'Masuk'}</button>
      </form><p class="auth-link"><a href="#${setup ? 'login' : 'setup'}">${setup ? 'Sudah punya akun? Masuk' : 'Pertama kali menggunakan aplikasi? Siapkan admin'}</a></p>
    </div></div>`;
  onForm('#auth', async fd => {
    const result = await api(setup ? 'setup' : 'login', 'POST', Object.fromEntries(fd));
    if (setup) { navigationMessage = result.message; location.hash = 'login'; }
    else { user = result; const destination = user.role === 'admin' ? 'dashboard' : 'attendance'; if (location.hash === '#' + destination) await render(); else location.hash = destination; }
  });
}

async function dashboardPage(turn) {
  const [summary, students, attendance] = await Promise.all([api('dashboard'), api('students'), api('attendance')]);
  if (turn !== generation) return;
  const recorded = summary.total - summary.pending;
  const completion = summary.total ? Math.round(recorded / summary.total * 100) : 0;
  const groups = classes(students).map(name => {
    const members = students.filter(s => s.class === name);
    const memberIds = new Set(members.map(s => s.id));
    const records = attendance.records.filter(r => memberIds.has(r.student_id));
    return {name, total: members.length, recorded: records.length, present: records.filter(r => r.status === 'Hadir').length};
  });
  const nextTitle = !summary.total ? 'Mulai dari data siswa' : summary.pending ? `${summary.pending} siswa belum dicatat` : 'Presensi hari ini lengkap';
  const nextCopy = !summary.total ? 'Tambahkan siswa dan kelas sebelum mencatat kehadiran.' : summary.pending ? 'Lanjutkan presensi untuk kelas yang belum lengkap.' : 'Kamu bisa melihat atau memperbarui catatan hari ini.';
  view.innerHTML = heading('Presensi hari ini', 'Pantau kehadiran dan lanjutkan pencatatan kelas.', `<a class="button primary heading-action" href="#attendance">${icon('check')}Catat kehadiran</a>`) + `
    <div class="attendance-overview"><div class="overview-main"><h2>Pencatatan kehadiran</h2><div class="overview-value"><strong>${recorded}<span> / ${summary.total}</span></strong><span>siswa sudah dicatat</span></div><progress max="100" value="${completion}" aria-label="${completion}% siswa sudah dicatat"></progress><p>${summary.total ? `${completion}% selesai. ${summary.statuses.Hadir} siswa tercatat hadir hari ini.` : 'Belum ada siswa terdaftar. Data akan muncul setelah siswa ditambahkan.'}</p></div><div class="overview-next"><div><strong>${nextTitle}</strong><p>${nextCopy}</p></div><a class="button" href="#${summary.total ? 'attendance' : 'students'}">${summary.total ? 'Buka presensi' : 'Tambah siswa'}</a></div></div>
    <div class="status-strip">${statuses.map(status => `<div class="status-item"><span><i class="dot ${status.toLowerCase()}" aria-hidden="true"></i>${status}</span><strong>${summary.statuses[status]}</strong></div>`).join('')}</div>
    <div class="panel"><div class="panel-head"><div><h2>Kehadiran per kelas</h2><p>${groups.length ? `${groups.length} kelas terdaftar dalam buku presensi.` : 'Daftar kelas mengikuti data siswa yang terdaftar.'}</p></div><a href="#history">Lihat riwayat</a></div>${groups.length ? `<div class="table-wrap"><table><thead><tr><th>Kelas</th><th>Jumlah siswa</th><th>Hadir</th><th>Sudah dicatat</th><th>Kelengkapan</th></tr></thead><tbody>${groups.map(g => `<tr><td class="class-name">${esc(g.name)}</td><td>${g.total} siswa</td><td>${g.present}</td><td>${g.recorded} dari ${g.total}</td><td><progress class="class-progress" max="${g.total}" value="${g.recorded}" aria-label="${esc(g.name)}: ${g.recorded} dari ${g.total} dicatat"></progress></td></tr>`).join('')}</tbody></table></div>` : empty('Buku presensi masih kosong', 'Tambahkan siswa beserta kelasnya. Setelah itu, kamu bisa mencatat kehadiran setiap hari.', '#students', 'Tambah siswa pertama', 'students')}</div>`;
}

async function studentsPage(turn) {
  const students = await api('students');
  if (turn !== generation) return;
  view.innerHTML = heading('Data siswa', 'Daftar siswa yang digunakan untuk presensi setiap kelas.') + `
    <div class="students-layout"><div class="panel student-editor"><div class="panel-head"><div><h2 id="editor-title">Tambah siswa</h2><p>Isi identitas dan kelas siswa.</p></div></div><div class="panel-body"><form id="student-form"><input type="hidden" name="id">${field('Nama lengkap', 'name', 100, 'Nama sesuai daftar siswa')}${field('Kelas', 'class', 30, 'Contoh: XI TKJ 1')}${field('Jurusan', 'major', 50, 'Contoh: Teknik Komputer dan Jaringan')}<div class="actions"><button type="submit" class="primary">Simpan siswa</button><button type="reset" class="quiet">Batal</button></div><p class="editor-hint muted">Gunakan penulisan kelas yang sama untuk siswa dalam satu kelas.</p></form></div></div>
    <div class="panel student-register"><div class="panel-head"><h2>Daftar siswa <span class="count">${students.length} siswa</span></h2><label class="search-field"><span class="sr-only">Cari nama, kelas, atau jurusan</span>${icon('search')}<input id="student-search" type="search" placeholder="Cari nama atau kelas…"></label></div><div id="student-rows"></div></div></div>`;
  const form = document.querySelector('#student-form');
  const drawRows = query => {
    const filtered = students.filter(s => `${s.name} ${s.class} ${s.major}`.toLocaleLowerCase('id').includes(query.toLocaleLowerCase('id')));
    document.querySelector('#student-rows').innerHTML = filtered.length ? `<div class="table-wrap"><table><thead><tr><th>No.</th><th>Nama siswa</th><th>Kelas</th><th>Aksi</th></tr></thead><tbody>${filtered.map((s, i) => `<tr><td class="row-number">${i + 1}</td><td class="class-name">${esc(s.name)}<small>${esc(s.major)}</small></td><td>${esc(s.class)}</td><td><div class="table-actions"><button class="small" data-edit="${s.id}" aria-label="Edit ${esc(s.name)}">Edit</button><button class="small danger" data-delete="${s.id}" aria-label="Hapus ${esc(s.name)}">Hapus</button></div></td></tr>`).join('')}</tbody></table></div>` : empty(students.length ? 'Siswa tidak ditemukan' : 'Belum ada siswa', students.length ? 'Coba nama atau kelas yang berbeda.' : 'Isi formulir tambah siswa untuk memulai daftar kelas.', '', '', 'students');
    document.querySelectorAll('[data-edit]').forEach(button => button.onclick = () => {
      const student = students.find(s => s.id === Number(button.dataset.edit));
      for (const key of ['id', 'name', 'class', 'major']) form.elements.namedItem(key).value = student[key];
      document.querySelector('#editor-title').textContent = 'Edit siswa';
      form.elements.namedItem('name').focus();
    });
    document.querySelectorAll('[data-delete]').forEach(button => button.onclick = async () => {
      const student = students.find(s => s.id === Number(button.dataset.delete));
      if (!confirm(`Hapus ${student.name}? Siswa yang sudah memiliki riwayat absensi tidak dapat dihapus.`)) return;
      try { await api('students/' + student.id, 'DELETE', {}); await render(); message('Siswa dihapus dari daftar.'); }
      catch (error) { message(error.message, true); }
    });
  };
  drawRows('');
  document.querySelector('#student-search').addEventListener('input', event => drawRows(event.target.value));
  form.onreset = () => { document.querySelector('#editor-title').textContent = 'Tambah siswa'; };
  onForm('#student-form', async fd => {
    const data = Object.fromEntries(fd);
    await api('students' + (data.id ? '/' + data.id : ''), data.id ? 'PUT' : 'POST', data);
    await render(); message('Data siswa disimpan.');
  });
}

async function attendancePage(turn, history) {
  const students = await api('students');
  if (turn !== generation) return;
  const lockedClass = user.role === 'petugas' ? user.assigned_class : null;
  view.innerHTML = heading(history ? 'Riwayat absensi' : 'Catat kehadiran', history ? 'Temukan kembali catatan kehadiran berdasarkan tanggal dan kelas.' : 'Pilih kelas, periksa status siswa, lalu simpan presensi hari ini.') + `<div class="panel"><form id="filter" class="filters">${history ? '<label>Tanggal<input type="date" name="date" required></label>' : ''}<label>Kelas${lockedClass !== null ? `<input name="class" value="${esc(lockedClass)}" readonly aria-describedby="class-assignment-note"><small id="class-assignment-note" class="muted">Kelas sesuai penugasan akunmu.</small>` : `<select name="class">${history ? '<option value="">Semua kelas</option>' : '<option value="">Pilih kelas</option>'}${options(classes(students))}</select>`}</label><button type="submit" class="primary">${history ? 'Cari catatan' : 'Tampilkan siswa'}</button></form><div id="records">${empty(students.length ? (history ? 'Pilih catatan yang ingin dilihat' : 'Kelas mana yang akan dicatat?') : 'Tambahkan siswa terlebih dahulu', students.length ? (history ? 'Tentukan tanggal dan kelas, lalu pilih Cari catatan.' : 'Pilih kelas di atas untuk membuka daftar presensi hari ini.') : (user.role === 'admin' ? 'Daftar presensi akan tersedia setelah kamu menambahkan data siswa.' : 'Hubungi pengelola untuk menambahkan siswa ke kelas yang ditugaskan.'), students.length || user.role !== 'admin' ? '' : '#students', 'Tambah siswa', history ? 'history' : 'check')}</div></div>`;
  if (history) {
    const summary = await api('dashboard');
    if (turn !== generation) return;
    document.querySelector('[name=date]').value = summary.date;
  }
  onForm('#filter', async fd => {
    const selected = String(fd.get('class') || '');
    if (!history && !selected) { message('Pilih kelas yang ingin dicatat.', true); return; }
    const target = document.querySelector('#records');
    if (history) {
      const data = await api('attendance?' + new URLSearchParams(fd));
      if (turn !== generation) return;
      target.innerHTML = `<div class="record-caption"><h2>Catatan kehadiran</h2><p>${data.records.length} catatan ditemukan</p></div>` + (data.records.length ? `<div class="table-wrap"><table><thead><tr><th>Nama siswa</th><th>Kelas</th><th>Status</th><th>Tanggal</th><th>Waktu WIB</th><th>Dicatat oleh</th></tr></thead><tbody>${data.records.map(r => `<tr><td class="class-name">${esc(r.name)}</td><td>${esc(r.class)}</td><td>${statusBadge(r.status)}</td><td>${esc(r.date)}</td><td>${esc(r.time)}</td><td>${esc(r.recorded_by_name ?? 'Tidak tersedia')}</td></tr>`).join('')}</tbody></table></div>` : empty('Tidak ada catatan untuk filter ini', 'Coba tanggal atau kelas yang berbeda. Presensi baru dapat dicatat melalui menu Catat kehadiran.', '', '', 'history'));
    } else {
      const members = students.filter(s => s.class === selected);
      const data = await api('attendance?class=' + encodeURIComponent(selected));
      if (turn !== generation) return;
      if (!members.length) { target.innerHTML = empty('Kelas belum memiliki siswa', 'Tambahkan siswa ke kelas ini sebelum mencatat kehadiran.'); return; }
      target.innerHTML = `<div class="record-caption"><h2>${esc(selected)}</h2><p>${members.length} siswa</p></div><form id="attendance-form"><div class="table-wrap"><table><thead><tr><th>No.</th><th>Nama siswa</th><th>Status kehadiran</th></tr></thead><tbody>${members.map((s, i) => `<tr><td class="row-number">${i + 1}</td><td class="class-name">${esc(s.name)}</td><td><select name="${s.id}" aria-label="Status ${esc(s.name)}">${options(statuses)}</select></td></tr>`).join('')}</tbody></table></div><div class="save-bar"><p>Periksa semua status sebelum menyimpan.<br>Menyimpan ulang akan memperbarui presensi hari ini.</p><button class="primary" type="submit">Simpan presensi kelas</button></div></form>`;
      const form = document.querySelector('#attendance-form');
      for (const record of data.records) {
        const input = form.elements.namedItem(String(record.student_id));
        if (input) input.value = record.status;
      }
      onForm('#attendance-form', async values => {
        await api('attendance', 'POST', {class: selected, records: members.map(s => ({student_id: s.id, status: values.get(String(s.id))}))});
        message(`Presensi kelas ${selected} disimpan.`);
      });
    }
  });
}

async function accountsPage(turn) {
  const [accounts, students] = await Promise.all([api('accounts'), api('students')]);
  if (turn !== generation) return;
  view.innerHTML = heading('Akun petugas', 'Atur akun pengisi presensi dan kelas yang dapat diakses.') + `
    <div class="students-layout"><div class="panel student-editor"><div class="panel-head"><div><h2>Tambah petugas</h2><p>Buat akun untuk guru, kepsek, atau ketua kelas.</p></div></div>
    <div class="panel-body"><form id="account-form"><label>Username<input name="username" minlength="3" maxlength="50" autocomplete="off" placeholder="Contoh: ketua_xi_tkj4" required></label><label>Password<input name="password" type="password" minlength="12" maxlength="128" autocomplete="new-password" placeholder="Minimal 12 karakter" required></label><label>Akses kelas<select name="assigned_class"><option value="">Semua kelas</option>${options(classes(students))}</select></label><p class="editor-hint muted">Pilih satu kelas untuk ketua kelas. Semua kelas memberikan akses presensi ke seluruh kelas terdaftar.</p><button type="submit" class="primary">Buat akun petugas</button></form></div></div>
    <div class="panel student-register"><div class="panel-head"><div><h2>Petugas terdaftar <span class="count">${accounts.length} akun</span></h2><p>Petugas dapat mengisi presensi, tetapi tidak dapat mengelola akun atau mengubah data siswa.</p></div></div>
    ${accounts.length ? `<div class="table-wrap"><table><thead><tr><th>No.</th><th>Username</th><th>Akses kelas</th></tr></thead><tbody>${accounts.map((a, i) => `<tr><td class="row-number">${i + 1}</td><td class="class-name">${esc(a.username)}</td><td>${esc(a.assigned_class ?? 'Semua kelas')}</td></tr>`).join('')}</tbody></table></div>` : empty('Belum ada akun petugas', 'Gunakan formulir tambah petugas untuk membuat akun pengisi presensi.', '', '', 'students')}</div></div>`;
  onForm('#account-form', async fd => {
    const data = Object.fromEntries(fd);
    await api('accounts', 'POST', {username: data.username, password: data.password, assigned_class: data.assigned_class || null});
    await render();
    message('Akun petugas berhasil dibuat. Sampaikan username dan password secara pribadi kepada petugas.');
  });
}

async function render() {
  const turn = ++generation;
  const page = location.hash.slice(1) || 'dashboard';
  if (user) {
    const entryPath = user.role === 'admin' ? '/admin' : '/presensi';
    const destination = user.role === 'admin' ? page : 'attendance';
    if (location.pathname !== entryPath) { location.replace(entryPath + '#' + destination); return; }
  }
  if (user?.role === 'petugas' && page !== 'attendance') { location.hash = 'attendance'; return; }
  message(navigationMessage); navigationMessage = '';
  view.setAttribute('aria-busy', 'true');
  document.body.classList.toggle('auth-mode', !user);
  document.querySelectorAll('nav a').forEach(link => {
    link.hidden = user?.role !== 'admin' && link.hash !== '#attendance';
    const active = link.hash === '#' + page;
    link.classList.toggle('active', active);
    if (active) link.setAttribute('aria-current', 'page'); else link.removeAttribute('aria-current');
  });
  document.querySelector('#breadcrumb').textContent = !user ? 'Administrasi sekolah' : ({dashboard: 'Ringkasan kehadiran', students: 'Administrasi / Data siswa', attendance: 'Presensi / Catat kehadiran', history: 'Presensi / Riwayat absensi', accounts: 'Administrasi / Akun petugas'})[page] || 'Ruang administrasi';
  document.querySelector('#account').innerHTML = `<span class="avatar" aria-hidden="true">${esc((user?.username || 'A').slice(0, 1).toUpperCase())}</span><span>${esc(user?.username || 'Ruang administrasi')}<small>${user ? 'Administrator' : 'Akses khusus admin'}</small></span>`;
  logout.hidden = !user;
  document.querySelector('#accounts-link').hidden = user?.role !== 'admin';
  try {
    if (!user) { authPage(page === 'setup'); return; }
    if (page === 'dashboard') await dashboardPage(turn);
    else if (page === 'students') await studentsPage(turn);
    else if (page === 'accounts') { if (user.role !== 'admin') { location.hash = 'attendance'; return; } await accountsPage(turn); }
    else if (page === 'attendance' || page === 'history') await attendancePage(turn, page === 'history');
    else location.hash = 'dashboard';
  } catch (error) {
    if (turn === generation) {
      view.innerHTML = `<div class="panel">${empty('Data belum dapat dimuat', 'Periksa koneksi, lalu coba muat kembali halaman ini.')}<div class="actions panel-body"><button id="retry">Coba lagi</button></div></div>`;
      document.querySelector('#retry').onclick = render;
      message(error.message, true);
    }
  } finally { if (turn === generation) view.setAttribute('aria-busy', 'false'); }
}
logout.onclick = async () => {
  try { await api('logout', 'POST', {}); user = null; location.hash = 'login'; await render(); }
  catch (error) { message(error.message, true); }
};
window.addEventListener('hashchange', render);
try { user = await api('me'); } catch { /* Visitors without a session see the login form. */ }
await render();
