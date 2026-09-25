<?php
// Konfigurasi koneksi database
$host     = "localhost";
$user_db  = "root";
$pass_db  = "";              // Kosongkan jika XAMPP default
$nama_db  = "ppks_db";          // ⚠️ GANTI sesuai nama database Anda

$conn = mysqli_connect($host, $user_db, $pass_db, $nama_db);

// Cek koneksi
if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// Set charset ke UTF-8
mysqli_set_charset($conn, "utf8mb4");
?>