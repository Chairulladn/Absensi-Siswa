<?php

include "koneksi.php";

/* =========================
   TOTAL ANGGOTA
========================= */

$query = mysqli_query(
    $koneksi,
    "SELECT COUNT(*) AS total FROM karyawan"
);

$data = mysqli_fetch_assoc($query);

$total_anggota = $data['total'];


/* =========================
   PENERIMAAN HARI INI
========================= */

$tanggal_hari_ini = date("Y-m-d");

$query = mysqli_query(
    $koneksi,
    "SELECT COUNT(*) AS total
     FROM absensi
     WHERE tanggal = '$tanggal_hari_ini'"
);

$data = mysqli_fetch_assoc($query);

$total_hari_ini = $data['total'];


/* =========================
   TOTAL SELURUH PENERIMAAN
========================= */

$query = mysqli_query(
    $koneksi,
    "SELECT COUNT(*) AS total
     FROM absensi"
);

$data = mysqli_fetch_assoc($query);

$total_penerimaan = $data['total'];


/* =========================
   AKTIVITAS TERBARU
========================= */

$query_terbaru = mysqli_query(
    $koneksi,
    "SELECT
        absensi.tanggal,
        absensi.waktu,
        karyawan.nama,
        karyawan.kategori_dansos
     FROM absensi
     INNER JOIN karyawan
     ON absensi.anggota = karyawan.id
     ORDER BY absensi.id DESC
     LIMIT 5"
);


/* =========================
   KATEGORI DANSOS
========================= */

$query_kategori = mysqli_query(
    $koneksi,
    "SELECT
        karyawan.kategori_dansos,
        COUNT(absensi.id) AS jumlah
     FROM absensi
     INNER JOIN karyawan
     ON absensi.anggota = karyawan.id
     GROUP BY karyawan.kategori_dansos
     ORDER BY jumlah DESC"
);

?>

<!DOCTYPE html>
<html>

<head>

    <title>Dashboard - Dansos PUK OMRON</title>

    <link rel="stylesheet" href="css/style.css?v=2">

</head>

<body>

<div class="layout">

    <!-- SIDEBAR -->

    <aside class="sidebar">

        <h2>Serah Terima Dansos</h2>

        <nav>

            <a href="index.php" class="active">
                Dashboard
            </a>

            <a href="absen_rfid.php">
                Scan RFID
            </a>

            <a href="karyawan.php">
                Data Anggota
            </a>

            <a href="tambah_karyawan.php">
                Tambah Anggota
            </a>

            <a href="absensi.php">
                Riwayat Dansos
            </a>

            <a href="rekap.php">
                Rekap & Export
            </a>

        </nav>

    </aside>


    <!-- KONTEN -->

    <main class="content">

        <h1>Dashboard</h1>

        <p>
            Monitoring Serah Terima Dansos PUK OMRON.
        </p>


        <!-- STATISTIK -->

        <div class="dashboard-cards">


            <!-- TOTAL ANGGOTA -->

            <div class="dashboard-card">

                <div class="dashboard-icon">
                    👥
                </div>

                <div>

                    <span class="dashboard-label">
                        Total Anggota
                    </span>

                    <strong>
                        <?php echo $total_anggota; ?>
                    </strong>

                </div>

            </div>


            <!-- HARI INI -->

            <div class="dashboard-card">

                <div class="dashboard-icon">
                    🎁
                </div>

                <div>

                    <span class="dashboard-label">
                        Penerimaan Hari Ini
                    </span>

                    <strong>
                        <?php echo $total_hari_ini; ?>
                    </strong>

                </div>

            </div>


            <!-- TOTAL -->

            <div class="dashboard-card">

                <div class="dashboard-icon">
                    📋
                </div>

                <div>

                    <span class="dashboard-label">
                        Total Penerimaan
                    </span>

                    <strong>
                        <?php echo $total_penerimaan; ?>
                    </strong>

                </div>

            </div>


        </div>


        <!-- DUA KOLOM -->

        <div class="dashboard-grid">


            <!-- AKTIVITAS TERBARU -->

            <div class="dashboard-panel">

                <div class="panel-header">

                    <h2>Aktivitas Terbaru</h2>

                    <a href="absensi.php">
                        Lihat semua
                    </a>

                </div>


                <div class="activity-list">

                    <?php if (mysqli_num_rows($query_terbaru) > 0) { ?>

                        <?php while ($aktivitas = mysqli_fetch_assoc($query_terbaru)) { ?>

                            <div class="activity-item">

                                <div class="activity-time">

                                    <?php
                                    echo date(
                                        "H:i",
                                        strtotime($aktivitas['waktu'])
                                    );
                                    ?>

                                </div>


                                <div class="activity-info">

                                    <strong>
                                        <?php echo htmlspecialchars($aktivitas['nama']); ?>
                                    </strong>

                                    <span>
                                        <?php echo htmlspecialchars($aktivitas['kategori_dansos']); ?>
                                    </span>

                                </div>

                            </div>

                        <?php } ?>

                    <?php } else { ?>

                        <div class="empty-data">
                            Belum ada penerimaan.
                        </div>

                    <?php } ?>

                </div>

            </div>


            <!-- KATEGORI -->

            <div class="dashboard-panel">

                <div class="panel-header">

                    <h2>Kategori Dansos</h2>

                </div>


                <div class="category-list">

                    <?php if (mysqli_num_rows($query_kategori) > 0) { ?>

                        <?php while ($kategori = mysqli_fetch_assoc($query_kategori)) { ?>

                            <div class="category-item">

                                <span>
                                    <?php echo htmlspecialchars($kategori['kategori_dansos']); ?>
                                </span>

                                <strong>
                                    <?php echo $kategori['jumlah']; ?>
                                </strong>

                            </div>

                        <?php } ?>

                    <?php } else { ?>

                        <div class="empty-data">
                            Belum ada data kategori.
                        </div>

                    <?php } ?>

                </div>

            </div>


        </div>


        <!-- AKSES CEPAT -->

        <div class="quick-dashboard">

            <h2>Akses Cepat</h2>

            <div class="quick-dashboard-buttons">

                <a href="absen_rfid.php">
                    🪪
                    <span>Scan RFID</span>
                </a>

                <a href="karyawan.php">
                    👥
                    <span>Data Anggota</span>
                </a>

                <a href="rekap.php">
                    📊
                    <span>Rekap & Export</span>
                </a>

            </div>

        </div>


        <?php include "footer.php"; ?>

    </main>

</div>

</body>

</html>