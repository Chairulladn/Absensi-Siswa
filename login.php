<?php

session_start();

include "koneksi.php";

$pesan = "";

if (isset($_SESSION['admin_id'])) {

    header("Location: index.php");
    exit;

}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = mysqli_real_escape_string(
        $koneksi,
        $_POST['username']
    );

    $password = $_POST['password'];

    $query = mysqli_query(
        $koneksi,
        "SELECT *
         FROM admin
         WHERE username = '$username'
         LIMIT 1"
    );

    $admin = mysqli_fetch_assoc($query);

    if (
        $admin &&
        password_verify($password, $admin['password'])
    ) {

        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];

        header("Location: index.php");
        exit;

    } else {

        $pesan = "Username atau password salah.";

    }

}

?>

<!DOCTYPE html>
<html>

<head>

    <title>Login Admin</title>

    <link
        rel="stylesheet"
        href="css/style.css?v=3"
    >

</head>

<body>

<div class="login-container">

    <div class="login-box">

        <h1>Login Admin</h1>

        <p>
            Masuk untuk mengelola data anggota.
        </p>

        <?php if ($pesan != "") { ?>

            <div class="login-error">
                <?php echo $pesan; ?>
            </div>

        <?php } ?>

        <form method="POST">

            <label>Username</label>

            <input
                type="text"
                name="username"
                required
                autofocus
            >

            <label>Password</label>

            <input
                type="password"
                name="password"
                required
            >

            <button type="submit">
                Login
            </button>

        </form>

        <a
            href="index.php"
            class="login-back"
        >
            ← Kembali ke Dashboard
        </a>

    </div>

</div>

</body>

</html>