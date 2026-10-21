<?php

$host = "localhost";
$user = "root";
$pass = "";
$db   = "absensi_siswa";

$koneksi = mysqli_connect(
    $host,
    $user,
    $pass,
    $db
);

if (!$koneksi) {
    die("Koneksi database gagal.");
}

mysqli_set_charset($koneksi, "utf8mb4");