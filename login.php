<?php
session_start();
include 'config/database.php';

$error_message = '';

if (isset($_POST['login'])) {
    $input    = mysqli_real_escape_string($conn, trim($_POST['username']));
    $password = $_POST['password'];

    if (empty($input) || empty($password)) {
        $error_message = "Harap isi email/nama dan password!";
    } else {
        $query  = "SELECT * FROM user WHERE email='$input' OR nama='$input' LIMIT 1";
        $result = mysqli_query($conn, $query);

        if ($result && mysqli_num_rows($result) > 0) {
            $user = mysqli_fetch_assoc($result);

            if (password_verify($password, $user['password'])) {
                $_SESSION['login']    = true;
                $_SESSION['user_id']  = $user['id'];
                $_SESSION['username'] = $user['nama'];
                $_SESSION['role']     = $user['role'];

                if ($user['role'] === 'admin') {
                    header("Location: admin/index.php");
                } elseif ($user['role'] === 'petugas') {
                    header("Location: petugas/index.php");
                } else {
                    header("Location: user/index.php");
                }
                exit;
            } else {
                $error_message = "Password yang Anda masukkan salah!";
            }
        } else {
            $error_message = "Akun belum terdaftar!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Portal PPKS</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body.auth-body { background-color: #edf5ff; display: flex; justify-content: center; align-items: center; min-height: 100vh; padding: 20px; }
        .auth-box { width: 100%; max-width: 400px; background: #ffffff; padding: 35px 30px; border-radius: 12px; border: 1px solid #cbd5e1; box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08); }
        .auth-box h2 { text-align: center; font-size: 1.4rem; color: #0f172a; margin-bottom: 6px; }
        .auth-box p.subtitle { text-align: center; margin-bottom: 20px; font-size: 0.875rem; color: #64748b; }
        .alert-danger { background-color: #fee2e2; border: 1px solid #f87171; color: #991b1b; padding: 10px 14px; border-radius: 8px; font-size: 0.875rem; margin-bottom: 20px; text-align: center; font-weight: 500; }
        .alert-success { background-color: #dcfce7; border: 1px solid #4ade80; color: #166534; padding: 10px 14px; border-radius: 8px; font-size: 0.875rem; margin-bottom: 20px; text-align: center; font-weight: 500; }
        form label { font-weight: 600; color: #475569; display: block; margin-bottom: 6px; font-size: 0.875rem; }
        form input[type="text"], form input[type="password"] { width: 100%; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.9rem; margin-bottom: 18px; outline: none; transition: border-color 0.2s; }
        form input:focus { border-color: #0265cb; box-shadow: 0 0 0 3px rgba(2, 101, 203, 0.15); }
        .btn-login { width: 100%; background-color: #0265cb; color: #ffffff; padding: 12px; border: none; border-radius: 8px; cursor: pointer; font-size: 0.95rem; font-weight: 600; transition: background 0.2s; }
        .btn-login:hover { background-color: #0252a5; }
        .auth-footer { text-align: center; margin-top: 24px; font-size: 0.875rem; color: #64748b; }
        .auth-footer a { color: #0265cb; font-weight: 600; text-decoration: none; }
        .auth-footer a.back-link { color: #64748b; font-weight: normal; }
        .auth-footer a:hover { text-decoration: underline; }
    </style>
</head>
<body class="auth-body">

    <div class="auth-box">
        <div style="text-align: center; font-size: 2.2rem; margin-bottom: 8px;">🛡️</div>
        <h2>Login Portal PPKS</h2>
        <p class="subtitle">Masukkan akun Anda untuk melanjutkan</p>

        <?php if (isset($_GET['registered'])): ?>
            <div class="alert-success">✅ Pendaftaran berhasil! Silakan login.</div>
        <?php endif; ?>

        <?php if (!empty($error_message)): ?>
            <div class="alert-danger">⚠️ <?= $error_message; ?></div>
        <?php endif; ?>

        <form action="" method="POST">
            <label for="username">Email / Nama</label>
            <input type="text" id="username" name="username" placeholder="Masukkan email atau nama" 
                   value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required>

            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="Masukkan password" required>

            <button type="submit" name="login" class="btn-login">Masuk &rarr;</button>
        </form>

        <div class="auth-footer">
            <p>Belum punya akun? <a href="register.php">Register</a></p>
            <p style="margin-top: 10px;">
                <a href="index.php" class="back-link">&larr; Kembali ke Beranda</a>
            </p>
        </div>
    </div>

</body>
</html>