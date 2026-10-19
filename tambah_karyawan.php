<?php

include "auth.php";
include "koneksi.php";

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
         WHERE rfid_uid = '$rfid_uid'"
    );

    if (mysqli_num_rows($cek_rfid) > 0) {

        $pesan = "RFID tersebut sudah terdaftar!";

    } else {

        $query = mysqli_query(
            $koneksi,
            "INSERT INTO karyawan
            (nama, nik, rfid_uid, kategori_dansos)
            VALUES
            ('$nama', '$nik', '$rfid_uid', '$kategori_dansos')"
        );

        if ($query) {

            $pesan = "Anggota berhasil ditambahkan!";

        } else {

            $pesan = "Data gagal ditambahkan.";

        }
    }
}

?>

<!DOCTYPE html>
<html>

<head>

    <title>Tambah Anggota</title>

    <link rel="stylesheet" href="css/style.css">

</head>

<body>

    <div class="layout">

        <!-- Sidebar -->

        <aside class="sidebar">

            <h2>Serah Terima Dansos</h2>

            <nav>

                <a href="index.php">
                    Dashboard
                </a>

                <a href="absen_rfid.php">
                    Scan RFID
                </a>

                <a href="karyawan.php">
                    Data Anggota
                </a>

                <a href="tambah_karyawan.php" class="active">
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


        <!-- Konten -->

        <main class="content">

            <h1>Tambah Anggota</h1>

            <p>
                Tambahkan data penerima dansos ke dalam sistem.
            </p>

            <?php if ($pesan != "") { ?>
                <div class="pesan">
                    <?php echo $pesan; ?>
                </div>
            <?php } ?>

            <div class="rfid-box">

                <form method="POST">

    <label>Nama Anggota:</label>
    <input
        type="text"
        name="nama"
        required
    >

    <label>NIK:</label>
    <input
        type="text"
        name="nik"
        required
    >

    <label>RFID UID:</label>
    <input
        type="text"
        name="rfid_uid"
        required
    >

    <label>Kategori Dansos:</label>
    <input
        type="text"
        name="kategori_dansos"
        required
    >

    <button type="submit">
        Tambah Anggota
    </button>

</form>
<?php include "footer.php"; ?>
            </div>

        </main>

    </div>

</body>

</html>