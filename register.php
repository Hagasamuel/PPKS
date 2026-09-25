    <?php
    session_start();
    include 'config/database.php';

    $error = '';
    $success = '';

    if (isset($_POST['register'])) {
        $nama     = mysqli_real_escape_string($conn, trim($_POST['nama']));
        $email    = mysqli_real_escape_string($conn, trim($_POST['email']));
        $password = $_POST['password'];

        if (empty($nama) || empty($email) || empty($password)) {
            $error = "Semua field wajib diisi!";
        } elseif (strlen($password) < 6) {
            $error = "Password minimal 6 karakter!";
        } else {
            $cek = mysqli_query($conn, "SELECT * FROM user WHERE nama='$nama' OR email='$email'");
            
            if (mysqli_num_rows($cek) > 0) {
                $error = "Nama atau Email sudah terdaftar!";
            } else {
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                $query = "INSERT INTO user (nama, email, password, role) 
                        VALUES ('$nama', '$email', '$password_hash', 'pelapor')";
                
                if (mysqli_query($conn, $query)) {
                    $success = "Pendaftaran berhasil! Silakan <a href='login.php'>login</a>.";
                } else {
                    $error = "Gagal mendaftar: " . mysqli_error($conn);
                }
            }
        }
    }
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Register - Portal PPKS</title>
        <link rel="stylesheet" href="assets/register.css">
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
        <script src="https://unpkg.com/lucide@latest"></script>
    </head>
    <body>

        <div class="register-wrapper">
            <div class="register-card">
                
                <div class="card-header">
                    <div class="icon-brand">
                        <i data-lucide="user-plus"></i>
                    </div>
                    <h2>Daftar Akun Baru</h2>
                    <p>Lengkapi data diri Anda untuk mengakses layanan PPKS</p>
                </div>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-error">
                        <i data-lucide="alert-circle"></i>
                        <span><?= $error; ?></span>
                    </div>
                <?php endif; ?>

                <?php if (!empty($success)): ?>
                    <div class="alert alert-success">
                        <i data-lucide="check-circle"></i>
                        <span><?= $success; ?></span>
                    </div>
                <?php endif; ?>

                <form action="" method="POST" class="register-form">
                    <div class="form-group">
                        <label for="nama">Nama Lengkap</label>
                        <input type="text" id="nama" name="nama" required placeholder="Masukkan nama lengkap"
                            value="<?= isset($_POST['nama']) ? htmlspecialchars($_POST['nama']) : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required placeholder="contoh@email.com"
                            value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required placeholder="Minimal 6 karakter">
                    </div>

                    <button type="submit" name="register" class="btn-submit">
                        <span>Daftar Akun</span>
                        <i data-lucide="arrow-right"></i>
                    </button>
                </form>

                <div class="card-footer">
                    <p>Sudah punya akun? <a href="login.php" class="link-bold">Login</a></p>
                    <div class="divider"></div>
                    <a href="index.php" class="link-back">
                        <i data-lucide="arrow-left"></i>
                        Kembali ke Beranda
                    </a>
                </div>

            </div>
        </div>

        <script>
            lucide.createIcons();
        </script>
    </body>
    </html> 