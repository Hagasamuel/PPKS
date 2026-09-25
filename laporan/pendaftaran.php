<?php
session_start();
include '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pelapor') {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_report'])) {
    $user_id          = $_SESSION['user_id'];
    $category_id      = mysqli_real_escape_string($conn, $_POST['category_id']);
    $tanggal_kejadian = mysqli_real_escape_string($conn, $_POST['tanggal_kejadian']);
    $lokasi           = mysqli_real_escape_string($conn, $_POST['lokasi']);
    $kronologi        = mysqli_real_escape_string($conn, $_POST['kronologi']);
    $terlapor         = mysqli_real_escape_string($conn, $_POST['terlapor']);
    $saksi            = mysqli_real_escape_string($conn, $_POST['saksi']);
    $urgensi          = mysqli_real_escape_string($conn, $_POST['urgensi']);
    $anonim           = (int) $_POST['anonim'];
    $status           = 'diajukan';

    $kode_laporan = 'PPKS-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

    // Validasi wajib
    if (empty($category_id) || empty($tanggal_kejadian) || empty($lokasi) || empty($kronologi)) {
        echo "<script>alert('Kategori, tanggal, lokasi, dan kronologi wajib diisi!'); window.location='../user/buat_laporan.php';</script>";
        exit;
    }

    // 1. Simpan laporan ke tabel 'reports'
    $query_report = "INSERT INTO reports 
        (kode_laporan, user_id, category_id, tanggal_kejadian, lokasi, kronologi, 
         terlapor, saksi, urgensi, status, anonim, created_at) 
        VALUES 
        ('$kode_laporan', '$user_id', '$category_id', '$tanggal_kejadian', '$lokasi', '$kronologi', 
         '$terlapor', '$saksi', '$urgensi', '$status', '$anonim', NOW())";

    if (mysqli_query($conn, $query_report)) {
        $report_id = mysqli_insert_id($conn);

        // 2. Proses upload bukti (opsional)
        if (isset($_FILES['evidence']) && $_FILES['evidence']['error'] === UPLOAD_ERR_OK) {
            $file_tmp     = $_FILES['evidence']['tmp_name'];
            $file_name    = $_FILES['evidence']['name'];
            $file_size    = $_FILES['evidence']['size'];
            $file_type    = $_FILES['evidence']['type'];

            $ekstensi_valid = ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            if (in_array($file_ext, $ekstensi_valid) && $file_size <= 5000000) {
                // Nama file unik untuk disimpan di server
                $new_file_name = time() . '_' . uniqid() . '.' . $file_ext;
                $upload_path   = '../uploads/' . $new_file_name;

                if (move_uploaded_file($file_tmp, $upload_path)) {
                    // Escape string untuk keamanan
                    $file_name_esc = mysqli_real_escape_string($conn, $file_name);
                    $new_file_esc  = mysqli_real_escape_string($conn, $new_file_name);
                    $file_type_esc = mysqli_real_escape_string($conn, $file_type);

                    // Simpan info file LENGKAP ke tabel 'report_evidence'
                    $query_evidence = "INSERT INTO report_evidence 
                        (report_id, nama_file, file_path, tipe_file, created_at) 
                        VALUES 
                        ('$report_id', '$file_name_esc', '$new_file_esc', '$file_type_esc', NOW())";
                    
                    mysqli_query($conn, $query_evidence);
                }
            }
        }

        echo "<script>
                alert('Laporan berhasil dikirim! Kode laporan: $kode_laporan');
                window.location.href = '../user/laporan_saya.php';
              </script>";
        exit;
    } else {
        echo "Gagal mengirim laporan: " . mysqli_error($conn);
    }
} else {
    header("Location: ../user/buat_laporan.php");
    exit;
}
?>