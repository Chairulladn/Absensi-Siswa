<?php

// Hanya boleh dijalankan melalui terminal
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("Akses ditolak.");
}

require_once __DIR__ . "/../config/koneksi.php";

// Cek apakah admin sudah tersedia
$result = mysqli_query(
    $koneksi,
    "SELECT COUNT(*) AS total FROM admin"
);

$data = mysqli_fetch_assoc($result);

if ($data["total"] > 0) {
    exit("Admin sudah tersedia. Setup dibatalkan.\n");
}

// Input username
echo "Masukkan username admin: ";
$username = trim(fgets(STDIN));

// Input password
echo "Masukkan password admin: ";
$password = trim(fgets(STDIN));

if (strlen($username) < 3 || strlen($username) > 50) {
    exit("Username harus 3-50 karakter.\n");
}

if (strlen($password) < 12) {
    exit("Password minimal 12 karakter.\n");
}

// Hash password
$passwordHash = password_hash($password, PASSWORD_DEFAULT);

// Simpan admin
$stmt = mysqli_prepare(
    $koneksi,
    "INSERT INTO admin (username, password) VALUES (?, ?)"
);

mysqli_stmt_bind_param(
    $stmt,
    "ss",
    $username,
    $passwordHash
);

if (mysqli_stmt_execute($stmt)) {
    echo "Akun admin berhasil dibuat!\n";
} else {
    echo "Gagal membuat admin.\n";
}

mysqli_stmt_close($stmt);