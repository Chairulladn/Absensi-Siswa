
<?php
require_once __DIR__ . "/../includes/auth.php";
require_once __DIR__ . "/../config/koneksi.php";

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$id) {
    header("Location: siswa.php");
    exit;
}

$stmt = mysqli_prepare(
    $koneksi,
    "SELECT id, nama, kelas, jurusan FROM siswa WHERE id = ?"
);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$siswa = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$siswa) {
    header("Location: siswa.php");
    exit;
}

$nama = $siswa["nama"];
$kelas = $siswa["kelas"];
$jurusan = $siswa["jurusan"];
$pesan = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nama = trim($_POST["nama"] ?? "");
    $kelas = trim($_POST["kelas"] ?? "");
    $jurusan = trim($_POST["jurusan"] ?? "");

    if ($nama === "" || $kelas === "" || $jurusan === "") {
        $pesan = "Semua kolom wajib diisi!";
    } else {
        $update = mysqli_prepare(
            $koneksi,
            "UPDATE siswa
             SET nama = ?, kelas = ?, jurusan = ?
             WHERE id = ?"
        );

        mysqli_stmt_bind_param(
            $update,
            "sssi",
            $nama,
            $kelas,
            $jurusan,
            $id
        );

        if (mysqli_stmt_execute($update)) {
            header("Location: siswa.php?status=diubah");
            exit;
        }

        $pesan = "Gagal mengubah data siswa.";
        mysqli_stmt_close($update);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Siswa | Absensi Siswa</title>
    <link rel="stylesheet" href="assets/css/dark.css?v=1">
</head>
<body>

<div class="layout">
    <?php require __DIR__ . "/../includes/sidebar.php"; ?>

    <main class="content">

<?php require __DIR__ . "/../includes/topbar.php"; ?>
        <h1>Edit Siswa</h1>
        <p>Ubah data siswa yang sudah terdaftar.</p>

        <?php if ($pesan !== ""): ?>
            <p class="pesan-error">
                <?= htmlspecialchars($pesan) ?>
            </p>
        <?php endif; ?>

        <div class="form-box">
            <form method="POST">

                <label for="nama">Nama Siswa</label>
                <input
                    type="text"
                    id="nama"
                    name="nama"
                    value="<?= htmlspecialchars($nama) ?>"
                    maxlength="100"
                    required
                >

                <label for="kelas">Kelas</label>
                <input
                    type="text"
                    id="kelas"
                    name="kelas"
                    value="<?= htmlspecialchars($kelas) ?>"
                    maxlength="30"
                    required
                >

                <label for="jurusan">Jurusan</label>
                <input
                    type="text"
                    id="jurusan"
                    name="jurusan"
                    value="<?= htmlspecialchars($jurusan) ?>"
                    maxlength="50"
                    required
                >

                <button type="submit" class="btn-primary">
                    Simpan Perubahan
                </button>

                <a href="siswa.php" class="btn-kembali">
                    Kembali
                </a>

            </form>
        </div>

        <?php require __DIR__ . "/../includes/footer.php"; ?>
    </main>
</div>

<script src="assets/js/topbar.js?v=1"></script>
</body>
</html>
