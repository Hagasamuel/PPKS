<?php
session_start();
include 'config/database.php';

// Ambil artikel atau info edukasi (statis atau dari DB)
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edukasi & Artikel - PPKS</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

    <!-- Header Navbar -->
    <header class="navbar">
        <div class="logo">
            <span>🛡️</span> Portal Layanan PPKS
        </div>
        <ul class="nav-links">
            <li><a href="index.php">Beranda</a></li>
            <li><a href="artikel.php" class="active">Artikel</a></li>
            <li><a href="buat_laporan.php">Daftar / Lapor</a></li>
            <li><a href="login.php">Login</a></li>
        </ul>
    </header>

    <!-- Content -->
    <div class="container">
        <h2>Edukasi & Informasi PPKS</h2>
        <p>Materi sosialisasi dan panduan pencegahan kekerasan seksual di lingkungan kampus.</p>
        <br>

        <div class="cards-grid">
            <div class="card">
                <h3>Mengenal Bentuk-Bentuk Kekerasan Seksual</h3>
                <p style="font-size: 0.9rem; font-weight: normal; margin-top: 10px;">
                    Pahami jenis-jenis tindakan yang masuk dalam kategori kekerasan seksual sesuai dengan regulasi yang berlaku.
                </p>
            </div>
            <div class="card">
                <h3>Hak dan Perlindungan Korban</h3>
                <p style="font-size: 0.9rem; font-weight: normal; margin-top: 10px;">
                    Setiap pelapor dan korban berhak mendapatkan pendampingan hukum, konseling psikologis, dan jaminan kerahasiaan identitas.
                </p>
            </div>
            <div class="card">
                <h3>Alur Prosedur Penanganan Adopsi Kasus</h3>
                <p style="font-size: 0.9rem; font-weight: normal; margin-top: 10px;">
                    Langkah demi langkah penanganan kasus dari penerimaan laporan hingga tindakan lanjut oleh Satgas PPKS.
                </p>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <p>&copy; 2026 Portal PPKS - Semua Hak Dilindungi</p>
    </footer>

</body>
</html>