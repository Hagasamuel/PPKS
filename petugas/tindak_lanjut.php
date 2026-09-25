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
$report_id  = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($report_id === 0) {
    echo "<script>alert('Laporan tidak ditemukan!'); window.location='index.php';</script>";
    exit;
}

// ========== PROSES FORM ==========

// 1. Ubah status laporan
if (isset($_POST['update_status'])) {
    $status_baru = mysqli_real_escape_string($conn, $_POST['status']);
    
    // Ambil status lama DULU dari database
    $q_lama = mysqli_query($conn, "SELECT status FROM reports WHERE id='$report_id'");
    $data_lama = mysqli_fetch_assoc($q_lama);
    $status_lama = $data_lama['status'] ?? '';
    
    $status_valid = ['diajukan','diverifikasi','dalam_pemeriksaan','dalam_penanganan','selesai','ditutup'];
    
    if (in_array($status_baru, $status_valid)) {
        if ($status_baru === $status_lama) {
            // Status sama, jangan update
            echo "<script>alert('Status sudah sama dengan yang sekarang, tidak ada perubahan.'); window.location='tindak_lanjut.php?id=$report_id';</script>";
            exit;
        } else {
            // Status beda, update
            mysqli_query($conn, "UPDATE reports SET status='$status_baru' WHERE id='$report_id'");
            
            // Catat otomatis ke case_notes
            $catatan_auto = "Status laporan diubah menjadi: " . strtoupper(str_replace('_', ' ', $status_baru));
            mysqli_query($conn, "INSERT INTO case_notes (report_id, user_id, catatan, created_at) 
                                 VALUES ('$report_id', '$petugas_id', '$catatan_auto', NOW())");
            
            echo "<script>alert('Status berhasil diubah!'); window.location='tindak_lanjut.php?id=$report_id';</script>";
            exit;
        }
    }
}
// 2. Tambah catatan
if (isset($_POST['add_note'])) {
    $catatan = mysqli_real_escape_string($conn, $_POST['catatan']);
    if (!empty($catatan)) {
        mysqli_query($conn, "INSERT INTO case_notes (report_id, user_id, catatan, created_at) 
                             VALUES ('$report_id', '$petugas_id', '$catatan', NOW())");
        echo "<script>alert('Catatan berhasil ditambahkan!'); window.location='tindak_lanjut.php?id=$report_id';</script>";
        exit;
    }
}

// ========== AMBIL DATA ==========

// Data laporan
$query_report = "SELECT r.*, 
                        u.nama AS nama_pelapor, 
                        u.email AS email_pelapor,
                        u.no_hp AS hp_pelapor,
                        c.nama AS nama_kategori 
                 FROM reports r
                 LEFT JOIN user u ON r.user_id = u.id
                 LEFT JOIN categories c ON r.category_id = c.id
                 WHERE r.id = '$report_id'
                 LIMIT 1";
$result_report = mysqli_query($conn, $query_report);

if (!$result_report || mysqli_num_rows($result_report) === 0) {
    echo "<script>alert('Laporan tidak ditemukan!'); window.location='index.php';</script>";
    exit;
}
$report = mysqli_fetch_assoc($result_report);

// Bukti pendukung (semua)
$query_ev = mysqli_query($conn, "SELECT * FROM report_evidence WHERE report_id='$report_id'");
$bukti_list = [];
while ($b = mysqli_fetch_assoc($query_ev)) {
    $bukti_list[] = $b;
}

// Riwayat catatan
$query_notes = "SELECT cn.*, u.nama AS nama_petugas 
                FROM case_notes cn
                LEFT JOIN user u ON cn.user_id = u.id
                WHERE cn.report_id = '$report_id'
                ORDER BY cn.created_at DESC";
$notes_result = mysqli_query($conn, $query_notes);

// Helper badge status
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
    return "<span style='background:{$c['bg']};color:{$c['text']};padding:4px 12px;border-radius:20px;font-size:0.75rem;font-weight:600;text-transform:uppercase;'>" . htmlspecialchars($status) . "</span>";
}

function badge_urgensi($urgensi) {
    $map = [
        'rendah' => ['bg' => '#dcfce7', 'text' => '#166534'],
        'sedang' => ['bg' => '#fef3c7', 'text' => '#92400e'],
        'tinggi' => ['bg' => '#fee2e2', 'text' => '#991b1b'],
    ];
    $c = $map[$urgensi] ?? ['bg' => '#f1f5f9', 'text' => '#475569'];
    return "<span style='background:{$c['bg']};color:{$c['text']};padding:4px 12px;border-radius:20px;font-size:0.75rem;font-weight:600;text-transform:uppercase;'>" . htmlspecialchars($urgensi) . "</span>";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tindak Lanjut - Portal PPKS</title>
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        .container { max-width: 1000px; margin: 30px auto; padding: 0 20px; }
        .header-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px; }
        .header-bar h2 { color: #0f172a; }
        .btn-back { color: #64748b; text-decoration: none; font-size: 0.9rem; }
        .btn-back:hover { color: #0265cb; }

        .card { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 25px 30px; margin-bottom: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.04); }
        .card h3 { color: #0f172a; margin-bottom: 15px; border-bottom: 1px solid #e2e8f0; padding-bottom: 10px; font-size: 1.05rem; }

        .info-grid { display: grid; grid-template-columns: 180px 1fr; gap: 10px; font-size: 0.9rem; }
        .info-grid .label { color: #64748b; font-weight: 600; }
        .info-grid .value { color: #1e293b; }

        .kronologi-box { background: #f8fafc; border-left: 4px solid #0265cb; padding: 15px 20px; border-radius: 6px; line-height: 1.7; font-size: 0.9rem; white-space: pre-wrap; }

        .bukti-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 15px; margin-top: 10px; }
        .bukti-item { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; text-align: center; }
        .bukti-item img { width: 100%; height: 120px; object-fit: cover; display: block; }
        .bukti-item .file-icon { font-size: 2.5rem; padding: 25px 0 10px; color: #94a3b8; }
        .bukti-item .file-name { padding: 8px; font-size: 0.72rem; color: #475569; border-top: 1px solid #e2e8f0; }
        .bukti-item a { display: block; text-decoration: none; color: inherit; }

        .status-form { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }
        .status-form select { padding: 10px 14px; border-radius: 8px; border: 1px solid #cbd5e1; font-size: 0.9rem; }
        .status-form button { background: #0265cb; color: #fff; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 600; cursor: pointer; }
        .status-form button:hover { background: #0252a5; }

        textarea { width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-family: inherit; font-size: 0.9rem; resize: vertical; }
        .btn-submit { background: #16a34a; color: #fff; border: none; padding: 10px 24px; border-radius: 8px; font-weight: 600; cursor: pointer; margin-top: 10px; }
        .btn-submit:hover { background: #15803d; }

        .note-item { border-left: 4px solid #94a3b8; padding: 10px 15px; margin-bottom: 12px; background: #f8fafc; border-radius: 6px; }
        .note-item .note-header { font-size: 0.82rem; color: #64748b; margin-bottom: 6px; }
        .note-item .note-header strong { color: #334155; }
        .note-item .note-body { color: #1e293b; font-size: 0.9rem; line-height: 1.6; }
    </style>
</head>
<body>
    <header class="navbar">
        <div class="logo"><span>🛡️</span> Portal Layanan PPKS — Petugas</div>
        <ul class="nav-links">
            <li><a href="index.php">Dashboard</a></li>
            <li><a href="../logout.php">Logout (<?= htmlspecialchars($username); ?>)</a></li>
        </ul>
    </header>

    <div class="container">
        <div class="header-bar">
            <h2>⚙️ Tindak Lanjut Laporan</h2>
            <a href="index.php" class="btn-back">← Kembali ke Dashboard</a>
        </div>

        <!-- Info Laporan -->
        <div class="card">
            <h3>📌 Informasi Laporan — <?= htmlspecialchars($report['kode_laporan']); ?></h3>
            <div class="info-grid">
                <span class="label">Pelapor</span>
                <span class="value">
                    <?php if ($report['anonim'] == 1): ?>
                        <em>🎭 Anonim</em>
                    <?php else: ?>
                        <?= htmlspecialchars($report['nama_pelapor'] ?? '-'); ?> (<?= htmlspecialchars($report['email_pelapor'] ?? '-'); ?>)
                    <?php endif; ?>
                </span>

                <span class="label">Kategori</span>
                <span class="value"><?= htmlspecialchars($report['nama_kategori'] ?? '-'); ?></span>

                <span class="label">Tanggal Kejadian</span>
                <span class="value"><?= date('d F Y', strtotime($report['tanggal_kejadian'])); ?></span>

                <span class="label">Lokasi</span>
                <span class="value"><?= htmlspecialchars($report['lokasi']); ?></span>

                <span class="label">Terlapor</span>
                <span class="value"><?= htmlspecialchars($report['terlapor'] ?? '-'); ?></span>

                <span class="label">Saksi</span>
                <span class="value"><?= htmlspecialchars($report['saksi'] ?? '-'); ?></span>

                <span class="label">Urgensi</span>
                <span class="value"><?= badge_urgensi($report['urgensi']); ?></span>

                <span class="label">Status Saat Ini</span>
                <span class="value"><?= badge_status($report['status']); ?></span>
            </div>
        </div>

        <!-- Kronologi -->
        <div class="card">
            <h3>📝 Kronologi Kejadian</h3>
            <div class="kronologi-box"><?= htmlspecialchars($report['kronologi']); ?></div>
        </div>

        <!-- Bukti -->
        <div class="card">
            <h3>📎 Bukti Pendukung</h3>
            <?php if (count($bukti_list) > 0): ?>
                <div class="bukti-grid">
                    <?php foreach ($bukti_list as $bukti): 
                        $file_url = '../uploads/' . $bukti['file_path'];
                        $is_image = (strpos($bukti['tipe_file'], 'image') !== false);
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
                <p style="color:#94a3b8; font-style:italic;">Tidak ada bukti pendukung.</p>
            <?php endif; ?>
        </div>

        <!-- Form Ubah Status -->
        <div class="card">
            <h3>🔄 Ubah Status Laporan</h3>
            <form method="POST" class="status-form">
                <label for="status" style="font-weight:600;color:#334155;">Pilih Status Baru:</label>
                <select name="status" id="status" required>
                    <option value="">— Pilih Status —</option>
                    <option value="diverifikasi" <?= $report['status'] === 'diverifikasi' ? 'selected' : ''; ?>>Diverifikasi</option>
                    <option value="dalam_pemeriksaan" <?= $report['status'] === 'dalam_pemeriksaan' ? 'selected' : ''; ?>>Dalam Pemeriksaan</option>
                    <option value="dalam_penanganan" <?= $report['status'] === 'dalam_penanganan' ? 'selected' : ''; ?>>Dalam Penanganan</option>
                    <option value="selesai" <?= $report['status'] === 'selesai' ? 'selected' : ''; ?>>Selesai</option>
                    <option value="ditutup" <?= $report['status'] === 'ditutup' ? 'selected' : ''; ?>>Ditutup</option>
                </select>
                <button type="submit" name="update_status">Simpan Perubahan</button>
            </form>
        </div>

        <!-- Form Tambah Catatan -->
        <div class="card">
            <h3>✍️ Tambah Catatan Perkembangan</h3>
            <form method="POST">
                <textarea name="catatan" rows="4" placeholder="Tuliskan hasil konseling, wawancara, atau tindakan pendampingan..." required></textarea>
                <button type="submit" name="add_note" class="btn-submit">Simpan Catatan</button>
            </form>
        </div>

        <!-- Riwayat Catatan -->
        <div class="card">
            <h3>📜 Riwayat Penanganan (<?= mysqli_num_rows($notes_result); ?> catatan)</h3>
            <?php if (mysqli_num_rows($notes_result) > 0): ?>
                <?php while ($note = mysqli_fetch_assoc($notes_result)): ?>
                    <div class="note-item">
                        <div class="note-header">
                            <strong><?= htmlspecialchars($note['nama_petugas'] ?? 'Petugas'); ?></strong>
                            — <?= date('d M Y, H:i', strtotime($note['created_at'])); ?> WIB
                        </div>
                        <div class="note-body"><?= nl2br(htmlspecialchars($note['catatan'])); ?></div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="color:#94a3b8; font-style:italic;">Belum ada catatan untuk laporan ini.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>