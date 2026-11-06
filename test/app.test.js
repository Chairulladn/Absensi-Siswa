import {test} from 'node:test';
import assert from 'node:assert/strict';
import {mkdtemp, readFile, readdir, rm} from 'node:fs/promises';
import {tmpdir} from 'node:os';
import {join} from 'node:path';
import {getPlatformProxy} from 'wrangler';
import app from '../src/index.js';
import {digest, today} from '../src/auth.js';

test('D1 integration: setup, authentication, students, attendance and logout', async () => {
  const path=await mkdtemp(join(tmpdir(),'absensi-test-'));
  const proxy=await getPlatformProxy({persist:{path}});
  const env={...proxy.env,SETUP_TOKEN:'test-only-setup-token-'.repeat(3)};
  let cookie='';
  async function request(route, method='GET', body, origin='https://school.test'){
    return app.request('https://school.test/api/'+route,{method,headers:{Origin:origin,'Content-Type':'application/json',Cookie:cookie},body:body===undefined?undefined:JSON.stringify(body)},env);
  }
  try{
    const directory = new URL('../migrations/', import.meta.url);
    for (const file of (await readdir(directory)).filter(f => f.endsWith('.sql')).sort()) {
      const sql = await readFile(new URL(file, directory), 'utf8');
      for (const statement of sql.replace(/^--.*$/gm, '').split(';').filter(s => s.trim())) await env.DB.prepare(statement).run();
    }
    assert.equal((await request('students')).status,401);
    assert.equal((await request('setup','POST',{token:'wrong'})).status,403);
    const credentials={username:'admin',password:'a-strong-test-password-123'};
    assert.equal((await request('setup','POST',{...credentials,token:env.SETUP_TOKEN})).status,201);
    assert.equal((await request('setup','POST',{...credentials,token:env.SETUP_TOKEN})).status,409);
    assert.equal((await request('login','POST',credentials,'https://evil.test')).status,403);
    assert.equal((await request('login','POST',{...credentials,password:'wrong-but-long-password'})).status,401);
    const login=await request('login','POST',credentials);
    assert.equal(login.status,200);
    assert.match(login.headers.get('set-cookie'),/HttpOnly/i);
    assert.match(login.headers.get('set-cookie'),/Secure/i);
    assert.match(login.headers.get('set-cookie'),/SameSite=Strict/i);
    cookie=login.headers.get('set-cookie').split(';')[0];
    assert.equal((await request('me')).status,200);
    const s={name:'Siswa Uji <script>',class:'XI TKJ 1',major:'TKJ'};
    assert.equal((await request('students','POST',{...s,name:''})).status,400);
    const added=await request('students','POST',s);assert.equal(added.status,201);
    const {id}=await added.json();
    assert.equal((await request('students/'+id,'PUT',{...s,name:'Nama Baru'})).status,200);
    assert.equal((await request('students/999','PUT',s)).status,404);
    assert.equal((await (await request('students')).json())[0].name,'Nama Baru');
    assert.equal((await request('attendance','POST',{class:s.class,records:[]})).status,400);
    assert.equal((await request('attendance','POST',{class:s.class,records:[{student_id:id,status:'invalid'}]})).status,400);
    for(const status of ['Hadir','Izin'])assert.equal((await request('attendance','POST',{class:s.class,records:[{student_id:id,status}]})).status,200);
    const records=(await (await request('attendance?date='+today())).json()).records;
    assert.equal(records.length,1);assert.equal(records[0].status,'Izin');
    assert.equal((await request('attendance?date=2026-02-30')).status,400);
    const dashboard=await (await request('dashboard')).json();
    assert.equal(dashboard.total,1);assert.equal(dashboard.statuses.Izin,1);assert.equal(dashboard.pending,0);
    assert.equal((await request('students/'+id,'DELETE',{})).status,409);
    const removable=await (await request('students','POST',{...s,name:'Hapus Saya'})).json();
    assert.equal((await request('students/'+removable.id,'DELETE',{})).status,200);
    const adminCookie = cookie;
    const outside = await (await request('students', 'POST', {...s, name:'Other class', class:'XI AKL 1'})).json();
    assert.equal((await request('accounts','POST',{username:'badclass',password:credentials.password,assigned_class:'missing'})).status,400);
    const petugas = {username:'ketua_kelas',password:'test-petugas-password-123',assigned_class:s.class};
    assert.equal((await request('accounts','POST',{...petugas,role:'admin'})).status,201);
    assert.equal((await request('accounts','POST',petugas)).status,409);
    const accounts = await (await request('accounts')).json();
    assert.equal(accounts[0].role,'petugas'); assert.ok(!('password_hash' in accounts[0]));
    const petugasLogin = await request('login','POST',petugas); assert.equal(petugasLogin.status,200);
    assert.equal((await petugasLogin.clone().json()).role,'petugas');
    cookie=petugasLogin.headers.get('set-cookie').split(';')[0];
    const account = await (await request('me')).json();assert.equal(account.assigned_class,s.class);
    const scopedStudents = await (await request('students')).json(); assert.equal(scopedStudents.length,1);assert.equal(scopedStudents[0].id,id);
    for (const [route,method,body] of [['accounts','GET',undefined],['accounts','POST',petugas],['students','POST',s],['students/'+id,'PUT',s],['students/'+id,'DELETE',{}]]) {
      assert.equal((await request(route,method,body)).status,403,route+' must be admin-only');
    }
    assert.equal((await request('attendance?class=XI%20AKL%201')).status,403);
    assert.equal((await request('attendance','POST',{class:'XI AKL 1',records:[{student_id:outside.id,status:'Hadir'}]})).status,403);
    assert.equal((await request('attendance','POST',{class:s.class,records:[{student_id:outside.id,status:'Hadir'}]})).status,400);
    assert.equal((await request('attendance','POST',{class:s.class,records:[{student_id:id,status:'Sakit'}]})).status,200);
    const scopedHistory = await (await request('attendance')).json();
    assert.equal(scopedHistory.records.length,1);assert.equal(scopedHistory.records[0].recorded_by_name,petugas.username);
    assert.equal(scopedHistory.records[0].recorded_by,account.id);
    assert.equal((await (await request('dashboard')).json()).total,1);
    cookie=adminCookie;
    assert.equal((await (await request('dashboard')).json()).total,2);
    assert.equal((await (await request('attendance')).json()).records[0].recorded_by_name,petugas.username);
    const teacher={username:'teacher',password:'test-teacher-password-123',assigned_class:null};
    assert.equal((await request('accounts','POST',teacher)).status,201);
    const teacherLogin=await request('login','POST',teacher);cookie=teacherLogin.headers.get('set-cookie').split(';')[0];
    assert.equal((await (await request('students')).json()).length,2);
    assert.equal((await request('attendance','POST',{class:'XI AKL 1',records:[{student_id:outside.id,status:'Hadir'}]})).status,200);
    cookie=adminCookie;
    // Both entry points serve the same frontend document; access is enforced by API sessions.
    const assets={fetch:async req=>new Response(new URL(req.url).pathname)};
    for (const entry of ['/admin','/presensi']) {
      const response=await app.request('https://school.test'+entry,{}, {...env,ASSETS:assets});
      assert.equal(await response.text(),'/');
    }
    assert.equal((await app.request('https://school.test/presensi/',{},env)).status,308);
    assert.equal((await request('logout','POST',{})).status,200);
    assert.equal((await request('me')).status,401);
    const again=await request('login','POST',credentials);cookie=again.headers.get('set-cookie').split(';')[0];
    await env.DB.prepare('UPDATE sessions SET expires_at=0').run();assert.equal((await request('me')).status,401);
    const stored=await env.DB.prepare('SELECT password_hash FROM admins').first();assert.notEqual(stored.password_hash,credentials.password);
    assert.notEqual(await digest(cookie.split('=')[1]),cookie.split('=')[1]);
    await env.DB.prepare('UPDATE login_attempts SET attempts=10').run();assert.equal((await request('login','POST',credentials)).status,429);
  }finally{await proxy.dispose();await rm(path,{recursive:true,force:true});}
});
