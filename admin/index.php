<?php
session_start();
include '../config/database.php';

// Proteksi: wajib login sebagai admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$username = $_SESSION['username'];

// ========== STATISTIK ==========

// Total user per role
$q_user = mysqli_query($conn, "SELECT role, COUNT(*) AS total FROM user GROUP BY role");
$stats_user = ['admin' => 0, 'petugas' => 0, 'pelapor' => 0];
while ($row = mysqli_fetch_assoc($q_user)) {
    $stats_user[$row['role']] = $row['total'];
}

// Total laporan per status
$q_status = mysqli_query($conn, "SELECT status, COUNT(*) AS total FROM reports GROUP BY status");
$stats_status = [
    'diajukan' => 0, 'diverifikasi' => 0, 'dalam_pemeriksaan' => 0,
    'dalam_penanganan' => 0, 'selesai' => 0, 'ditutup' => 0
];
while ($row = mysqli_fetch_assoc($q_status)) {
    $stats_status[$row['status']] = $row['total'];
}

// Total semua laporan
$q_total = mysqli_query($conn, "SELECT COUNT(*) AS total FROM reports");
$total_laporan = mysqli_fetch_assoc($q_total)['total'];

// ========== DAFTAR LAPORAN TERBARU ==========

// Filter status (opsional)
$filter_status = isset($_GET['status']) ? mysqli_real_escape_string($conn, $_GET['status']) : '';

$where = "WHERE 1=1";
if (!empty($filter_status)) {
    $where .= " AND r.status = '$filter_status'";
}

