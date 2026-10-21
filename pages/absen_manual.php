
<?php
require_once __DIR__ . "/../includes/auth.php";
require_once __DIR__ . "/../config/koneksi.php";

date_default_timezone_set("Asia/Jakarta");

if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

$pesan = "";
$tipe = "";
$kelas_pilih = trim($_GET["kelas"] ?? "");

// Ambil semua kelas yang terdaftar
$daftar_kelas = mysqli_query(
    $koneksi,
    "SELECT DISTINCT kelas FROM siswa ORDER BY kelas"
);

// Proses simpan absensi satu kelas
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $token = $_POST["csrf_token"] ?? "";
    $kelas_post = trim($_POST["kelas"] ?? "");
    $status_input = $_POST["status"] ?? [];

    if (
        !is_string($token) ||
        !hash_equals($_SESSION["csrf_token"], $token)
    ) {
        http_response_code(403);
        exit("Permintaan tidak valid.");
    }

    if (!is_array($status_input) || $kelas_post === "") {
        $pesan = "Data absensi tidak valid.";
        $tipe = "error";
    } else {

        $ambil = mysqli_prepare(
            $koneksi,
            "SELECT id FROM siswa WHERE kelas = ?"
        );
        mysqli_stmt_bind_param($ambil, "s", $kelas_post);
        mysqli_stmt_execute($ambil);
        $hasil = mysqli_stmt_get_result($ambil);

        $ids = [];
        while ($baris = mysqli_fetch_assoc($hasil)) {
            $ids[] = (int)$baris["id"];
        }
        mysqli_stmt_close($ambil);

       $statusValid = ["Hadir", "Izin", "Sakit", "Alpa"];
        $lengkap = count($ids) > 0;

        foreach ($ids as $id) {
            if (
                !isset($status_input[$id]) ||
                !is_string($status_input[$id]) ||
                
                !in_array($status_input[$id], $statusValid, true)

            ) {
                $lengkap = false;
                break;
            }
        }

        if (!$lengkap) {
            $pesan = "Pastikan semua siswa memiliki status.";
            $tipe = "error";
        } else {

            $tanggal = date("Y-m-d");
            $waktu = date("H:i:s");

            $simpan = mysqli_prepare(
                $koneksi,
                "INSERT INTO absensi
                 (siswa_id, tanggal, waktu, status)
                 VALUES (?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE
                    waktu = VALUES(waktu),
                    status = VALUES(status)"
            );

            try {
                mysqli_begin_transaction($koneksi);

                foreach ($ids as $id) {
                    $status = $status_input[$id];

                    mysqli_stmt_bind_param(
                        $simpan,
                        "isss",
                        $id,
                        $tanggal,
                        $waktu,
                        $status
                    );

                    mysqli_stmt_execute($simpan);
                }

                mysqli_commit($koneksi);

                $pesan = "Absensi satu kelas berhasil disimpan!";
                $tipe = "success";
                $kelas_pilih = $kelas_post;

            } catch (mysqli_sql_exception $e) {
                mysqli_rollback($koneksi);
                $pesan = "Gagal menyimpan absensi.";
                $tipe = "error";
            }

            mysqli_stmt_close($simpan);
        }
    }
}

// Ambil siswa sesuai kelas pilihan
$siswa_kelas = [];

if ($kelas_pilih !== "") {
    $stmt = mysqli_prepare(
        $koneksi,
        "SELECT id, nama, kelas
         FROM siswa
         WHERE kelas = ?
         ORDER BY nama ASC"
    );

    mysqli_stmt_bind_param($stmt, "s", $kelas_pilih);
    mysqli_stmt_execute($stmt);
    $hasil = mysqli_stmt_get_result($stmt);

    while ($siswa = mysqli_fetch_assoc($hasil)) {
        $siswa_kelas[] = $siswa;
    }

    mysqli_stmt_close($stmt);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Absensi Manual | Absensi Siswa</title>
    <link rel="stylesheet" href="assets/css/dark.css?v=1">
</head>
<body>

<div class="layout">

    <?php require __DIR__ . "/../includes/sidebar.php"; ?>

    <main class="content">

<?php require __DIR__ . "/../includes/topbar.php"; ?>

        <h1>Absensi Manual</h1>
        <p>Input kehadiran seluruh siswa dalam satu kelas.</p>

        <?php if ($pesan !== ""): ?>
            <p class="<?= $tipe === 'success'
                ? 'pesan-sukses'
                : 'pesan-error' ?>">
                <?= htmlspecialchars($pesan) ?>
            </p>
        <?php endif; ?>

        <div class="form-box">

            <form method="GET">

                <label for="kelas">Pilih Kelas</label>

                <select name="kelas" id="kelas" required>
                    <option value="">-- Pilih Kelas --</option>

                    <?php while ($row = mysqli_fetch_assoc($daftar_kelas)): ?>
                        <option
                            value="<?= htmlspecialchars($row['kelas']) ?>"
                            <?= $kelas_pilih === $row['kelas']
                                ? 'selected'
                                : '' ?>
                        >
                            <?= htmlspecialchars($row['kelas']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>

                <button type="submit" class="btn-primary">
                    Tampilkan Siswa
                </button>

            </form>

        </div>

        <?php if ($kelas_pilih !== ""): ?>

            <div class="table-box">

                <h2>
                    Absensi Kelas
                    <?= htmlspecialchars($kelas_pilih) ?>
                </h2>

                <?php if (count($siswa_kelas) === 0): ?>
                    <p>Tidak ada siswa di kelas ini.</p>
                <?php else: ?>

                    <form method="POST">

                        <input
                            type="hidden"
                            name="kelas"
                            value="<?= htmlspecialchars($kelas_pilih) ?>"
                        >

                        <input
                            type="hidden"
                            name="csrf_token"
                            value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>"
                        >

                        <table>
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Siswa</th>
                                    <th>Status Kehadiran</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php foreach ($siswa_kelas as $no => $siswa): ?>
                                    <tr>
                                        <td><?= $no + 1 ?></td>

                                        <td>
                                            <?= htmlspecialchars($siswa["nama"]) ?>
                                        </td>

                                        <td>
                                            <select
                                                name="status[<?= (int)$siswa['id'] ?>]"
                                                required
                                            >
                                                <option value="Hadir">Hadir</option>
                                                <option value="Izin">Izin</option>
                                                <option value="Sakit">Sakit</option>
                                                <option value="Alpa">Alpa</option>
                                            </select>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>

                        <button type="submit" class="btn-primary">
                            Simpan Absensi Satu Kelas
                        </button>

                    </form>

                <?php endif; ?>

            </div>

        <?php endif; ?>

        <?php require __DIR__ . "/../includes/footer.php"; ?>

    </main>

</div>

<script src="assets/js/topbar.js?v=1"></script>
</body>
</html>
