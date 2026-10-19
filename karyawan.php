<?php

include "auth.php";
include "koneksi.php";

$data = mysqli_query(
    $koneksi,
    "SELECT * FROM karyawan"
);

?>

<!DOCTYPE html>
<html>

<head>

    <title>Data Penerima</title>

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

            <?php if (isset($_SESSION['admin_id'])) { ?>

                <a href="logout.php">
                    Logout Admin
                </a>

            <?php } else { ?>

                <a href="login.php">
                    Login Admin
                </a>

<?php } ?>

            </nav>

        </aside>


        <!-- Konten -->

        <main class="content">

            <h1>Data Anggota</h1>

            <p>
                Daftar anggota yang terdaftar dalam sistem.
            </p>


            <div class="table-box">

                <table>

                    <tr>

                        <th>ID</th>
                        <th>Nama Anggota</th>
                        <th>NIK</th>
                        <th>RFID UID</th>
                        <th>Kategori Dansos</th>
                        <th>Aksi</th>

                    </tr>


                    <?php while ($karyawan = mysqli_fetch_assoc($data)) { ?>

<tr>

    <td>
        <?php echo $karyawan['id']; ?>
    </td>

    <td>
        <?php echo $karyawan['nama']; ?>
    </td>

    <td>
        <?php echo $karyawan['nik']; ?>
    </td>

    <td>
        <?php echo $karyawan['rfid_uid']; ?>
    </td>

    <td>
        <?php echo $karyawan['kategori_dansos']; ?>
    </td>

    <td>

        <a
            href="edit_karyawan.php?id=<?php echo $karyawan['id']; ?>"
            class="btn-edit"
        >
            Edit
        </a>

        <a
            href="hapus_karyawan.php?id=<?php echo $karyawan['id']; ?>"
            onclick="return confirm('Yakin ingin menghapus anggota ini?');"
            class="btn-hapus"
        >
            Hapus
        </a>

    </td>

</tr>

<?php } ?>
                </table>

            </div>

            <?php include "footer.php"; ?>

        </main>

    </div>

</body>

</html>