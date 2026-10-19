<?php

include "koneksi.php";

$pesan = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $rfid_uid = mysqli_real_escape_string(
        $koneksi,
        $_POST['rfid_uid']
    );

    /* =========================
       CARI ANGGOTA
    ========================= */

    $query = mysqli_query(
        $koneksi,
        "SELECT *
         FROM karyawan
         WHERE rfid_uid = '$rfid_uid'"
    );

    $penerima = mysqli_fetch_assoc($query);


    /* =========================
       RFID TIDAK TERDAFTAR
    ========================= */

    if (!$penerima) {

        $pesan = "
            <div class='result-icon result-error'>
                !
            </div>

            <div class='result-title result-title-error'>
                RFID Tidak Terdaftar
            </div>

            <div class='result-message'>
                Kartu RFID ini belum terdaftar
                dalam sistem.
            </div>
        ";

    } else {

        /* =========================
           DATA ANGGOTA
        ========================= */

        $anggota = $penerima['id'];
        $nama = $penerima['nama'];
        $kategori = $penerima['kategori_dansos'];

        $tanggal = date("Y-m-d");
        $waktu = date("H:i:s");

        $bulan = date("m");
        $tahun = date("Y");


        /* =========================
           CEK SUDAH MENERIMA BULAN INI
        ========================= */

        $cek_penerimaan = mysqli_query(
            $koneksi,
            "SELECT id
             FROM absensi
             WHERE anggota = '$anggota'
             AND MONTH(tanggal) = '$bulan'
             AND YEAR(tanggal) = '$tahun'
             LIMIT 1"
        );

        $sudah_menerima = mysqli_fetch_assoc($cek_penerimaan);


        /* =========================
           SUDAH MENERIMA BULAN INI
        ========================= */

        if ($sudah_menerima) {

            $pesan = "
                <div class='result-icon result-warning'>
                    !
                </div>

                <div class='result-title result-title-warning'>
                    Sudah Menerima Dansos
                </div>

                <div class='result-name'>
                    $nama
                </div>

                <div class='result-message'>
                    Anggota ini sudah menerima dansos
                    pada bulan ini.
                </div>
            ";


        /* =========================
           BELUM MENERIMA BULAN INI
        ========================= */

        } else {

            $simpan = mysqli_query(
                $koneksi,
                "INSERT INTO absensi
                (anggota, tanggal, waktu)
                VALUES
                ('$anggota', '$tanggal', '$waktu')"
            );


            /* =========================
               BERHASIL DISIMPAN
            ========================= */

            if ($simpan) {

                $pesan = "
                    <div class='result-icon result-success'>
                        ✓
                    </div>

                    <div class='result-title result-title-success'>
                        Absensi Berhasil
                    </div>

                    <div class='result-name'>
                        $nama
                    </div>

                    <div class='result-category'>
                        $kategori
                    </div>

                    <div class='result-time'>
                        $tanggal &nbsp;•&nbsp; $waktu
                    </div>

                    <div class='result-info'>
                        ✓ Data Absesnsi berhasil disimpan
                    </div>
                ";


            /* =========================
               GAGAL DISIMPAN
            ========================= */

            } else {

                $pesan = "
                    <div class='result-icon result-error'>
                        !
                    </div>

                    <div class='result-title result-title-error'>
                        Gagal Menyimpan
                    </div>

                    <div class='result-message'>
                        Data penerimaan tidak berhasil disimpan.
                    </div>
                ";
            }
        }
    }
}

?>

<!DOCTYPE html>
<html>

<head>

    <title>Serah Terima Dansos</title>

    <link
        rel="stylesheet"
        href="css/style.css?v=3"
    >

    <script
        src="js/script.js"
        defer
    ></script>

</head>

<body>

<div class="layout">


    <!-- SIDEBAR -->

    <aside class="sidebar">

        <h2>Serah Terima Dansos</h2>

        <nav>

            <a href="index.php">
                Dashboard
            </a>

            <a href="absen_rfid.php" class="active">
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

        <h1>Serah Terima Dansos 2026 - 2029</h1>

        <p>
            Silakan tap kartu RFID untuk mencatat
            penerimaan dansos.
        </p>


        <?php if ($pesan != "") { ?>

            <div class="pesan result-box">

                <?php echo $pesan; ?>

            </div>

        <?php } ?>


        <!-- RFID SCANNER -->

        <div class="rfid-box">

            <h2>Tap Kartu RFID</h2>

            <img
                src="images/tap.png"
                class="tap-card-image"
                alt="Tap RFID"
            >

            <p>
                Tempelkan kartu RFID pada reader
            </p>

            <form method="POST">

                <input
                    type="text"
                    name="rfid_uid"
                    id="rfid_uid"
                    autofocus
                    autocomplete="off"
                >

            </form>

        </div>


        <!-- IDENTITAS -->

        <div class="identitas-dansos">

            <h2>
                BIDANG V<br>
                InfokomSosek
            </h2>

            <img
                src="images/logo.png"
                alt="Logo"
                class="logo-dansos"
            >

        </div>


        <?php include "footer.php"; ?>

    </main>

</div>

</body>

</html>