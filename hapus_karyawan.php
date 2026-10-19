<?php

include "auth.php";
include "koneksi.php";

if (!isset($_GET['id'])) {
    die("ID anggota tidak ditemukan.");
}

$id = intval($_GET['id']);

// Cek apakah anggota punya riwayat
$cek = mysqli_query(
    $koneksi,
    "SELECT COUNT(*) AS jumlah
     FROM absensi
     WHERE anggota = '$id'"
);

$data = mysqli_fetch_assoc($cek);

if ($data['jumlah'] > 0) {

    echo "<script>
            alert('Anggota ini sudah memiliki riwayat penerimaan dan tidak dapat dihapus.');
            window.location.href = 'karyawan.php';
          </script>";

    exit;
}

// Hapus anggota
$query = mysqli_query(
    $koneksi,
    "DELETE FROM karyawan
     WHERE id = '$id'"
);

if ($query) {

    echo "<script>
            alert('Anggota berhasil dihapus.');
            window.location.href = 'karyawan.php';
          </script>";

} else {

    echo "<script>
            alert('Gagal menghapus anggota.');
            window.location.href = 'karyawan.php';
          </script>";
}

?>