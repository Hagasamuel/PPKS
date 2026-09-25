<?php
session_start();
include '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pelapor') {
    header("Location: ../login.php");
    exit;
}

$user_id  = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Ambil semua laporan user ini, urutkan dari yang terbaru
$query = "SELECT r.*, c.nama AS nama_kategori 
          FROM reports r 
          LEFT JOIN categories c ON r.category_id = c.id 
          WHERE r.user_id = '$user_id' 
          ORDER BY r.created_at DESC";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Saya - Portal PPKS</title>
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        .laporan-container {
            max-width: 1000px;
            margin: 30px auto;
            padding: 0 20px;
        }
        .laporan-container h2 {
            color: #0f172a;
            margin-bottom: 6px;
        }
        .laporan-container .subtitle {
            color: #64748b;
            margin-bottom: 25px;
        }
        .laporan-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 20px;
        }
        .laporan-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            transition: box-shadow 0.2s, transform 0.2s;
        }
        .laporan-card:hover {
            box-shadow: 0 6px 20px rgba(0,0,0,0.08);
            transform: translateY(-2px);
        }
        .laporan-kode {
            font-size: 0.75rem;
            color: #64748b;
            font-family: 'Courier New', monospace;
            background: #f1f5f9;
            padding: 3px 8px;
            border-radius: 4px;
            display: inline-block;
            margin-bottom: 12px;
        }
        .laporan-card h3 {
            font-size: 1.05rem;
            color: #0f172a;
            margin-bottom: 8px;
        }
        .laporan-meta {
            font-size: 0.8rem;
            color: #64748b;
            margin-bottom: 12px;
            line-height: 1.6;
        }
        .laporan-meta strong { color: #334155; }
        .badge-row {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-bottom: 15px;
        }
        .badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.72rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .badge-pending { background: #fef3c7; color: #92400e; }
        .badge-diproses { background: #dbeafe; color: #1e40af; }
        .badge-selesai { background: #dcfce7; color: #166534; }
        .badge-ditolak { background: #fee2e2; color: #991b1b; }
        .badge-rendah { background: #dcfce7; color: #166534; }
        .badge-sedang { background: #fef3c7; color: #92400e; }
        .badge-tinggi { background: #fee2e2; color: #991b1b; }
        .btn-detail {
            display: inline-block;
            background: #0265cb;
            color: #ffffff;
            padding: 8px 16px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.82rem;
            font-weight: 600;
            transition: background 0.2s;
        }
        .btn-detail:hover { background: #0252a5; }
        .empty-state {
            background: #ffffff;
            border: 2px dashed #cbd5e1;
            border-radius: 12px;
            padding: 50px 20px;
            text-align: center;
            color: #64748b;
        }
        .empty-state .icon {
            font-size: 3rem;
            margin-bottom: 15px;
        }
        .empty-state h3 { color: #334155; margin-bottom: 8px; }
        .empty-state p { margin-bottom: 20px; }
        .btn-buat {
            display: inline-block;
            background: #16a34a;
            color: #ffffff;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
        }
        .btn-buat:hover { background: #15803d; }
    </style>
</head>
<body>
    <header class="navbar">
        <div class="logo"><span>🛡️</span> Portal Layanan PPKS</div>
        <ul class="nav-links">
            <li><a href="index.php">Beranda</a></li>
            <li><a href="buat_laporan.php">Buat Laporan</a></li>
            <li><a href="laporan_saya.php" class="active">Laporan Saya</a></li>
            <li><a href="profil.php">Profil</a></li>
            <li><a href="../logout.php">Logout (<?= htmlspecialchars($username); ?>)</a></li>
        </ul>
    </header>

    <div class="laporan-container">
        <h2>📋 Laporan Saya</h2>
        <p class="subtitle">Daftar semua laporan yang pernah Anda kirimkan.</p>

        <?php if (mysqli_num_rows($result) > 0): ?>
            <div class="laporan-grid">
                <?php while ($row = mysqli_fetch_assoc($result)): 
                       // Tentukan class badge berdasarkan 6 status ENUM
                        $status_class = 'badge-diajukan';
                        if ($row['status'] === 'diverifikasi') $status_class = 'badge-diverifikasi';
                        elseif ($row['status'] === 'dalam_pemeriksaan') $status_class = 'badge-pemeriksaan';
                        elseif ($row['status'] === 'dalam_penanganan') $status_class = 'badge-penanganan';
                        elseif ($row['status'] === 'selesai') $status_class = 'badge-selesai';
                        elseif ($row['status'] === 'ditutup') $status_class = 'badge-ditutup';

                        $urgensi_class = 'badge-rendah';
                        if ($row['urgensi'] === 'sedang') $urgensi_class = 'badge-sedang';
                        elseif ($row['urgensi'] === 'tinggi') $urgensi_class = 'badge-tinggi';
                    ?>
                    <div class="laporan-card">
                        <span class="laporan-kode"><?= htmlspecialchars($row['kode_laporan']); ?></span>
                        
                        <h3><?= htmlspecialchars($row['nama_kategori'] ?? 'Tanpa Kategori'); ?></h3>
                        
                        <div class="laporan-meta">
                            <strong>📍 Lokasi:</strong> <?= htmlspecialchars($row['lokasi']); ?><br>
                            <strong>📅 Kejadian:</strong> <?= date('d M Y', strtotime($row['tanggal_kejadian'])); ?><br>
                            <strong>✍️ Dilaporkan:</strong> <?= date('d M Y, H:i', strtotime($row['created_at'])); ?>
                        </div>
                        
                        <div class="badge-row">
                            <span class="badge <?= $status_class; ?>"><?= htmlspecialchars($row['status']); ?></span>
                            <span class="badge <?= $urgensi_class; ?>">Urgensi: <?= htmlspecialchars($row['urgensi']); ?></span>
                            <?php if ($row['anonim'] == 1): ?>
                                <span class="badge" style="background:#ede9fe;color:#5b21b6;">🎭 Anonim</span>
                            <?php endif; ?>
                        </div>
                        
                        <a href="detail_laporan.php?id=<?= $row['id']; ?>" class="btn-detail">🔍 Lihat Detail →</a>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <div class="icon">📭</div>
                <h3>Belum Ada Laporan</h3>
                <p>Anda belum pernah membuat laporan. Silakan buat laporan baru jika Anda mengalami atau menyaksikan kekerasan seksual.</p>
                <a href="buat_laporan.php" class="btn-buat">+ Buat Laporan Baru</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>