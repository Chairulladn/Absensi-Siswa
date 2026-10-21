
<?php
$halamanAktif = basename($_SERVER["SCRIPT_NAME"]);

$judulHalaman = [
    "index.php" => "Dashboard",
    "absen_manual.php" => "Absensi Manual",
    "siswa.php" => "Data Siswa",
    "tambah_siswa.php" => "Tambah Siswa",
    "edit_siswa.php" => "Edit Siswa",
    "riwayat_absensi.php" => "Riwayat Absensi",
    "rekap_absensi.php" => "Rekap Absensi"
];

$judul = $judulHalaman[$halamanAktif] ?? "Absensi Siswa";

date_default_timezone_set("Asia/Jakarta");

$namaHari = [
    "Sunday" => "Minggu",
    "Monday" => "Senin",
    "Tuesday" => "Selasa",
    "Wednesday" => "Rabu",
    "Thursday" => "Kamis",
    "Friday" => "Jumat",
    "Saturday" => "Sabtu"
];

$hari = $namaHari[date("l")];
$tanggal = date("d/m/Y");

$admin = $_SESSION["admin_username"] ?? "Admin";
?>

<header class="topbar">

    <div class="topbar-left">

        <button
            type="button"
            class="sidebar-toggle"
            id="sidebarToggle"
            aria-label="Tampilkan atau sembunyikan sidebar"
            aria-expanded="true"
        >
            ☰
        </button>

        <div class="topbar-title">
            <strong><?= htmlspecialchars($judul) ?></strong>
            <span>Sistem Absensi Siswa</span>
        </div>

    </div>

    <div class="topbar-right">

        <div class="topbar-date">
            <span>◷</span>
            <span>
                <?= htmlspecialchars($hari . ", " . $tanggal) ?>
            </span>
        </div>

       
<div class="topbar-admin">

    <div class="admin-profile-icon">
        <svg
            width="18"
            height="18"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="1.8"
            stroke-linecap="round"
            stroke-linejoin="round"
            aria-hidden="true"
        >
            <circle cx="12" cy="8" r="4"></circle>
            <path d="M4 21v-2a8 8 0 0 1 16 0v2"></path>
        </svg>
    </div>

    <div class="admin-info">
        <strong>Administrator</strong>
        <span>Panel Pengelola</span>
    </div>

</div>


    </div>

</header>
