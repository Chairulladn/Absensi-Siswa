
<?php
$halaman = basename($_SERVER["SCRIPT_NAME"]);

$menu = [
    ["index.php", "Dashboard", "layout-dashboard"],
    ["absen_manual.php", "Absensi Manual", "clipboard-check"],
    ["siswa.php", "Data Siswa", "users"],
    ["tambah_siswa.php", "Tambah Siswa", "user-plus"],
    ["riwayat_absensi.php", "Riwayat Absensi", "history"],
];

$icons = [
    "layout-dashboard" => "▦",
    "clipboard-check" => "✓",
    "users" => "♙",
    "user-plus" => "+",
    "history" => "◷",
    "chart-column" => "▥"
];
?>

<aside class="sidebar">

    <div class="sidebar-brand">
    <div class="brand-text">
        <strong>Absensi Siswa</strong>
        <span>Sistem Kehadiran</span>
    </div>
</div>

    <div class="sidebar-label">MENU UTAMA</div>

    <nav>
        <?php foreach ($menu as [$link, $namaMenu, $icon]): ?>
            <a
                href="<?= htmlspecialchars($link) ?>"
                class="<?= $halaman === $link ? 'active' : '' ?>"
            >
                <span class="menu-icon" aria-hidden="true">
                    <?= $icons[$icon] ?>
                </span>

                <span><?= htmlspecialchars($namaMenu) ?></span>
            </a>
        <?php endforeach; ?>
    </nav>

    <div class="sidebar-bottom">
        <a href="logout.php" class="logout-link">
            <span class="menu-icon" aria-hidden="true">↪</span>
            <span>Logout</span>
        </a>
    </div>

</aside>
