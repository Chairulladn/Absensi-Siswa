<?php

include "koneksi.php";

$mode = isset($_GET['mode']) ? $_GET['mode'] : "semua";
$bulan = isset($_GET['bulan']) ? $_GET['bulan'] : date("m");
$tahun = isset($_GET['tahun']) ? $_GET['tahun'] : date("Y");

$nama_bulan = array(
    "01" => "Januari",
    "02" => "Februari",
    "03" => "Maret",
    "04" => "April",
    "05" => "Mei",
    "06" => "Juni",
    "07" => "Juli",
    "08" => "Agustus",
    "09" => "September",
    "10" => "Oktober",
    "11" => "November",
    "12" => "Desember"
);


/* =========================
   AMBIL DATA
========================= */

if ($mode == "bulan") {

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
         ORDER BY absensi.tanggal ASC, absensi.waktu ASC"
    );

    $periode = $nama_bulan[$bulan] . " " . $tahun;

    $nama_file =
        "Rekap_Dansos_" .
        $nama_bulan[$bulan] .
        "_" .
        $tahun .
        ".xls";

} else {

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
         ORDER BY absensi.tanggal ASC, absensi.waktu ASC"
    );

    $periode = "Semua Data";

    $nama_file = "Rekap_Dansos_Semua_Data.xls";
}


/* =========================
   EXPORT EXCEL
========================= */

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=\"$nama_file\"");
header("Pragma: no-cache");
header("Expires: 0");

?>

<html>

<head>

<meta charset="UTF-8">

<style>

@page {
    size: landscape;
    margin: 0.5in;
}

body {
    font-family: Arial, sans-serif;
    font-size: 11pt;
    color: #222222;
}

/* =========================
   JUDUL
========================= */

.judul {
    font-size: 22pt;
    font-weight: bold;
    text-align: center;
    color: #1f2937;
}

.subjudul {
    font-size: 12pt;
    font-weight: bold;
    text-align: center;
    color: #4b5563;
}

.jenis-laporan {
    font-size: 11pt;
    text-align: center;
    color: #6b7280;
}


/* =========================
   INFO
========================= */

.info {
    background-color: #f3f4f6;
    border: 1px solid #d1d5db;
    font-weight: bold;
    text-align: center;
}

.info-label {
    color: #6b7280;
    font-weight: normal;
}

.info-value {
    font-size: 13pt;
    color: #1f2937;
    font-weight: bold;
}


/* =========================
   HEADER TABEL
========================= */

.header {
    background-color: #1f2937;
    color: white;
    font-weight: bold;
    text-align: center;
    vertical-align: middle;
}


/* =========================
   DATA
========================= */

.data {
    vertical-align: middle;
}

.data-genap {
    background-color: #f8fafc;
}

.tengah {
    text-align: center;
}

.nama {
    font-weight: bold;
}


/* =========================
   TOTAL
========================= */

.total-label {
    background-color: #e5e7eb;
    font-weight: bold;
    text-align: right;
}

.total-value {
    background-color: #e5e7eb;
    font-weight: bold;
    text-align: center;
}


/* =========================
   FOOTER
========================= */

.footer {
    font-size: 9pt;
    color: #9ca3af;
    font-style: italic;
}

</style>

</head>

<body>

<table
    width="100%"
    cellspacing="0"
    cellpadding="7"
    border="0"
>


<!-- =====================================
     HEADER LAPORAN
===================================== -->

<tr>

    <td colspan="6" class="judul">
        REKAP SERAH TERIMA DANSOS
    </td>

</tr>

<tr>

    <td colspan="6" class="subjudul">
        BIDANG V | INFOKOMSOSEK
    </td>

</tr>

<tr>

    <td colspan="6" class="jenis-laporan">
        Laporan Penerimaan Dansos
    </td>

</tr>

<tr>
    <td colspan="6" height="10"></td>
</tr>


<!-- =====================================
     INFORMASI LAPORAN
===================================== -->

<tr>

    <td colspan="2" class="info">

        <span class="info-label">
            PERIODE
        </span>

        <br>

        <span class="info-value">
            <?php echo $periode; ?>
        </span>

    </td>


    <td colspan="2" class="info">

        <span class="info-label">
            TANGGAL CETAK
        </span>

        <br>

        <span class="info-value">
            <?php echo date("d/m/Y"); ?>
        </span>

    </td>


    <td colspan="2" class="info">

        <span class="info-label">
            TOTAL DATA
        </span>

        <br>

        <span class="info-value">
            <?php

            // sementara akan diisi setelah data dihitung
            echo "Laporan";

            ?>
        </span>

    </td>

</tr>


<tr>
    <td colspan="6" height="15"></td>
</tr>


<!-- =====================================
     TABEL
===================================== -->

<table
    width="100%"
    cellspacing="0"
    cellpadding="7"
    border="1"
    style="border-collapse: collapse;"
>


<tr class="header">

    <th width="7%">
        No
    </th>

    <th width="15%">
        Tanggal
    </th>

    <th width="15%">
        Waktu
    </th>

    <th width="25%">
        Nama Anggota
    </th>

    <th width="18%">
        RFID
    </th>

    <th width="20%">
        Kategori Dansos
    </th>

</tr>


<?php

$no = 1;
$total = 0;

while ($row = mysqli_fetch_assoc($data)) {

    $total++;

    $tanggal = date(
        "d/m/Y",
        strtotime($row['tanggal'])
    );

    $waktu = date(
        "H:i:s",
        strtotime($row['waktu'])
    );

    $class_baris = ($no % 2 == 0)
        ? "data data-genap"
        : "data";

?>

<tr class="<?php echo $class_baris; ?>">

    <td class="tengah">
        <?php echo $no; ?>
    </td>

    <td class="tengah">
        <?php echo $tanggal; ?>
    </td>

    <td class="tengah">
        <?php echo $waktu; ?>
    </td>

    <td class="nama">
        <?php echo $row['nama']; ?>
    </td>

    <td class="tengah">
        <?php echo $row['rfid_uid']; ?>
    </td>

    <td>
        <?php echo $row['kategori_dansos']; ?>
    </td>

</tr>

<?php

    $no++;

}

?>


<!-- =====================================
     TOTAL
===================================== -->

<tr>

    <td
        colspan="5"
        class="total-label"
    >
        TOTAL PENERIMAAN
    </td>

    <td
        class="total-value"
    >
        <?php echo $total; ?> DATA
    </td>

</tr>

</table>


<br>


<!-- =====================================
     FOOTER
===================================== -->

<table
    width="100%"
    border="0"
    cellspacing="0"
    cellpadding="5"
>

</table>


</body>

</html>