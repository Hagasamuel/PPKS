<?php
session_start();
include '../config/database.php';

// Proteksi: wajib login sebagai admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$username = $_SESSION['username'];
$message = '';
$message_type = '';

// Tambah Kategori
if (isset($_POST['add_category'])) {
    $nama      = mysqli_real_escape_string($conn, trim($_POST['nama']));
    $deskripsi = mysqli_real_escape_string($conn, trim($_POST['deskripsi'] ?? ''));

    if (empty($nama)) {
        $message = "Nama kategori tidak boleh kosong!";
        $message_type = 'error';
    } else {
        $insert = mysqli_query($conn, "INSERT INTO categories (nama, deskripsi, status) 
                                       VALUES ('$nama', '$deskripsi', 1)");
        if ($insert) {
            $message = "Kategori berhasil ditambahkan!";
            $message_type = 'success';
        } else {
            $message = "Gagal menambah kategori: " . mysqli_error($conn);
            $message_type = 'error';
        }
    }
}

// Hapus Kategori
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    $delete = mysqli_query($conn, "DELETE FROM categories WHERE id='$id'");
    if ($delete) {
        $message = "Kategori berhasil dihapus!";
        $message_type = 'success';
    }
}

$categories = mysqli_query($conn, "SELECT * FROM categories ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Kategori - Admin PPKS</title>
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        .container { max-width: 900px; margin: 30px auto; padding: 0 20px; }
        .container h2 { color: #0f172a; margin-bottom: 6px; }
        .container .subtitle { color: #64748b; margin-bottom: 25px; }

        .card { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 25px 30px; margin-bottom: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.04); }
        .card h3 { color: #0f172a; margin-bottom: 15px; border-bottom: 1px solid #e2e8f0; padding-bottom: 10px; font-size: 1.05rem; }

        .form-inline { display: flex; gap: 10px; flex-wrap: wrap; align-items: center; }
        .form-inline input[type="text"] {
            flex: 1;
            min-width: 200px;
            padding: 10px 14px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-size: 0.9rem;
            font-family: inherit;
        }
        .form-inline input:focus { border-color: #0265cb; box-shadow: 0 0 0 3px rgba(2,101,203,0.15); outline: none; }

        .btn-primary { background: #0265cb; color: #fff; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 600; cursor: pointer; }
        .btn-primary:hover { background: #0252a5; }

        .data-table { width: 100%; border-collapse: collapse; }
        .data-table th, .data-table td { padding: 12px 14px; text-align: left; font-size: 0.88rem; border-bottom: 1px solid #e2e8f0; }
        .data-table th { background: #f1f5f9; color: #475569; font-weight: 600; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.3px; }
        .data-table tbody tr:hover { background: #f8fafc; }

        .badge-status { background: #dcfce7; color: #166534; padding: 4px 10px; border-radius: 20px; font-size: 0.72rem; font-weight: 600; }

        .btn-danger { color: #dc2626; text-decoration: none; font-weight: 600; font-size: 0.85rem; }
        .btn-danger:hover { text-decoration: underline; }

        .alert { padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem; font-weight: 500; }
        .alert-success { background: #dcfce7; border: 1px solid #4ade80; color: #166534; }
        .alert-error { background: #fee2e2; border: 1px solid #f87171; color: #991b1b; }
    </style>
</head>
<body>
    <header class="navbar">
        <div class="logo"><span>🛡️</span> Portal Layanan PPKS — Admin</div>
        <ul class="nav-links">
            <li><a href="index.php">Dashboard</a></li>
            <li><a href="kelola_user.php">Kelola User</a></li>
            <li><a href="kategori.php" class="active">Kategori</a></li>
            <li class="nav-account">
                <button class="nav-account-btn" type="button">
                    👤 <?= htmlspecialchars($username); ?>
                    <span class="arrow">▼</span>
                </button>
                <div class="nav-account-menu">
                    <a href="../logout.php" class="logout-link">🚪 Logout</a>
                </div>
            </li>
        </ul>
    </header>

    <div class="container">
        <h2>📂 Kelola Kategori Kejadian</h2>
        <p class="subtitle">Kelola daftar kategori yang tampil di form pelaporan user.</p>

        <?php if (!empty($message)): ?>
            <div class="alert alert-<?= $message_type; ?>">
                <?= $message_type === 'success' ? '✅' : '⚠️'; ?> <?= htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- Form Tambah Kategori -->
        <div class="card">
            <h3>➕ Tambah Kategori Baru</h3>
            <form method="POST" class="form-inline">
                <input type="text" name="nama" placeholder="Nama Kategori (contoh: Pelecehan Verbal)" required>
                <input type="text" name="deskripsi" placeholder="Deskripsi singkat (opsional)">
                <button type="submit" name="add_category" class="btn-primary">Tambah</button>
            </form>
        </div>

        <!-- Tabel Kategori -->
        <div class="card">
            <h3>📋 Daftar Kategori (<?= mysqli_num_rows($categories); ?>)</h3>
            <?php if (mysqli_num_rows($categories) > 0): ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Kategori</th>
                            <th>Deskripsi</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; while ($cat = mysqli_fetch_assoc($categories)): ?>
                            <tr>
                                <td><?= $no++; ?></td>
                                <td><strong><?= htmlspecialchars($cat['nama']); ?></strong></td>
                                <td><?= htmlspecialchars($cat['deskripsi'] ?? '-'); ?></td>
                                <td><span class="badge-status"><?= ($cat['status'] == 1) ? 'AKTIF' : 'NONAKTIF'; ?></span></td>
                                <td>
                                    <a href="kategori.php?delete=<?= $cat['id']; ?>" class="btn-danger" 
                                       onclick="return confirm('Yakin ingin menghapus kategori ini?')">🗑️ Hapus</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="color:#94a3b8; font-style:italic; text-align:center; padding: 20px;">Belum ada kategori. Silakan tambah kategori baru.</p>
            <?php endif; ?>
        </div>
    </div>

    <script src="../assets/navbar.js"></script>
</body>
</html>