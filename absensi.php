<?php

include "koneksi.php";

// Bulan dan tahun sekarang
$bulan = isset($_GET['bulan']) ? $_GET['bulan'] : date("m");
$tahun = isset($_GET['tahun']) ? $_GET['tahun'] : date("Y");

// Ambil data sesuai bulan dan tahun
$data = mysqli_query(
    $koneksi,
    "SELECT absensi.tanggal,
            absensi.waktu,
            karyawan.nama,
            karyawan.rfid_uid,
            karyawan.kategori_dansos
     FROM absensi
     JOIN karyawan
     ON absensi.anggota = karyawan.id
     WHERE MONTH(absensi.tanggal) = '$bulan'
     AND YEAR(absensi.tanggal) = '$tahun'
     ORDER BY absensi.tanggal DESC, absensi.waktu DESC"
);

?>

<!DOCTYPE html>

<html>

<head>

    <title>Riwayat Serah Terima</title>

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

                <a href="tambah_karyawan.php">
                    Tambah Anggota
                </a>

                <a href="absensi.php" class="active">
                    Riwayat Dansos
                </a>

                <a href="rekap.php">
                    Rekap & Export
                </a>

            </nav>

        </aside>


        <!-- Konten -->

        <main class="content">

            <h1>Riwayat Dansos</h1>

            <p>
                Data penerimaan dansos yang telah tercatat.
            </p>

            <form method="GET" class="filter-riwayat">

    <div>
        <label>Bulan</label>

        <select name="bulan">
            <option value="01" <?php if ($bulan == "01") echo "selected"; ?>>Januari</option>
            <option value="02" <?php if ($bulan == "02") echo "selected"; ?>>Februari</option>
            <option value="03" <?php if ($bulan == "03") echo "selected"; ?>>Maret</option>
            <option value="04" <?php if ($bulan == "04") echo "selected"; ?>>April</option>
            <option value="05" <?php if ($bulan == "05") echo "selected"; ?>>Mei</option>
            <option value="06" <?php if ($bulan == "06") echo "selected"; ?>>Juni</option>
            <option value="07" <?php if ($bulan == "07") echo "selected"; ?>>Juli</option>
            <option value="08" <?php if ($bulan == "08") echo "selected"; ?>>Agustus</option>
            <option value="09" <?php if ($bulan == "09") echo "selected"; ?>>September</option>
            <option value="10" <?php if ($bulan == "10") echo "selected"; ?>>Oktober</option>
            <option value="11" <?php if ($bulan == "11") echo "selected"; ?>>November</option>
            <option value="12" <?php if ($bulan == "12") echo "selected"; ?>>Desember</option>
        </select>
    </div>

    <div>
        <label>Tahun</label>

        <select name="tahun">

            <?php
            $tahun_sekarang = date("Y");

            for ($i = $tahun_sekarang - 2; $i <= $tahun_sekarang + 2; $i++) {
            ?>

                <option value="<?php echo $i; ?>"
                    <?php if ($tahun == $i) echo "selected"; ?>>
                    <?php echo $i; ?>
                </option>

            <?php } ?>

        </select>
    </div>

    <button type="submit">
        Tampilkan
    </button>

</form>

            <div class="table-box">

    <table>
        <tr>
            <th>Tanggal</th>
            <th>Waktu</th>
            <th>Nama Anggota</th>
            <th>RFID</th>
            <th>Kategori Dansos</th>
        </tr>

        <?php
        while ($data_absen = mysqli_fetch_assoc($data)) {
        ?>
        <tr>
            <td>
                <?php echo $data_absen['tanggal']; ?>
            </td>

            <td>
                <?php echo date("H:i:s", strtotime($data_absen['waktu'])); ?>
            </td>

            <td>
                <?php echo $data_absen['nama']; ?>
            </td>

            <td>
                <?php echo $data_absen['rfid_uid']; ?>
            </td>

            <td>
                <?php echo $data_absen['kategori_dansos']; ?>
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