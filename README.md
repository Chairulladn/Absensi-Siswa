# Absensi

Aplikasi presensi sekolah dengan dua ruang kerja: **pengelola** untuk mengelola siswa dan akun petugas, serta **petugas** untuk mengisi kehadiran kelas. Frontend menggunakan HTML, CSS, dan JavaScript; backend berjalan di Cloudflare Workers dengan database D1. Tidak memerlukan VPS.

## Fitur

- **Web pengelola (`/admin`):** dashboard harian, daftar siswa dengan pencarian, tambah/edit/hapus siswa, akun petugas, dan riwayat berdasarkan tanggal serta kelas.
- **Web pengisian (`/presensi`):** petugas memilih kelas dan mencatat Hadir, Izin, Sakit, atau Alpa untuk seluruh siswa di kelas tersebut.
- **Penugasan kelas:** ketua kelas dapat dibatasi ke satu kelas; guru atau kepsek dapat diberi akun petugas dengan akses seluruh kelas.
- **Identitas pengisi:** riwayat menampilkan username petugas terakhir yang menyimpan atau memperbarui presensi.
- **Presensi harian:** satu catatan per siswa per tanggal. Penyimpanan ulang memperbarui catatan yang sama.
- **Waktu WIB:** tanggal dan waktu pencatatan menggunakan `Asia/Jakarta`.
- Tampilan responsif untuk desktop dan ponsel, dengan navigasi keyboard dan pesan kondisi kosong.

Siswa tidak perlu mengetik identitas atau membuat akun sendiri. Pengelola mendaftarkan siswa satu kali, lalu petugas mengisi kehadiran dari daftar yang tersedia.

## Teknologi

| Bagian | Teknologi |
| --- | --- |
| Frontend | HTML, CSS, JavaScript tanpa framework |
| API | Hono, JavaScript, Cloudflare Workers |
| Database | Cloudflare D1 (SQLite) |
| Hosting frontend | Workers Static Assets |
| Pengembangan | Node.js 22+, npm, Wrangler |
| Domain | Subdomain `workers.dev` atau Custom Domain di Cloudflare |

Node.js digunakan untuk alat pengembangan. Backend produksi berjalan pada runtime Workers dengan flag `nodejs_compat`, bukan server Node.js yang harus dipelihara sendiri.

## Hak akses

| Kemampuan | Admin | Petugas satu kelas | Petugas semua kelas |
| --- | --- | --- | --- |
| Kelola siswa | Ya | Tidak | Tidak |
| Buat dan lihat akun petugas | Ya | Tidak | Tidak |
| Baca daftar siswa | Semua kelas | Kelas penugasan | Semua kelas |
| Isi presensi | Semua kelas | Kelas penugasan | Semua kelas |
| Baca riwayat melalui API | Semua kelas | Kelas penugasan | Semua kelas |
| Dashboard melalui API | Semua kelas | Kelas penugasan | Semua kelas |

Tampilan petugas difokuskan pada pengisian presensi. Pembatasan data dan operasi diterapkan kembali di backend, termasuk jika endpoint diakses langsung. `assigned_class = NULL` berarti petugas dapat mengakses seluruh kelas.

## Struktur proyek

```text
public/
  index.html                 Kerangka halaman
  style.css                  Tampilan dan layout responsif
  app.js                     Login, navigasi, dan formulir
  favicon.svg                Ikon aplikasi
  _headers                   Header keamanan Static Assets
src/
  index.js                   API, sesi, hak akses, dan rute halaman
  auth.js                    Hash password, token, dan waktu WIB
migrations/
  0001_initial.sql           Tabel dasar tanpa data bawaan
  0002_petugas.sql           Role, penugasan kelas, dan identitas pengisi
test/
  app.test.js                Pengujian integrasi dengan D1 lokal
.dev.vars.example            Contoh konfigurasi lokal tanpa secret
wrangler.jsonc               Binding Workers, Static Assets, dan D1
```

File PHP dan folder `pages/`, `includes/`, `config/`, `setup/`, serta `assets/` lama dipertahankan sebagai referensi versi PHP/MySQL. Versi Cloudflare hanya mempublikasikan frontend di `public/` dan kode Worker; tidak menjalankan PHP atau mengimpor data MySQL lama.

