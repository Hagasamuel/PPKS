<?php
session_start();
include '../config/database.php';

// Proteksi: Wajib login sebagai petugas
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'petugas') {
    header("Location: ../login.php");
    exit;
}

$petugas_id = $_SESSION['user_id'];
$username   = $_SESSION['username'];

// Filter status (opsional)
$filter_status = isset($_GET['status']) ? mysqli_real_escape_string($conn, $_GET['status']) : '';

// Query dasar
$where = "WHERE 1=1";
if (!empty($filter_status)) {
    $where .= " AND r.status = '$filter_status'";
}

// Ambil semua laporan dengan JOIN ke user (pelapor) dan categories
$query = "SELECT r.*, 
                 u.nama AS nama_pelapor, 
                 c.nama AS nama_kategori 
          FROM reports r
          LEFT JOIN user u ON r.user_id = u.id
          LEFT JOIN categories c ON r.category_id = c.id
          $where
          ORDER BY r.created_at DESC";
$result = mysqli_query($conn, $query);

// Hitung statistik per status
$stats = [];
$stat_query = mysqli_query($conn, "SELECT status, COUNT(*) as total FROM reports GROUP BY status");
while ($s = mysqli_fetch_assoc($stat_query)) {
    $stats[$s['status']] = $s['total'];
}

// Warna badge status
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
    <title>Dashboard Petugas - Portal PPKS</title>
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        .container { max-width: 1200px; margin: 30px auto; padding: 0 20px; }
        .container h2 { color: #0f172a; margin-bottom: 6px; }
        .container .subtitle { color: #64748b; margin-bottom: 25px; }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 25px;
        }
        .stat-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 18px;
            text-align: center;
            box-shadow: 0 2px 6px rgba(0,0,0,0.04);
        }
        .stat-card .number { font-size: 1.8rem; font-weight: 700; color: #0265cb; }
        .stat-card .label { font-size: 0.78rem; color: #64748b; text-transform: uppercase; letter-spacing: 0.3px; }

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
        .filter-bar select {
            padding: 8px 12px;
            border-radius: 6px;
            border: 1px solid #cbd5e1;
            font-size: 0.9rem;
        }
        .filter-bar button {
            background: #0265cb;
            color: #fff;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
        }

        .laporan-table {
            width: 100%;
            background: #fff;
            border-collapse: collapse;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 6px rgba(0,0,0,0.04);
        }
        .laporan-table thead {
            background: #f1f5f9;
        }
        .laporan-table th, .laporan-table td {
            padding: 14px 16px;
            text-align: left;
            font-size: 0.88rem;
            border-bottom: 1px solid #e2e8f0;
        }
        .laporan-table th { color: #475569; font-weight: 600; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.3px; }
        .laporan-table tbody tr:hover { background: #f8fafc; }
        .laporan-table .kode { font-family: 'Courier New', monospace; font-size: 0.78rem; color: #64748b; }
        .btn-tindak {
            display: inline-block;
            background: #0265cb;
            color: #fff;
            padding: 6px 14px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.8rem;
            font-weight: 600;
            transition: background 0.2s;
        }
        .btn-tindak:hover { background: #0252a5; }
        .empty-state {
            background: #fff;
            border: 2px dashed #cbd5e1;
            border-radius: 12px;
            padding: 50px;
            text-align: center;
            color: #64748b;
        }
    </style>
</head>
<body>
    <header class="navbar">
        <div class="logo"><span>🛡️</span> Portal Layanan PPKS — Petugas</div>
        <ul class="nav-links">
            <li><a href="index.php" class="active">Dashboard</a></li>
            <li><a href="../logout.php">Logout (<?= htmlspecialchars($username); ?>)</a></li>
        </ul>
    </header>

    <div class="container">
        <h2>📋 Dashboard Petugas</h2>
        <p class="subtitle">Kelola dan tindak lanjuti laporan yang masuk.</p>

        <!-- Statistik -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="number"><?= $stats['diajukan'] ?? 0; ?></div>
                <div class="label">Diajukan</div>
            </div>
            <div class="stat-card">
                <div class="number"><?= $stats['diverifikasi'] ?? 0; ?></div>
                <div class="label">Diverifikasi</div>
            </div>
            <div class="stat-card">
                <div class="number"><?= $stats['dalam_pemeriksaan'] ?? 0; ?></div>
                <div class="label">Dalam Pemeriksaan</div>
            </div>
            <div class="stat-card">
                <div class="number"><?= $stats['dalam_penanganan'] ?? 0; ?></div>
                <div class="label">Dalam Penanganan</div>
            </div>
            <div class="stat-card">
                <div class="number"><?= $stats['selesai'] ?? 0; ?></div>
                <div class="label">Selesai</div>
            </div>
            <div class="stat-card">
                <div class="number"><?= $stats['ditutup'] ?? 0; ?></div>
                <div class="label">Ditutup</div>
            </div>
        </div>

        <!-- Filter -->
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

        <!-- Tabel Laporan -->
        <?php if (mysqli_num_rows($result) > 0): ?>
            <table class="laporan-table">
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
                    <?php $no = 1; while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td class="kode"><?= htmlspecialchars($row['kode_laporan']); ?></td>
                            <td>
                                <?php if ($row['anonim'] == 1): ?>
                                    <em style="color:#94a3b8;">🎭 Anonim</em>
                                <?php else: ?>
                                    <?= htmlspecialchars($row['nama_pelapor'] ?? 'Unknown'); ?>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($row['nama_kategori'] ?? '-'); ?></td>
                            <td><?= date('d M Y', strtotime($row['tanggal_kejadian'])); ?></td>
                            <td><?= badge_urgensi($row['urgensi']); ?></td>
                            <td><?= badge_status($row['status']); ?></td>
                            <td>
                                <a href="tindak_lanjut.php?id=<?= $row['id']; ?>" class="btn-tindak">Tindak Lanjut →</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="empty-state">
                <h3>Tidak Ada Laporan</h3>
                <p>Belum ada laporan yang masuk<?= !empty($filter_status) ? ' dengan status ini' : ''; ?>.</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>