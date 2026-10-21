
<?php
require_once __DIR__ . "/../includes/auth.php";
require_once __DIR__ . "/../config/koneksi.php";

$pesan = "";
$nama = "";
$kelas = "";
$jurusan = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nama = trim($_POST["nama"] ?? "");
    $kelas = trim($_POST["kelas"] ?? "");
    $jurusan = trim($_POST["jurusan"] ?? "");

    if ($nama === "" || $kelas === "" || $jurusan === "") {
        $pesan = "Semua kolom wajib diisi!";
    } else {

        $stmt = mysqli_prepare(
            $koneksi,
            "INSERT INTO siswa (nama, kelas, jurusan)
             VALUES (?, ?, ?)"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "sss",
            $nama,
            $kelas,
            $jurusan
        );

        if (mysqli_stmt_execute($stmt)) {
            header("Location: siswa.php?status=berhasil");
            exit;
        } else {
            $pesan = "Gagal menambahkan siswa!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Siswa</title>
    <link rel="stylesheet" href="assets/css/dark.css?v=1">
</head>
<body>

<div class="layout">

    <?php require __DIR__ . "/../includes/sidebar.php"; ?>

    <main class="content">

<?php require __DIR__ . "/../includes/topbar.php"; ?>

        <h1>Tambah Siswa</h1>
        <p>Masukkan data siswa baru.</p>

        <?php if ($pesan !== ""): ?>
            <p class="pesan-error">
                <?= htmlspecialchars($pesan) ?>
            </p>
        <?php endif; ?>

        <div class="form-box">

           <form method="POST" autocomplete="off">

                <label>Nama Siswa</label>
                <input
                    type="text"
                    id="nama"
                    name="nama"
                    placeholder="Masukkan nama siswa"
                    maxlength="100"
                    value="<?= htmlspecialchars($nama) ?>"
                    autocomplete="off"
                    required
                >

                <label>Kelas</label>
                <input
                    type="text"
                    name="kelas"
                    placeholder="Contoh: XI TKJ 1"
                    maxlength="30"
                    value="<?= htmlspecialchars($kelas) ?>"
                    required
                >

                <label>Jurusan</label>
                <input
                    type="text"
                    name="jurusan"
                    placeholder="Contoh: TKJ"
                    maxlength="50"
                    value="<?= htmlspecialchars($jurusan) ?>"
                    required
                >

                <button type="submit" class="btn-primary">
                    Simpan Siswa
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
