<?php

include "auth.php";
include "koneksi.php";

if (!isset($_GET['id'])) {
    die("ID anggota tidak ditemukan.");
}

$id = $_GET['id'];


/* =========================
   AMBIL DATA ANGGOTA
========================= */

$query = mysqli_query(
    $koneksi,
    "SELECT * FROM karyawan WHERE id = '$id'"
);

$anggota = mysqli_fetch_assoc($query);

if (!$anggota) {
    die("Data anggota tidak ditemukan.");
}


/* =========================
   PROSES UPDATE
========================= */

$pesan = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nama = mysqli_real_escape_string(
        $koneksi,
        $_POST['nama']
    );

    $nik = mysqli_real_escape_string(
        $koneksi,
        $_POST['nik']
    );

    $rfid_uid = mysqli_real_escape_string(
        $koneksi,
        $_POST['rfid_uid']
    );

    $kategori_dansos = mysqli_real_escape_string(
        $koneksi,
        $_POST['kategori_dansos']
    );


    /* =========================
       CEK RFID DUPLIKAT
    ========================= */

    $cek_rfid = mysqli_query(
        $koneksi,
        "SELECT id
         FROM karyawan
         WHERE rfid_uid = '$rfid_uid'
         AND id != '$id'"
    );

    if (mysqli_num_rows($cek_rfid) > 0) {

        $pesan = "RFID tersebut sudah digunakan oleh anggota lain.";

    } else {

        $update = mysqli_query(
            $koneksi,
            "UPDATE karyawan
             SET
                nama = '$nama',
                nik = '$nik',
                rfid_uid = '$rfid_uid',
                kategori_dansos = '$kategori_dansos'
             WHERE id = '$id'"
        );


        if ($update) {

            header("Location: karyawan.php");
            exit;

        } else {

            $pesan = "Data gagal diperbarui.";

        }

    }
}

?>

<!DOCTYPE html>
<html>

<head>

    <title>Edit Anggota</title>

    <link rel="stylesheet" href="css/style.css">

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

            <a href="absen_rfid.php">
                Scan RFID
            </a>

            <a href="karyawan.php" class="active">
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

        <h1>Edit Anggota</h1>

        <p>
            Ubah data anggota yang sudah terdaftar.
        </p>


        <?php if ($pesan != "") { ?>

            <div class="pesan">
                <?php echo $pesan; ?>
            </div>

        <?php } ?>


        <div class="rfid-box">

            <h2>Data Anggota</h2>

            <form method="POST">


                <label>Nama Anggota</label>

                <input
                    type="text"
                    name="nama"
                    value="<?php echo htmlspecialchars($anggota['nama']); ?>"
                    required
                >


                <label>NIK</label>

                <input
                    type="text"
                    name="nik"
                    value="<?php echo htmlspecialchars($anggota['nik']); ?>"
                    required
                >


                <label>RFID UID</label>

                <input
                    type="text"
                    name="rfid_uid"
                    value="<?php echo htmlspecialchars($anggota['rfid_uid']); ?>"
                    required
                >


                <label>Kategori Dansos</label>

                <input
                    type="text"
                    name="kategori_dansos"
                    value="<?php echo htmlspecialchars($anggota['kategori_dansos']); ?>"
                    required
                >


                <button type="submit">
                    Simpan Perubahan
                </button>

            </form>

        </div>


        <?php include "footer.php"; ?>

    </main>

</div>

</body>

</html>