$q_laporan = mysqli_query($conn, "
    SELECT r.*, 
           u.nama AS nama_pelapor, 
           c.nama AS nama_kategori 
    FROM reports r
    LEFT JOIN user u ON r.user_id = u.id
    LEFT JOIN categories c ON r.category_id = c.id
    $where
    ORDER BY r.created_at DESC
    LIMIT 10
");

// Helper badge
function badge_status($status) {
    $map = [
        'diajukan'          => ['bg' => '#fef3c7', 'text' => '#92400e'],
        'diverifikasi'      => ['bg' => '#dbeafe', 'text' => '#1e40af'],
        'dalam_pemeriksaan' => ['bg' => '#e0e7ff', 'text' => '#3730a3'],
        'dalam_penanganan'  => ['bg' => '#fed7aa', 'text' => '#9a3412'],
        'selesai'           => ['bg' => '#dcfce7', 'text' => '#166534'],
        'ditutup'           => ['bg' => '#e2e8f0', 'text' => '#475569'],
    ];
    $c = $map[$status] ?? ['bg' => '#f1f5f9', 'text' => '#475569'];
    return "<span style='background:{$c['bg']};color:{$c['text']};padding:4px 10px;border-radius:20px;font-size:0.72rem;font-weight:600;text-transform:uppercase;'>" . htmlspecialchars($status) . "</span>";
}

function badge_urgensi($urgensi) {
    $map = [
        'rendah' => ['bg' => '#dcfce7', 'text' => '#166534'],
        'sedang' => ['bg' => '#fef3c7', 'text' => '#92400e'],
        'tinggi' => ['bg' => '#fee2e2', 'text' => '#991b1b'],
    ];
    $c = $map[$urgensi] ?? ['bg' => '#f1f5f9', 'text' => '#475569'];
    return "<span style='background:{$c['bg']};color:{$c['text']};padding:4px 10px;border-radius:20px;font-size:0.72rem;font-weight:600;text-transform:uppercase;'>" . htmlspecialchars($urgensi) . "</span>";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Portal PPKS</title>
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        .container { max-width: 1300px; margin: 30px auto; padding: 0 20px; }
        .container h2 { color: #0f172a; margin-bottom: 6px; }
        .container .subtitle { color: #64748b; margin-bottom: 25px; }

        /* Section title */
        .section-title {
            font-size: 0.85rem;
            font-weight: 700;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 30px;
            margin-bottom: 15px;
            padding-left: 10px;
            border-left: 4px solid #0265cb;
        }

        /* Stats grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        .stat-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 2px 6px rgba(0,0,0,0.04);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .stat-card:hover { transform: translateY(-3px); box-shadow: 0 6px 16px rgba(0,0,0,0.08); }
        .stat-card .icon { font-size: 1.8rem; margin-bottom: 8px; }
        .stat-card .number { font-size: 2rem; font-weight: 700; color: #0265cb; margin-bottom: 4px; }
        .stat-card .label { font-size: 0.78rem; color: #64748b; text-transform: uppercase; letter-spacing: 0.3px; font-weight: 600; }

        .stat-card.orange .number { color: #f59e0b; }
        .stat-card.green .number { color: #16a34a; }
        .stat-card.red .number { color: #dc2626; }
        .stat-card.purple .number { color: #8b5cf6; }

        /* Filter bar */
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
        .data-table th { background: #f1f5f9; color: #475569; font-weight: 600; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.3px; }
        .data-table tbody tr:hover { background: #f8fafc; }
        .data-table .kode { font-family: 'Courier New', monospace; font-size: 0.78rem; color: #64748b; }
        .data-table .aksi a { color: #0265cb; text-decoration: none; font-weight: 600; font-size: 0.85rem; }
        .data-table .aksi a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <header class="navbar">
        <div class="logo"><span>🛡️</span> Portal Layanan PPKS — Admin</div>
        <ul class="nav-links">
            <li><a href="index.php" class="active">Dashboard</a></li>
            <li><a href="kelola_user.php">Kelola User</a></li>
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
        <h2>📊 Dashboard Admin</h2>
        <p class="subtitle">Ringkasan keseluruhan sistem Portal Layanan PPKS.</p>

        <!-- STATISTIK USER -->
        <div class="section-title">👥 Statistik User</div>
        <div class="stats-grid">
            <div class="stat-card">
                <div class="icon">👤</div>
                <div class="number"><?= $stats_user['pelapor']; ?></div>
                <div class="label">Pelapor</div>
            </div>
            <div class="stat-card orange">
                <div class="icon">👮</div>
                <div class="number"><?= $stats_user['petugas']; ?></div>
                <div class="label">Petugas</div>
            </div>
            <div class="stat-card purple">
                <div class="icon">👨‍💼</div>
                <div class="number"><?= $stats_user['admin']; ?></div>
                <div class="label">Admin</div>
            </div>
        </div>

        <!-- STATISTIK LAPORAN -->
        <div class="section-title">📋 Statistik Laporan (Total: <?= $total_laporan; ?>)</div>
        <div class="stats-grid">
            <div class="stat-card">
                <div class="icon">🆕</div>
                <div class="number"><?= $stats_status['diajukan']; ?></div>
                <div class="label">Diajukan</div>
            </div>
            <div class="stat-card green">
                <div class="icon">✅</div>
                <div class="number"><?= $stats_status['diverifikasi']; ?></div>
                <div class="label">Diverifikasi</div>
            </div>
            <div class="stat-card purple">
                <div class="icon">🔍</div>
                <div class="number"><?= $stats_status['dalam_pemeriksaan']; ?></div>
                <div class="label">Pemeriksaan</div>
            </div>
            <div class="stat-card orange">
                <div class="icon">⚙️</div>
                <div class="number"><?= $stats_status['dalam_penanganan']; ?></div>
                <div class="label">Penanganan</div>
            </div>
            <div class="stat-card green">
                <div class="icon">🎉</div>
                <div class="number"><?= $stats_status['selesai']; ?></div>
                <div class="label">Selesai</div>
            </div>
            <div class="stat-card">
                <div class="icon">🔒</div>
                <div class="number"><?= $stats_status['ditutup']; ?></div>
                <div class="label">Ditutup</div>
            </div>
        </div>

        <!-- DAFTAR LAPORAN -->
        <div class="section-title">📌 Laporan Terbaru (10 Terakhir)</div>

        <form method="GET" class="filter-bar">
            <label for="status">Filter Status:</label>
            <select name="status" id="status">
                <option value="">— Semua Status —</option>
                <option value="diajukan" <?= $filter_status === 'diajukan' ? 'selected' : ''; ?>>Diajukan</option>
                <option value="diverifikasi" <?= $filter_status === 'diverifikasi' ? 'selected' : ''; ?>>Diverifikasi</option>
                <option value="dalam_pemeriksaan" <?= $filter_status === 'dalam_pemeriksaan' ? 'selected' : ''; ?>>Dalam Pemeriksaan</option>
                <option value="dalam_penanganan" <?= $filter_status === 'dalam_penanganan' ? 'selected' : ''; ?>>Dalam Penanganan</option>
                <option value="selesai" <?= $filter_status === 'selesai' ? 'selected' : ''; ?>>Selesai</option>
                <option value="ditutup" <?= $filter_status === 'ditutup' ? 'selected' : ''; ?>>Ditutup</option>
            </select>
            <button type="submit">Terapkan</button>
            <?php if (!empty($filter_status)): ?>
                <a href="index.php" style="color:#64748b;font-size:0.85rem;">Reset</a>
            <?php endif; ?>
        </form>

        <?php if (mysqli_num_rows($q_laporan) > 0): ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Kode</th>
                        <th>Pelapor</th>
                        <th>Kategori</th>
                        <th>Tanggal Kejadian</th>
                        <th>Urgensi</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; while ($row = mysqli_fetch_assoc($q_laporan)): ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td class="kode"><?= htmlspecialchars($row['kode_laporan']); ?></td>
                            <td>
                                <?php if ($row['anonim'] == 1): ?>
                                    <em style="color:#94a3b8;">🎭 Anonim</em>
                                <?php else: ?>
                                    <?= htmlspecialchars($row['nama_pelapor'] ?? '-'); ?>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($row['nama_kategori'] ?? '-'); ?></td>
                            <td><?= date('d M Y', strtotime($row['tanggal_kejadian'])); ?></td>
                            <td><?= badge_urgensi($row['urgensi']); ?></td>
                            <td><?= badge_status($row['status']); ?></td>
                            <td class="aksi">
                                <a href="../petugas/tindak_lanjut.php?id=<?= $row['id']; ?>">Lihat →</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p style="text-align:center; padding: 40px; background:#fff; border-radius:12px; color:#94a3b8; border: 1px solid #e2e8f0;">
                Belum ada laporan<?= !empty($filter_status) ? ' dengan status ini' : ''; ?>.
            </p>
        <?php endif; ?>
    </div>

    <script src="../assets/navbar.js"></script>
</body>
</html>