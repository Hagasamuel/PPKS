<?php
session_start();
include '../config/database.php';

// Proteksi: wajib login sebagai pelapor
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pelapor') {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$message = '';
$message_type = ''; // success / error

// ========== PROSES FORM ==========

// 1. Update Profil (nama & no_hp)
if (isset($_POST['update_profil'])) {
    $nama  = mysqli_real_escape_string($conn, trim($_POST['nama']));
    $no_hp = mysqli_real_escape_string($conn, trim($_POST['no_hp']));

    if (empty($nama)) {
        $message = "Nama tidak boleh kosong!";
        $message_type = 'error';
    } else {
        $update = mysqli_query($conn, "UPDATE user SET nama='$nama', no_hp='$no_hp' WHERE id='$user_id'");
        if ($update) {
            // Update session username juga
            $_SESSION['username'] = $nama;
            $message = "Profil berhasil diperbarui!";
            $message_type = 'success';
        } else {
            $message = "Gagal memperbarui profil: " . mysqli_error($conn);
            $message_type = 'error';
        }
    }
}

// 2. Ganti Password
if (isset($_POST['change_password'])) {
    $pass_lama   = $_POST['pass_lama'];
    $pass_baru   = $_POST['pass_baru'];
    $pass_konfir = $_POST['pass_konfir'];

    // Ambil password lama dari database
    $q = mysqli_query($conn, "SELECT password FROM user WHERE id='$user_id'");
    $data = mysqli_fetch_assoc($q);

    if (!password_verify($pass_lama, $data['password'])) {
        $message = "Password lama salah!";
        $message_type = 'error';
    } elseif (strlen($pass_baru) < 6) {
        $message = "Password baru minimal 6 karakter!";
        $message_type = 'error';
    } elseif ($pass_baru !== $pass_konfir) {
        $message = "Konfirmasi password tidak cocok!";
        $message_type = 'error';
    } else {
        $hash_baru = password_hash($pass_baru, PASSWORD_DEFAULT);
        $update = mysqli_query($conn, "UPDATE user SET password='$hash_baru' WHERE id='$user_id'");
        if ($update) {
            $message = "Password berhasil diubah!";
            $message_type = 'success';
        } else {
            $message = "Gagal mengubah password: " . mysqli_error($conn);
            $message_type = 'error';
        }
    }
}

