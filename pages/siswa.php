
<?php
require_once __DIR__ . "/../includes/auth.php";
require_once __DIR__ . "/../config/koneksi.php";

if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

$query = mysqli_query(
    $koneksi,
   "SELECT id, nama, kelas, jurusan
   FROM siswa
   ORDER BY id DESC"
);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Siswa | Absensi Siswa</title>
<link rel="stylesheet" href="assets/css/dark.css?v=1">
</head>
<body>

<div class="layout">

    <?php require __DIR__ . "/../includes/sidebar.php"; ?>

    <main class="content">

<?php require __DIR__ . "/../includes/topbar.php"; ?>

        <h1>Data Siswa</h1>

<?php
$status = $_GET["status"] ?? "";

$pesan_status = [
    "berhasil" => "Siswa berhasil ditambahkan!",
    "diubah" => "Data siswa berhasil diperbarui!",
    "dihapus" => "Siswa berhasil dihapus!",
    "ada_absensi" => "Siswa tidak dapat dihapus karena sudah memiliki riwayat absensi.",
    "gagal_hapus" => "Gagal menghapus siswa."
];

if (isset($pesan_status[$status])):
    $error = in_array($status, ["ada_absensi", "gagal_hapus"]);
?>
    <p class="<?= $error ? 'pesan-error' : 'pesan-sukses' ?>">
        <?= htmlspecialchars($pesan_status[$status]) ?>
    </p>
<?php endif; ?>

        <p>Daftar siswa yang terdaftar dalam sistem.</p>

        <div class="table-box">
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Siswa</th>
                        <th>Kelas</th>
                        <th>Jurusan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    <?php $no = 1; ?>

                    <?php while ($siswa = mysqli_fetch_assoc($query)): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= htmlspecialchars($siswa['nama']) ?></td>
                            <td><?= htmlspecialchars($siswa['kelas']) ?></td>
                            <td><?= htmlspecialchars($siswa['jurusan']) ?></td>
                            <td>
                                <a href="edit_siswa.php?id=<?= $siswa['id'] ?>"
                                   class="btn-edit">Edit</a>

<form
    action="hapus_siswa.php"
    method="POST"
    style="display:inline;"
    onsubmit="return confirm('Yakin ingin menghapus siswa ini?')"
>
    <input
        type="hidden"
        name="id"
        value="<?= (int)$siswa['id'] ?>"
    >

    <input
        type="hidden"
        name="csrf_token"
        value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>"
    >

    <button type="submit" class="btn-hapus">
        Hapus
    </button>
</form>

                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <?php require __DIR__ . "/../includes/footer.php"; ?>

    </main>
</div>

<script src="assets/js/topbar.js?v=1"></script>
</body>
</html>