## Menjalankan secara lokal

### 1. Pasang dependensi

```sh
git clone https://github.com/Chairulladn/Absensi-Siswa.git
cd Absensi-Siswa
npm ci
```

**Windows PowerShell:** jika `npm.ps1` diblokir execution policy, gunakan `npm.cmd` dan `npx.cmd`, misalnya `npm.cmd ci`. Tidak perlu mengubah execution policy.

### 2. Siapkan database lokal

```sh
npm run db:local
```

Wrangler menerapkan seluruh migration ke D1 lokal. Database lokal terpisah dari database produksi, tanpa akun, siswa, atau presensi bawaan. Jalankan perintah ini lagi setelah menambahkan migration baru. Jangan mengubah migration yang sudah diterapkan.

### 3. Buat admin pertama

Salin `.dev.vars.example` menjadi `.dev.vars`. Buat token acak pribadi:

```sh
node -e "console.log(require('node:crypto').randomBytes(32).toString('hex'))"
```

Isi file `.dev.vars` dengan token tersebut:

```dotenv
SETUP_TOKEN=GANTI_DENGAN_TOKEN_ACAK_PRIBADI
```

Token minimal 32 karakter. Nilai contoh di atas harus diganti. Jangan commit `.dev.vars`, mengirim token di chat, atau memakai secret produksi untuk pengujian lokal.

```sh
npm run dev
```

Buka alamat yang ditampilkan Wrangler, biasanya `http://127.0.0.1:8787/admin`. Pilih **Pertama kali menggunakan aplikasi? Siapkan admin**, masukkan token, username (3–50 karakter), dan password (12–128 karakter). Setup hanya membuat admin pertama; sesudahnya gunakan login.

Setelah admin berhasil dibuat, hapus baris `SETUP_TOKEN` dari `.dev.vars` dan restart server untuk menonaktifkan setup. Akun yang telah dibuat tetap tersimpan.

> Jalankan aplikasi melalui Wrangler. VS Code Live Server hanya menyajikan file statis dan tidak menjalankan API atau D1.

### 4. Buat akun petugas

1. Login sebagai admin di `/admin`.
2. Tambahkan siswa beserta kelas dan jurusan.
3. Buka **Akun petugas**, lalu isi username dan password baru.
4. Pilih satu kelas untuk penugasan khusus, atau **Semua kelas** sesuai kebutuhan.
5. Sampaikan kredensial kepada petugas secara pribadi.
6. Login sebagai petugas di `/presensi`, tampilkan siswa, tentukan status, lalu simpan.
7. Di web pengelola, buka **Riwayat absensi** untuk melihat hasil dan pengisi terakhir.

Gunakan jendela browser terpisah atau Incognito untuk menguji dua akun bersamaan. Kedua alamat memakai cookie sesi yang sama pada satu origin. Kelas penugasan otomatis terisi pada formulir petugas.

## Perintah yang tersedia

| Perintah | Fungsi |
| --- | --- |
| `npm run dev` | Menjalankan frontend dan API dengan D1 lokal |
| `npm run db:local` | Menerapkan migration lokal |
| `npm run db:remote` | Menerapkan migration ke D1 produksi |
| `npm test` | Menjalankan pengujian integrasi |
| `npm run build` | Simulasi deployment tanpa publikasi |
| `npm run deploy` | Mempublikasikan Worker dan Static Assets |

Pengujian memakai database lokal terisolasi, bukan data siswa yang dipakai untuk pengembangan atau produksi. Cakupannya meliputi setup sekali, login, cookie sesi, validasi origin, CRUD siswa, absensi insert/update, riwayat, dashboard, logout, sesi kedaluwarsa, pembatasan login, akun petugas, pembatasan kelas, dan identitas pengisi terakhir. Pengujian ini tidak membuktikan bahwa akun Cloudflare, DNS, atau deployment produksi telah dikonfigurasi.

## Deployment Cloudflare

