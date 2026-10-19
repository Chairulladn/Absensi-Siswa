<?php

date_default_timezone_set("Asia/Jakarta");

$host = "localhost";
$user = "root";
$password = "";
$database = "absensi_pt";

$koneksi = mysqli_connect($host, $user, $password, $database);

if (!$koneksi) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

?>