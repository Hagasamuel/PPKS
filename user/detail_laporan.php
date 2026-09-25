<?php
session_start();
include '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pelapor') {
    header("Location: ../login.php");
    exit;
}

$user_id   = $_SESSION['user_id'];
$username  = $_SESSION['username'];
$report_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

$query = "SELECT r.*, c.nama AS nama_kategori 
          FROM reports r 
          LEFT JOIN categories c ON r.category_id = c.id 
          WHERE r.id = '$report_id' AND r.user_id = '$user_id' 
          LIMIT 1";
$result = mysqli_query($conn, $query);

if (!$result || mysqli_num_rows($result) === 0) {
    echo "<script>alert('Laporan tidak ditemukan!'); window.location='laporan_saya.php';</script>";
    exit;
}

$laporan = mysqli_fetch_assoc($result);

$query_ev = "SELECT * FROM report_evidence WHERE report_id = '$report_id'";
$result_ev = mysqli_query($conn, $query_ev);
$bukti_list = [];
if ($result_ev) {
    while ($row = mysqli_fetch_assoc($result_ev)) {
        $bukti_list[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Laporan - Portal PPKS</title>
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        .detail-container { max-width: 850px; margin: 30px auto; padding: 0 20px; }
        .detail-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px; }
        .detail-header h2 { color: #0f172a; }
        .btn-back { color: #64748b; text-decoration: none; font-size: 0.9rem; }
        .btn-back:hover { color: #0265cb; }
        .detail-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 25px 30px; margin-bottom: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.04); }
        .detail-card h3 { color: #0f172a; margin-bottom: 15px; border-bottom: 1px solid #e2e8f0; padding-bottom: 10px; font-size: 1.05rem; }
        .detail-row { display: grid; grid-template-columns: 180px 1fr; gap: 10px; padding: 8px 0; border-bottom: 1px dashed #f1f5f9; font-size: 0.9rem; }
        .detail-row:last-child { border-bottom: none; }
        .detail-row .label { color: #64748b; font-weight: 600; }
        .detail-row .value { color: #1e293b; word-break: break-word; }
        .badge { padding: 4px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.3px; display: inline-block; }
        .badge-pending { background: #fef3c7; color: #92400e; }
        .badge-diproses { background: #dbeafe; color: #1e40af; }
        .badge-selesai { background: #dcfce7; color: #166534; }
        .badge-ditolak { background: #fee2e2; color: #991b1b; }
        .badge-rendah { background: #dcfce7; color: #166534; }
        .badge-sedang { background: #fef3c7; color: #92400e; }
        .badge-tinggi { background: #fee2e2; color: #991b1b; }
        .kronologi-box { background: #f8fafc; border-left: 4px solid #0265cb; padding: 15px 20px; border-radius: 6px; color: #334155; line-height: 1.7; font-size: 0.9rem; white-space: pre-wrap; }
        .bukti-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 15px; margin-top: 10px; }
        .bukti-item { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; text-align: center; transition: box-shadow 0.2s; }
        .bukti-item:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
        .bukti-item img { width: 100%; height: 140px; object-fit: cover; display: block; }
        .bukti-item .file-icon { font-size: 3rem; padding: 30px 0 15px; color: #94a3b8; }
        .bukti-item .file-name { padding: 10px; font-size: 0.75rem; color: #475569; word-break: break-all; border-top: 1px solid #e2e8f0; }
        .bukti-item a { display: block; text-decoration: none; color: inherit; }
        .empty-bukti { color: #94a3b8; font-style: italic; padding: 15px 0; font-size: 0.9rem; }
        .info-box { background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 15px 20px; color: #1e40af; font-size: 0.85rem; line-height: 1.6; }
    </style>
</head>
<body>
        <header class="navbar">
        <div class="logo"><span>🛡️</span> Portal Layanan PPKS</div>
        <ul class="nav-links">
            <li><a href="index.php">Beranda</a></li>
            <li><a href="buat_laporan.php">Buat Laporan</a></li>
            <li><a href="laporan_saya.php" class="active">Laporan Saya</a></li>
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

    <div class="detail-container">
        <div class="detail-header">
            <h2>🔍 Detail Laporan</h2>
            <a href="laporan_saya.php" class="btn-back">← Kembali ke Laporan Saya</a>
        </div>

        <?php
        $status_class = 'badge-pending';
        if ($laporan['status'] === 'diproses') $status_class = 'badge-diproses';
        elseif ($laporan['status'] === 'selesai') $status_class = 'badge-selesai';
        elseif ($laporan['status'] === 'ditolak') $status_class = 'badge-ditolak';

        $urgensi_class = 'badge-rendah';
        if ($laporan['urgensi'] === 'sedang') $urgensi_class = 'badge-sedang';
        elseif ($laporan['urgensi'] === 'tinggi') $urgensi_class = 'badge-tinggi';
        ?>

        <div class="detail-card">
            <h3>📌 Informasi Laporan</h3>
            <div class="detail-row"><span class="label">Kode Laporan</span><span class="value"><strong><?= htmlspecialchars($laporan['kode_laporan']); ?></strong></span></div>
            <div class="detail-row"><span class="label">Kategori</span><span class="value"><?= htmlspecialchars($laporan['nama_kategori'] ?? 'Tanpa Kategori'); ?></span></div>
            <div class="detail-row"><span class="label">Status</span><span class="value"><span class="badge <?= $status_class; ?>"><?= htmlspecialchars($laporan['status']); ?></span></span></div>
            <div class="detail-row"><span class="label">Tingkat Urgensi</span><span class="value"><span class="badge <?= $urgensi_class; ?>"><?= htmlspecialchars($laporan['urgensi']); ?></span></span></div>
            <div class="detail-row"><span class="label">Tanggal Kejadian</span><span class="value"><?= date('d F Y', strtotime($laporan['tanggal_kejadian'])); ?></span></div>
            <div class="detail-row"><span class="label">Lokasi</span><span class="value"><?= htmlspecialchars($laporan['lokasi']); ?></span></div>
            <div class="detail-row"><span class="label">Terlapor</span><span class="value"><?= !empty($laporan['terlapor']) ? htmlspecialchars($laporan['terlapor']) : '<em style="color:#94a3b8;">Tidak disebutkan</em>'; ?></span></div>
            <div class="detail-row"><span class="label">Saksi</span><span class="value"><?= !empty($laporan['saksi']) ? htmlspecialchars($laporan['saksi']) : '<em style="color:#94a3b8;">Tidak ada</em>'; ?></span></div>
            <div class="detail-row"><span class="label">Anonim</span><span class="value"><?= $laporan['anonim'] == 1 ? '🎭 Ya' : '👤 Tidak'; ?></span></div>
            <div class="detail-row"><span class="label">Tanggal Dilaporkan</span><span class="value"><?= date('d F Y, H:i', strtotime($laporan['created_at'])); ?> WIB</span></div>
        </div>

        <div class="detail-card">
            <h3>📝 Kronologi Kejadian</h3>
            <div class="kronologi-box"><?= htmlspecialchars($laporan['kronologi']); ?></div>
        </div>

        <div class="detail-card">
            <h3>📎 Bukti Pendukung</h3>
            <?php if (count($bukti_list) > 0): ?>
                <div class="bukti-grid">
                    <?php foreach ($bukti_list as $bukti): 
                        $file_url = '../uploads/' . $bukti['file_path'];
                        $tipe = $bukti['tipe_file'];
                        $is_image = (strpos($tipe, 'image') !== false);
                    ?>
                        <div class="bukti-item">
                            <a href="<?= $file_url; ?>" target="_blank">
                                <?php if ($is_image): ?>
                                    <img src="<?= $file_url; ?>" alt="Bukti">
                                <?php else: ?>
                                    <div class="file-icon">📄</div>
                                <?php endif; ?>
                                <div class="file-name"><?= htmlspecialchars($bukti['nama_file']); ?></div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="empty-bukti">Tidak ada bukti pendukung yang diunggah.</p>
            <?php endif; ?>
        </div>

        <div class="info-box">
            💡 <strong>Catatan:</strong> Status laporan Anda akan diperbarui oleh petugas setelah laporan diverifikasi dan ditindaklanjuti. Anda akan bisa melihat perkembangannya di halaman ini.
        </div>
    </div>
    <script src="../assets/navbar.js"></script>
</body>
</html>