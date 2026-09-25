<?php
session_start();
include '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pelapor') {
    header("Location: ../login.php");
    exit;
}

$user_id  = $_SESSION['user_id'];
$username = $_SESSION['username'];

$query_count = mysqli_query($conn, "SELECT COUNT(*) AS total FROM reports WHERE user_id = '$user_id'");
$count = $query_count ? mysqli_fetch_assoc($query_count) : ['total' => 0];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard User - Portal PPKS</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
        <header class="navbar">
        <div class="logo"><span>🛡️</span> Portal Layanan PPKS</div>
        <ul class="nav-links">
            <li><a href="index.php" class="active">Beranda</a></li>
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
    <section class="hero">
        <h1>Selamat Datang, <?= htmlspecialchars($username); ?>! 👋</h1>
        <p>Anda login sebagai <strong>Pelapor</strong>. Silakan buat laporan jika Anda mengalami atau menyaksikan kekerasan seksual.</p>
        <a href="buat_laporan.php" class="btn-cta">Buat Laporan Baru</a>
        <a href="laporan_saya.php" class="btn-cta" style="background:#2563eb; margin-left:10px;">
            Lihat Laporan Saya (<?= $count['total']; ?>)
        </a>
    </section>

    <section class="features-container">
        <div class="feature-card">
            <div class="icon-box">🔒</div>
            <h3>Kerahasiaan Terjamin</h3>
            <p>Semua laporan Anda dijamin kerahasiaannya dan hanya bisa diakses oleh petugas berwenang.</p>
        </div>
        <div class="feature-card">
            <div class="icon-box">📋</div>
            <h3>Pantau Status Laporan</h3>
            <p>Anda dapat memantau status laporan melalui menu "Laporan Saya".</p>
        </div>
        <div class="feature-card">
            <div class="icon-box">💬</div>
            <h3>Dukungan Psikologis</h3>
            <p>Tim ahli siap memberikan pendampingan psikologis dan bantuan moral untuk Anda.</p>
        </div>
    </section>
        <script src="../assets/navbar.js"></script>
</body>
</body>
</html> 