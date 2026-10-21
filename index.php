
<?php
require_once "auth.php";
require_once "koneksi.php";

date_default_timezone_set("Asia/Jakarta");
$tanggalHariIni = date("Y-m-d");


function ambilJumlah($koneksi, $sql, $parameter = [])
{
    $stmt = mysqli_prepare($koneksi, $sql);

    if (!empty($parameter)) {
        $tipe = str_repeat("s", count($parameter));
        mysqli_stmt_bind_param($stmt, $tipe, ...$parameter);
    }

    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $jumlah);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);

    return (int) $jumlah;
}


$totalSiswa = ambilJumlah(
    $koneksi,
    "SELECT COUNT(*) FROM siswa"
);

$hadir = ambilJumlah(
    $koneksi,
    "SELECT COUNT(*) FROM absensi
     WHERE tanggal = ? AND status = 'Hadir'",
    [$tanggalHariIni]
);

$izin = ambilJumlah(
    $koneksi,
    "SELECT COUNT(*) FROM absensi
     WHERE tanggal = ? AND status = 'Izin'",
    [$tanggalHariIni]
);

$sakit = ambilJumlah(
    $koneksi,
    "SELECT COUNT(*) FROM absensi
     WHERE tanggal = ? AND status = 'Sakit'",
    [$tanggalHariIni]
);


$alpa = ambilJumlah(
    $koneksi,
    "SELECT COUNT(*) FROM absensi
     WHERE tanggal = ? AND status = 'Alpa'",
    [$tanggalHariIni]
);


$sudahAbsen = $hadir + $izin + $sakit + $alpa;
$belumAbsen = max(0, $totalSiswa - $sudahAbsen);

$persentase = $totalSiswa > 0
    ? round(($hadir / $totalSiswa) * 100)
    : 0;


$stmt = mysqli_prepare($koneksi, "
    SELECT
        s.nama,
        s.kelas,
        a.status,
        a.waktu
    FROM absensi a
    JOIN siswa s ON s.id = a.siswa_id
    WHERE a.tanggal = ?
    ORDER BY a.waktu DESC, a.id DESC
    LIMIT 8
");

mysqli_stmt_bind_param($stmt, "s", $tanggalHariIni);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$aktivitas = mysqli_fetch_all(
    $result,
    MYSQLI_ASSOC
);

mysqli_stmt_close($stmt);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Dashboard - Absensi Siswa</title>

    <link rel="stylesheet" href="assets/css/dark.css?v=2">
</head>

<body>

<div class="layout">

    <?php include "sidebar.php"; ?>

    <main class="content">

        <?php include "topbar.php"; ?>

        <div class="dashboard-heading">
            <div>
                <h1>Dashboard</h1>
                <p>Ringkasan aktivitas absensi siswa hari ini.</p>
            </div>

            <a href="absen_manual.php" class="dashboard-action">
                + Input Absensi
            </a>
        </div>

        <div class="dashboard-stats">

            <div class="dashboard-stat">
                <div class="stat-heading">
                    <span>Total Siswa</span>
                    <span class="stat-symbol stat-blue">▦</span>
                </div>
                <strong><?= $totalSiswa ?></strong>
                <small>Terdaftar di sistem</small>
            </div>

            <div class="dashboard-stat">
                <div class="stat-heading">
                    <span>Hadir Hari Ini</span>
                    <span class="stat-symbol stat-green">✓</span>
                </div>
                <strong><?= $hadir ?></strong>
                <small>Siswa hadir</small>
            </div>

            <div class="dashboard-stat">
                <div class="stat-heading">
                    <span>Izin Hari Ini</span>
                    <span class="stat-symbol stat-yellow">◷</span>
                </div>
                <strong><?= $izin ?></strong>
                <small>Siswa izin</small>
            </div>

            <div class="dashboard-stat">
                <div class="stat-heading">
                    <span>Sakit Hari Ini</span>
                    <span class="stat-symbol stat-red">+</span>
                </div>
                <strong><?= $sakit ?></strong>
                <small>Siswa sakit</small>
            </div>

            
<div class="dashboard-stat">
    <div class="stat-heading">
        <span>Alpa Hari Ini</span>
        <span class="stat-symbol stat-alpa">!</span>
    </div>
    <strong><?= $alpa ?></strong>
    <small>Siswa alpa</small>
</div>

</div> <!-- Penutup dashboard-stats -->

<div class="dashboard-grid">
    <section class="dashboard-panel">


                <div class="panel-heading">
                    <div>
                        <h2>Ringkasan Kehadiran</h2>
                        <p>Statistik absensi hari ini</p>
                    </div>
                </div>

                <div class="attendance-percentage">
                    <?= $persentase ?>%
                </div>

                <div class="progress-track">
                    <div
                        class="progress-fill"
                        style="width: <?= $persentase ?>%"
                    ></div>
                </div>

                <div class="attendance-details">
                    <div>
                        <span>Sudah diabsen</span>
                        <strong><?= $sudahAbsen ?></strong>
                    </div>

                    <div>
                        <span>Belum diabsen</span>
                        <strong><?= $belumAbsen ?></strong>
                    </div>
                </div>

            </section>

        </div>

        <?php include "footer.php"; ?>

    </main>

</div>

<script src="assets/js/topbar.js?v=1"></script>

</body>
</html>
