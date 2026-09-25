<?php
session_start();
include '../config/database.php';

// Proteksi: wajib login sebagai admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$username = $_SESSION['username'];
$my_id    = $_SESSION['user_id'];
$message = '';
$message_type = '';

// ========== AKSI: UBAH ROLE ==========
if (isset($_POST['change_role'])) {
    $target_id = (int) $_POST['target_id'];
    $new_role  = mysqli_real_escape_string($conn, $_POST['new_role']);

    $role_valid = ['admin', 'petugas', 'pelapor'];

    if (!in_array($new_role, $role_valid)) {
        $message = "Role tidak valid!";
        $message_type = 'error';
    } elseif ($target_id == $my_id) {
        $message = "Anda tidak bisa mengubah role akun Anda sendiri!";
        $message_type = 'error';
    } else {
        $update = mysqli_query($conn, "UPDATE user SET role='$new_role' WHERE id='$target_id'");
        if ($update) {
            $message = "Role berhasil diubah menjadi " . strtoupper($new_role) . "!";
            $message_type = 'success';
        } else {
            $message = "Gagal mengubah role: " . mysqli_error($conn);
            $message_type = 'error';
        }
    }
}

// ========== AKSI: HAPUS USER ==========
if (isset($_GET['delete'])) {
    $target_id = (int) $_GET['delete'];

    if ($target_id == $my_id) {
        $message = "Anda tidak bisa menghapus akun Anda sendiri!";
        $message_type = 'error';
    } else {
        $delete = mysqli_query($conn, "DELETE FROM user WHERE id='$target_id'");
        if ($delete) {
            $message = "User berhasil dihapus!";
            $message_type = 'success';
        } else {
            $message = "Gagal menghapus user: " . mysqli_error($conn);
            $message_type = 'error';
        }
    }
}

// ========== STATISTIK USER ==========
$q_stats = mysqli_query($conn, "SELECT role, COUNT(*) AS total FROM user GROUP BY role");
$stats = ['admin' => 0, 'petugas' => 0, 'pelapor' => 0];
while ($row = mysqli_fetch_assoc($q_stats)) {
    $stats[$row['role']] = $row['total'];
}

// ========== FILTER ROLE ==========
$filter_role = isset($_GET['role']) ? mysqli_real_escape_string($conn, $_GET['role']) : '';

$where = "WHERE 1=1";
if (!empty($filter_role)) {
    $where .= " AND role = '$filter_role'";
}

