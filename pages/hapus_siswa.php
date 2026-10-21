
<?php
require_once __DIR__ . "/../includes/auth.php";
require_once __DIR__ . "/../config/koneksi.php";

// Hanya menerima permintaan POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: siswa.php");
    exit;
}

// Periksa CSRF token
$token = $_POST["csrf_token"] ?? "";

if (
    empty($_SESSION["csrf_token"]) ||
    !hash_equals($_SESSION["csrf_token"], $token)
) {
    header("Location: siswa.php?status=gagal_hapus");
    exit;
}

// Ambil ID siswa
$id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);

if (!$id || $id <= 0) {
    header("Location: siswa.php?status=gagal_hapus");
    exit;
}

// Hapus siswa
$stmt = mysqli_prepare(
    $koneksi,
    "DELETE FROM siswa WHERE id = ?"
);

mysqli_stmt_bind_param($stmt, "i", $id);

$berhasil = mysqli_stmt_execute($stmt);
$terhapus = mysqli_stmt_affected_rows($stmt);

mysqli_stmt_close($stmt);

if ($berhasil && $terhapus > 0) {
    header("Location: siswa.php?status=dihapus");
} else {
    header("Location: siswa.php?status=gagal_hapus");
}

exit;
?>
