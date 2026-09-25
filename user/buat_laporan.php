    <?php
    session_start();
    include '../config/database.php';

    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pelapor') {
        header("Location: ../login.php");
        exit;
    }
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Buat Laporan - Portal PPKS</title>
        <link rel="stylesheet" href="../assets/style.css">
    </head>
    <body>
        <header class="navbar">
            <div class="logo"><span>🛡️</span> Portal Layanan PPKS</div>
            <ul class="nav-links">
                <li><a href="index.php">Beranda</a></li>
                <li><a href="buat_laporan.php" class="active">Buat Laporan</a></li>
                <li><a href="laporan_saya.php">Laporan Saya</a></li>
                <li><a href="profil.php">Profil</a></li>
                <li><a href="../logout.php">Logout (<?= htmlspecialchars($_SESSION['username']); ?>)</a></li>
            </ul>
        </header>

        <div class="form-container">
            <h2>Form Pengaduan PPKS</h2>
            <p style="color:#64748b; margin-bottom: 20px;">
                Isi form di bawah ini dengan detail kejadian yang Anda alami atau saksikan.
            </p>

            <form action="../laporan/pendaftaran.php" method="POST" enctype="multipart/form-data">
                
                <label for="category_id">Kategori Kejadian:</label>
                <select name="category_id" id="category_id" required>
                    <option value="">-- Pilih Kategori --</option>
                <?php
                    $categories = mysqli_query($conn, "SELECT * FROM categories WHERE status = 1");
                    if ($categories && mysqli_num_rows($categories) > 0) {
                    while ($cat = mysqli_fetch_assoc($categories)) {
                    echo "<option value='".$cat['id']."'>".htmlspecialchars($cat['nama'])."</option>";
                }
                 } else {
                    echo "<option value=''>-- Belum ada kategori --</option>";
                }
                ?>
                 </select>
                <label for="tanggal_kejadian">Tanggal Kejadian:</label>
                <input type="date" name="tanggal_kejadian" id="tanggal_kejadian" required>

                <label for="lokasi">Lokasi Kejadian:</label>
                <input type="text" name="lokasi" id="lokasi" placeholder="Contoh: Gedung A, Lantai 2" required>

                <label for="kronologi">Kronologi Kejadian:</label>
                <textarea name="kronologi" id="kronologi" rows="6" 
                        placeholder="Jelaskan secara rinci kronologi kejadian..." required></textarea>

                <label for="terlapor">Nama Terlapor (Pihak yang Dilaporkan):</label>
                <input type="text" name="terlapor" id="terlapor" 
                    placeholder="Kosongkan jika tidak ingin menyebutkan">

                <label for="saksi">Saksi (Jika Ada):</label>
                <input type="text" name="saksi" id="saksi" placeholder="Nama saksi (opsional)">

                <label for="urgensi">Tingkat Urgensi:</label>
                <select name="urgensi" id="urgensi" required>
                    <option value="">-- Pilih Tingkat Urgensi --</option>
                    <option value="rendah">Rendah</option>
                    <option value="sedang">Sedang</option>
                    <option value="tinggi">Tinggi</option>
                </select>

                <label for="anonim">Sembunyikan Identitas Anda? (Anonim)</label>
                <select name="anonim" id="anonim" required>
                    <option value="0">Tidak (Identitas terlihat oleh petugas)</option>
                    <option value="1">Ya (Anonim)</option>
                </select>

                <label for="evidence">Upload Bukti Pendukung (Foto/Dokumen):</label>
                <input type="file" name="evidence" id="evidence" accept="image/*,.pdf,.doc,.docx">

                <button type="submit" name="submit_report" class="btn-submit">Kirim Laporan</button>
            </form>
        </div>
    </body>
    </html>