// Ambil semua user
$q_users = mysqli_query($conn, "SELECT * FROM user $where ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola User - Admin PPKS</title>
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        .container { max-width: 1200px; margin: 30px auto; padding: 0 20px; }
        .container h2 { color: #0f172a; margin-bottom: 6px; }
        .container .subtitle { color: #64748b; margin-bottom: 25px; }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 15px;
            margin-bottom: 25px;
        }
        .stat-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 2px 6px rgba(0,0,0,0.04);
        }
        .stat-card .icon { font-size: 1.8rem; margin-bottom: 8px; }
        .stat-card .number { font-size: 2rem; font-weight: 700; color: #0265cb; margin-bottom: 4px; }
        .stat-card .label { font-size: 0.78rem; color: #64748b; text-transform: uppercase; letter-spacing: 0.3px; font-weight: 600; }
        .stat-card.orange .number { color: #f59e0b; }
        .stat-card.purple .number { color: #8b5cf6; }
        .stat-card.green .number { color: #16a34a; }

        /* Filter */
        .filter-bar {
            background: #fff;
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            border: 1px solid #e2e8f0;
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
        }
        .filter-bar label { font-weight: 600; color: #334155; font-size: 0.9rem; }
        .filter-bar select { padding: 8px 12px; border-radius: 6px; border: 1px solid #cbd5e1; font-size: 0.9rem; }
        .filter-bar button { background: #0265cb; color: #fff; border: none; padding: 8px 16px; border-radius: 6px; cursor: pointer; font-weight: 600; }

        /* Table */
        .data-table {
            width: 100%;
            background: #fff;
            border-collapse: collapse;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 6px rgba(0,0,0,0.04);
        }
        .data-table th, .data-table td {
            padding: 14px 16px;
            text-align: left;
            font-size: 0.88rem;
            border-bottom: 1px solid #e2e8f0;
        }
        .data-table th {
            background: #f1f5f9;
            color: #475569;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.3px;
        }
        .data-table tbody tr:hover { background: #f8fafc; }

        /* Role badges */
        .badge { padding: 4px 12px; border-radius: 20px; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.3px; display: inline-block; }
        .badge-admin { background: #ede9fe; color: #5b21b6; }
        .badge-petugas { background: #fed7aa; color: #9a3412; }
        .badge-pelapor { background: #dbeafe; color: #1e40af; }

        /* Form role */
        .role-form { display: flex; gap: 6px; align-items: center; }
        .role-form select { padding: 6px 10px; border-radius: 6px; border: 1px solid #cbd5e1; font-size: 0.82rem; font-family: inherit; }
        .btn-small { padding: 6px 12px; border: none; border-radius: 6px; font-size: 0.78rem; font-weight: 600; cursor: pointer; }
        .btn-save { background: #0265cb; color: #fff; }
        .btn-save:hover { background: #0252a5; }
        .btn-danger { background: #dc2626; color: #fff; text-decoration: none; display: inline-block; }
        .btn-danger:hover { background: #b91c1c; }

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
            <li><a href="kelola_user.php" class="active">Kelola User</a></li>
            <li><a href="kategori.php">Kategori</a></li>
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
        <h2>👥 Kelola User</h2>
        <p class="subtitle">Kelola semua akun yang terdaftar di sistem PPKS.</p>

        <?php if (!empty($message)): ?>
            <div class="alert alert-<?= $message_type; ?>">
                <?= $message_type === 'success' ? '✅' : '⚠️'; ?> <?= htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- Statistik -->
        <div class="stats-grid">
            <div class="stat-card purple">
                <div class="icon">👨‍💼</div>
                <div class="number"><?= $stats['admin']; ?></div>
                <div class="label">Admin</div>
            </div>
            <div class="stat-card orange">
                <div class="icon">👮</div>
                <div class="number"><?= $stats['petugas']; ?></div>
                <div class="label">Petugas</div>
            </div>
            <div class="stat-card">
                <div class="icon">👤</div>
                <div class="number"><?= $stats['pelapor']; ?></div>
                <div class="label">Pelapor</div>
            </div>
            <div class="stat-card green">
                <div class="icon">📊</div>
                <div class="number"><?= array_sum($stats); ?></div>
                <div class="label">Total User</div>
            </div>
        </div>

        <!-- Filter -->
        <form method="GET" class="filter-bar">
            <label for="role">Filter Role:</label>
            <select name="role" id="role">
                <option value="">— Semua Role —</option>
                <option value="admin" <?= $filter_role === 'admin' ? 'selected' : ''; ?>>Admin</option>
                <option value="petugas" <?= $filter_role === 'petugas' ? 'selected' : ''; ?>>Petugas</option>
                <option value="pelapor" <?= $filter_role === 'pelapor' ? 'selected' : ''; ?>>Pelapor</option>
            </select>
            <button type="submit">Terapkan</button>
            <?php if (!empty($filter_role)): ?>
                <a href="kelola_user.php" style="color:#64748b;font-size:0.85rem;">Reset</a>
            <?php endif; ?>
        </form>

        <!-- Tabel User -->
        <?php if (mysqli_num_rows($q_users) > 0): ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>No HP</th>
                        <th>Role</th>
                        <th>Terdaftar</th>
                        <th>Ubah Role</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; while ($u = mysqli_fetch_assoc($q_users)): ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td><strong><?= htmlspecialchars($u['nama']); ?></strong>
                                <?php if ($u['id'] == $my_id): ?>
                                    <span style="color:#0265cb;font-size:0.75rem;">(Anda)</span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($u['email']); ?></td>
                            <td><?= htmlspecialchars($u['no_hp'] ?? '-'); ?></td>
                            <td>
                                <?php
                                $badge_class = 'badge-pelapor';
                                if ($u['role'] === 'admin') $badge_class = 'badge-admin';
                                elseif ($u['role'] === 'petugas') $badge_class = 'badge-petugas';
                                ?>
                                <span class="badge <?= $badge_class; ?>"><?= $u['role']; ?></span>
                            </td>
                            <td><?= date('d M Y', strtotime($u['created_at'])); ?></td>
                            <td>
                                <?php if ($u['id'] != $my_id): ?>
                                    <form method="POST" class="role-form">
                                        <input type="hidden" name="target_id" value="<?= $u['id']; ?>">
                                        <select name="new_role" required>
                                            <option value="pelapor" <?= $u['role'] === 'pelapor' ? 'selected' : ''; ?>>Pelapor</option>
                                            <option value="petugas" <?= $u['role'] === 'petugas' ? 'selected' : ''; ?>>Petugas</option>
                                            <option value="admin" <?= $u['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                        </select>
                                        <button type="submit" name="change_role" class="btn-small btn-save">Simpan</button>
                                    </form>
                                <?php else: ?>
                                    <span style="color:#94a3b8;font-size:0.82rem;">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($u['id'] != $my_id): ?>
                                    <a href="kelola_user.php?delete=<?= $u['id']; ?>" class="btn-small btn-danger"
                                       onclick="return confirm('Yakin ingin menghapus user ini?');">Hapus</a>
                                <?php else: ?>
                                    <span style="color:#94a3b8;font-size:0.82rem;">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p style="text-align:center; padding: 40px; background:#fff; border-radius:12px; color:#94a3b8; border: 1px solid #e2e8f0;">
                Tidak ada user<?= !empty($filter_role) ? ' dengan role ini' : ''; ?>.
            </p>
        <?php endif; ?>
    </div>

    <script src="../assets/navbar.js"></script>
</body>
</html>