// ========== AMBIL DATA USER ==========
$q_user = mysqli_query($conn, "SELECT * FROM user WHERE id='$user_id' LIMIT 1");
$user = mysqli_fetch_assoc($q_user);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya - Portal PPKS</title>
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        .container { max-width: 800px; margin: 30px auto; padding: 0 20px; }
        .container h2 { color: #0f172a; margin-bottom: 6px; }
        .container .subtitle { color: #64748b; margin-bottom: 25px; }

        .card { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 25px 30px; margin-bottom: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.04); }
        .card h3 { color: #0f172a; margin-bottom: 15px; border-bottom: 1px solid #e2e8f0; padding-bottom: 10px; font-size: 1.05rem; }

        .info-grid { display: grid; grid-template-columns: 180px 1fr; gap: 12px; font-size: 0.9rem; }
        .info-grid .label { color: #64748b; font-weight: 600; }
        .info-grid .value { color: #1e293b; }

        .badge { padding: 4px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.3px; background: #dbeafe; color: #1e40af; }

        form label { display: block; font-weight: 600; color: #475569; margin-bottom: 6px; font-size: 0.88rem; }
        form input[type="text"], form input[type="password"] {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-size: 0.9rem;
            margin-bottom: 16px;
            outline: none;
            transition: border-color 0.2s;
            box-sizing: border-box;
        }
        form input:focus { border-color: #0265cb; box-shadow: 0 0 0 3px rgba(2,101,203,0.15); }
        form input:disabled { background: #f1f5f9; color: #94a3b8; cursor: not-allowed; }

        .btn-primary {
            background: #0265cb; color: #fff; border: none;
            padding: 10px 24px; border-radius: 8px;
            font-weight: 600; cursor: pointer; font-size: 0.9rem;
            transition: background 0.2s;
        }
        .btn-primary:hover { background: #0252a5; }

        .btn-success {
            background: #16a34a; color: #fff; border: none;
            padding: 10px 24px; border-radius: 8px;
            font-weight: 600; cursor: pointer; font-size: 0.9rem;
            transition: background 0.2s;
        }
        .btn-success:hover { background: #15803d; }

        .alert { padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem; font-weight: 500; }
        .alert-success { background: #dcfce7; border: 1px solid #4ade80; color: #166534; }
        .alert-error { background: #fee2e2; border: 1px solid #f87171; color: #991b1b; }

        .hint { font-size: 0.78rem; color: #94a3b8; margin-top: -10px; margin-bottom: 16px; }
    </style>
</head>
<body>
        <header class="navbar">
        <div class="logo"><span>🛡️</span> Portal Layanan PPKS</div>
        <ul class="nav-links">
            <li><a href="index.php">Beranda</a></li>
            <li><a href="buat_laporan.php">Buat Laporan</a></li>
            <li><a href="laporan_saya.php">Laporan Saya</a></li>
            <li class="nav-account">
                <button class="nav-account-btn" type="button">
                    👤 <?= htmlspecialchars($_SESSION['username']); ?>
                    <span class="arrow">▼</span>
                </button>
                <div class="nav-account-menu">
                    <a href="profil.php">👤 Profil Saya</a>
                    <a href="../logout.php" class="logout-link">🚪 Logout</a>
                </div>
            </li>
        </ul>
    </header>

    <div class="container">
        <h2>👤 Profil Saya</h2>
        <p class="subtitle">Kelola informasi akun dan keamanan Anda.</p>

        <?php if (!empty($message)): ?>
            <div class="alert alert-<?= $message_type; ?>">
                <?= $message_type === 'success' ? '✅' : '⚠️'; ?> <?= htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- Info Akun (Read-only) -->
        <div class="card">
            <h3>📌 Informasi Akun</h3>
            <div class="info-grid">
                <span class="label">Email</span>
                <span class="value"><?= htmlspecialchars($user['email']); ?></span>

                <span class="label">Role</span>
                <span class="value"><span class="badge"><?= htmlspecialchars($user['role']); ?></span></span>

                <span class="label">Terdaftar Sejak</span>
                <span class="value"><?= date('d F Y, H:i', strtotime($user['created_at'])); ?> WIB</span>
            </div>
            <p class="hint" style="margin-top:15px;">ℹ️ Email tidak dapat diubah. Hubungi admin jika perlu mengubah email.</p>
        </div>

        <!-- Form Edit Profil -->
        <div class="card">
            <h3>✏️ Edit Profil</h3>
            <form method="POST">
                <label for="nama">Nama Lengkap</label>
                <input type="text" id="nama" name="nama" required value="<?= htmlspecialchars($user['nama']); ?>">

                <label for="no_hp">Nomor HP</label>
                <input type="text" id="no_hp" name="no_hp" placeholder="Contoh: 081234567890"
                       value="<?= htmlspecialchars($user['no_hp'] ?? ''); ?>">

                <button type="submit" name="update_profil" class="btn-primary">Simpan Perubahan</button>
            </form>
        </div>

        <!-- Form Ganti Password -->
        <div class="card">
            <h3>🔒 Ganti Password</h3>
            <form method="POST">
                <label for="pass_lama">Password Lama</label>
                <input type="password" id="pass_lama" name="pass_lama" required placeholder="Masukkan password lama">

                <label for="pass_baru">Password Baru</label>
                <input type="password" id="pass_baru" name="pass_baru" required placeholder="Minimal 6 karakter">

                <label for="pass_konfir">Konfirmasi Password Baru</label>
                <input type="password" id="pass_konfir" name="pass_konfir" required placeholder="Ulangi password baru">

                <button type="submit" name="change_password" class="btn-success">Ganti Password</button>
            </form>
        </div>
    </div>
        <script src="../assets/navbar.js"></script>
</body>
</html>