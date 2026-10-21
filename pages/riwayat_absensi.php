
<?php
require_once __DIR__ . "/../includes/auth.php";
require_once __DIR__ . "/../config/koneksi.php";

date_default_timezone_set("Asia/Jakarta");

$tanggal = $_GET["tanggal"] ?? date("Y-m-d");
$kelas = $_GET["kelas"] ?? "";

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal)) {
    $tanggal = date("Y-m-d");
}

// Ambil daftar kelas
$daftar_kelas = mysqli_query(
    $koneksi,
    "SELECT DISTINCT kelas FROM siswa ORDER BY kelas"
);

// Ambil riwayat berdasarkan filter
$stmt = mysqli_prepare(
    $koneksi,
    "SELECT
        siswa.nama,
        siswa.kelas,
        siswa.jurusan,
        absensi.tanggal,
        absensi.waktu,
        absensi.status
     FROM absensi
     JOIN siswa ON absensi.siswa_id = siswa.id
     WHERE absensi.tanggal = ?
       AND (? = '' OR siswa.kelas = ?)
     ORDER BY siswa.kelas ASC, siswa.nama ASC"
);

mysqli_stmt_bind_param(
    $stmt,
    "sss",
    $tanggal,
    $kelas,
    $kelas
);

mysqli_stmt_execute($stmt);
$hasil = mysqli_stmt_get_result($stmt);

$riwayat = [];

$jumlah = [
    "Hadir" => 0,
    "Izin" => 0,
    "Sakit" => 0,
    "Alpa" => 0
];


while ($row = mysqli_fetch_assoc($hasil)) {
    $riwayat[] = $row;

    if (isset($jumlah[$row["status"]])) {
        $jumlah[$row["status"]]++;
    }
}

mysqli_stmt_close($stmt);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Riwayat Absensi | Absensi Siswa</title>
    <link rel="stylesheet" href="assets/css/dark.css?v=2">
</head>

<body>

<div class="layout">

    <?php require __DIR__ . "/../includes/sidebar.php"; ?>

    <main class="content">

<?php require __DIR__ . "/../includes/topbar.php"; ?>

        <h1>Riwayat Absensi</h1>
        <p>Lihat data kehadiran siswa berdasarkan tanggal dan kelas.</p>

        <div class="form-box">
            <form method="GET">

                <label for="tanggal">Tanggal</label>
                <input
                    type="date"
                    id="tanggal"
                    name="tanggal"
                    value="<?= htmlspecialchars($tanggal) ?>"
                    required
                >

                <label for="kelas">Kelas</label>
                <select id="kelas" name="kelas">
                    <option value="">Semua Kelas</option>

                    <?php while ($row = mysqli_fetch_assoc($daftar_kelas)): ?>
                        <option
                            value="<?= htmlspecialchars($row['kelas']) ?>"
                            <?= $kelas === $row['kelas'] ? 'selected' : '' ?>
                        >
                            <?= htmlspecialchars($row['kelas']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>

                <button type="submit" class="btn-primary">
                    Tampilkan Riwayat
                </button>

            </form>
        </div>

        <div class="ringkasan-absensi">
            <div class="ringkasan-item">
                <span>Hadir</span>
                <strong><?= $jumlah["Hadir"] ?></strong>
            </div>

            <div class="ringkasan-item">
                <span>Izin</span>
                <strong><?= $jumlah["Izin"] ?></strong>
            </div>

            <div class="ringkasan-item">
                <span>Sakit</span>
                <strong><?= $jumlah["Sakit"] ?></strong>
            </div>
            <div class="ringkasan-item">
                <span>Alpa</span>
                <strong><?= $jumlah["Alpa"] ?></strong>
            </div>

        </div>

        <div class="table-box">

            <h2>Data Riwayat Absensi</h2>

            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Siswa</th>
                        <th>Kelas</th>
                        <th>Jurusan</th>
                        <th>Tanggal</th>
                        <th>Waktu</th>
                        <th>Status</th>
                    </tr>
                </thead>

                <tbody>

                    <?php if (count($riwayat) === 0): ?>
                        <tr>
                            <td colspan="7">
                                Belum ada data absensi pada tanggal ini.
                            </td>
                        </tr>
                    <?php else: ?>

                        <?php foreach ($riwayat as $no => $row): ?>
                            <tr>
                                <td><?= $no + 1 ?></td>
                                <td><?= htmlspecialchars($row["nama"]) ?></td>
                                <td><?= htmlspecialchars($row["kelas"]) ?></td>
                                <td><?= htmlspecialchars($row["jurusan"]) ?></td>
                                <td>
                                    <?= date("d-m-Y", strtotime($row["tanggal"])) ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars($row["waktu"]) ?>
                                </td>
                                <td>
                                    <span class="status-absensi status-<?= strtolower($row["status"]) ?>">
                                        <?= htmlspecialchars($row["status"]) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                    <?php endif; ?>

                </tbody>
            </table>

        </div>

        <?php require __DIR__ . "/../includes/footer.php"; ?>

    </main>
</div>

<script src="assets/js/topbar.js?v=1"></script>
</body>
</html>