Konfigurasi repository memakai nama Worker **`absensi`** dan binding database **`DB`** dengan nama D1 **`absensi-siswa`**. `database_id` bukan secret, tetapi menunjuk resource akun pemilik proyek. Jika memakai akun Cloudflare lain, buat D1 sendiri dan ganti ID tersebut; nama binding tetap `DB`.

```sh
npx wrangler login
npx wrangler whoami
```

Untuk instalasi baru pada akun lain:

```sh
npx wrangler d1 create absensi-siswa
```

Salin ID hasil perintah ke `wrangler.jsonc`. Pertahankan hanya satu binding `DB` untuk database yang digunakan. Pilih **No** bila ditanya apakah local dev harus memakai remote resource.

Pada akun pemilik yang sudah memiliki database yang benar, gunakan database tersebut tanpa membuat atau menghapus database lain.

```sh
npm run db:remote
npm test
npm run build
npm run deploy
```

Periksa target database sebelum menyetujui migration remote. Deployment mengirim kode dan aset; tidak menyalin akun, siswa, atau presensi dari database lokal. Mengubah `name` lalu deploy akan membuat atau menargetkan Worker dengan nama baru; secret Worker lama tidak otomatis ikut berpindah.

### Admin produksi

Jika database produksi belum memiliki admin, buat token acak yang berbeda dari lokal, lalu masukkan secara interaktif:

```sh
npx wrangler secret put SETUP_TOKEN
```

Pada alamat HTTPS hasil deployment, buat admin pertama melalui halaman setup. Setelah berhasil:

```sh
npx wrangler secret delete SETUP_TOKEN
```

Jika admin sudah tersedia, login menggunakan akun tersebut; tidak perlu menjalankan setup lagi. Secret lokal `.dev.vars` tidak menjadi konfigurasi produksi secara otomatis.

Alamat untuk konfigurasi akun pemilik proyek:

```text
https://absensi.chairulladn.workers.dev/admin
https://absensi.chairulladn.workers.dev/presensi
```

Gunakan URL yang benar-benar ditampilkan oleh deployment akunmu. README ini bukan pemeriksaan ketersediaan layanan produksi.

### Custom Domain

Gunakan domain yang dimiliki atau diizinkan untuk dikelola, lalu aktifkan domain utama di Cloudflare DNS. Buka **Workers & Pages → absensi → Settings → Domains & Routes**, tambahkan **Custom Domain**, misalnya `absensi.namasekolah.sch.id`. Cloudflare menangani DNS dan HTTPS setelah pemasangan berhasil.

Jika muncul **No zones found**, belum ada domain yang tersedia pada akun untuk dipasang. Alamat gratis `workers.dev` tetap dapat dipakai. Mengganti subdomain akun `workers.dev` berdampak pada alamat seluruh Worker dalam akun tersebut.

## Keamanan dan batasan

- Password disimpan sebagai PBKDF2-SHA256 dengan salt acak dan 100.000 iterasi, menggunakan Web Crypto Workers.
- Token sesi acak disimpan sebagai hash di D1; sesi berlaku 8 jam. Cookie menggunakan HttpOnly, SameSite=Strict, dan Secure pada HTTPS.
- Mutasi API membutuhkan JSON dan Origin yang sama. Query database menggunakan parameter.
- Login dibatasi 10 percobaan per IP dalam 15 menit, termasuk login berhasil. Pada local dev, percobaan berbagi key `local`.
- Admin membuat akun petugas; endpoint tersebut tidak menerima peningkatan role dari browser.
- Siswa dengan riwayat presensi tidak dapat dihapus. Pengiriman presensi dibatasi maksimal 500 siswa per kelas dan disimpan atomik.
- `recorded_by` menyimpan pengisi **terakhir**, bukan audit seluruh perubahan. Catatan lama dapat memiliki pengisi kosong.
- Pengelolaan akun saat ini mencakup pembuatan dan daftar akun; belum tersedia formulir reset password, perubahan penugasan, atau penonaktifan akun.
- Data dalam `.wrangler/`, secret, backup database, dan dependensi lokal tidak ikut Git. Export/backup D1 sebelum perubahan produksi berikutnya.

## Pengembang

Dikembangkan oleh [Chairulladn](https://github.com/Chairulladn).
