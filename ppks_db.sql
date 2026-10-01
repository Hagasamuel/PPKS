-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 01 Okt 2026 pada 12.13
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ppks_db`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `assignments`
--

CREATE TABLE `assignments` (
  `id` int(11) NOT NULL,
  `report_id` int(11) NOT NULL,
  `petugas_id` int(11) NOT NULL,
  `assigned_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `case_notes`
--

CREATE TABLE `case_notes` (
  `id` int(11) NOT NULL,
  `report_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `catatan` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `case_notes`
--

INSERT INTO `case_notes` (`id`, `report_id`, `user_id`, `catatan`, `created_at`) VALUES
(4, 2, 8, 'Status laporan diubah menjadi: DALAM PEMERIKSAAN', '2026-09-19 06:47:59');

-- --------------------------------------------------------

--
-- Struktur dari tabel `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `status` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `categories`
--

INSERT INTO `categories` (`id`, `nama`, `deskripsi`, `status`, `created_at`) VALUES
(1, 'Pelecehan Verbal', 'Ucapan atau komentar yang bersifat melecehkan', 1, '2026-09-18 18:37:36'),
(2, 'Pelecehan Fisik', 'Sentuhan atau tindakan fisik yang tidak diinginkan', 1, '2026-09-18 18:37:36'),
(3, 'Pelecehan Online', 'Pelecehan melalui media sosial atau chat', 1, '2026-09-18 18:37:36'),
(4, 'Pemaksaan', 'Tindakan memaksa untuk melakukan sesuatu', 1, '2026-09-18 18:37:36'),
(5, 'Kekerasan Seksual', 'Tindakan kekerasan yang bersifat seksual', 1, '2026-09-18 18:37:36'),
(6, 'Lainnya', 'Kategori lain yang tidak tercantum', 1, '2026-09-18 18:37:36');

-- --------------------------------------------------------

--
-- Struktur dari tabel `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `report_id` int(11) DEFAULT NULL,
  `judul` varchar(150) NOT NULL,
  `pesan` text NOT NULL,
  `sudah_dibaca` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `reports`
--

CREATE TABLE `reports` (
  `id` int(11) NOT NULL,
  `kode_laporan` varchar(30) NOT NULL,
  `user_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `tanggal_kejadian` date NOT NULL,
  `lokasi` varchar(255) NOT NULL,
  `kronologi` text NOT NULL,
  `terlapor` text DEFAULT NULL,
  `saksi` text DEFAULT NULL,
  `urgensi` enum('rendah','sedang','tinggi') DEFAULT 'sedang',
  `status` enum('diajukan','diverifikasi','dalam_pemeriksaan','dalam_penanganan','selesai','ditutup') DEFAULT 'diajukan',
  `anonim` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `reports`
--

INSERT INTO `reports` (`id`, `kode_laporan`, `user_id`, `category_id`, `tanggal_kejadian`, `lokasi`, `kronologi`, `terlapor`, `saksi`, `urgensi`, `status`, `anonim`, `created_at`) VALUES
(1, 'PPKS-20260918-E043', 6, 2, '2026-09-11', 'Gedung A', 'jatuh dari tangga', 'Chris', 'adit', 'rendah', 'diajukan', 0, '2026-09-18 18:43:20'),
(2, 'PPKS-20260918-ED2B', 6, 3, '2026-09-09', 'Gedung RIU', 'jatuh di toilet', 'Chris', 'Tian', 'rendah', 'dalam_pemeriksaan', 0, '2026-09-18 18:56:38'),
(3, 'PPKS-20260921-A7DC', 12, 2, '2026-09-10', 'Gedung Pl', 'Verbal\r\n', 'Gata', 'Joko', 'sedang', 'diajukan', 1, '2026-09-21 14:56:14'),
(4, 'PPKS-20260927-0410', 16, 1, '2026-08-31', 'Gedung A', 'jatuh', 'Gata', 'adit', 'rendah', 'diajukan', 1, '2026-09-27 14:21:53');

-- --------------------------------------------------------

--
-- Struktur dari tabel `report_evidence`
--

CREATE TABLE `report_evidence` (
  `id` int(11) NOT NULL,
  `report_id` int(11) NOT NULL,
  `nama_file` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `tipe_file` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `report_evidence`
--

INSERT INTO `report_evidence` (`id`, `report_id`, `nama_file`, `file_path`, `tipe_file`, `created_at`) VALUES
(1, 1, 'WhatsApp Image 2026-09-17 at 21.01.12.jpeg', '1789757000_6aad86487ed72.jpeg', 'image/jpeg', '2026-09-18 18:43:20'),
(2, 2, 'matematika 1.jpeg', '1789757798_6aad8966ef6e7.jpeg', 'image/jpeg', '2026-09-18 18:56:38'),
(3, 3, 'WhatsApp Image 2026-09-21 at 19.27.59.jpeg', '1790002574_6ab1458edb080.jpeg', 'image/jpeg', '2026-09-21 14:56:14'),
(4, 4, 'ChatGPT Image 27 Sep 2026, 14.23.52.png', '1790518913_6ab926815140f.png', 'image/png', '2026-09-27 14:21:53');

-- --------------------------------------------------------

--
-- Struktur dari tabel `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','petugas','pelapor') DEFAULT 'pelapor',
  `no_hp` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `user`
--

INSERT INTO `user` (`id`, `nama`, `email`, `password`, `role`, `no_hp`, `created_at`) VALUES
(6, 'Budi', 'buditest@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'pelapor', NULL, '2026-09-18 17:55:41'),
(8, 'Petugas Test', 'petugastest@gmail.com', '$2y$10$aZHfafwWQrEoXVDyG4sOI.LSc7m.Q023q/fczPj74oLAcPEl6e.8W', 'petugas', NULL, '2026-09-19 06:24:56'),
(12, 'daniel', 'daniel321@gmail.com', '$2y$10$xzHFe5GKuag1c1qLLkakWuktNNAjQsgDcERRGnaOpQJFuFbNy1lwq', 'pelapor', NULL, '2026-09-21 14:15:34'),
(14, 'admin', 'admin123@gmail.com', '$2y$10$85wykyLMPfNr5rZCSZoF9e/j7Uli9X1OLe4SsRDAwkc1W8S8TNvOm', 'pelapor', NULL, '2026-09-23 14:01:07'),
(15, 'Admin Test', 'admin@test.com', '$2y$10$4epGgNHWzojGzZg/rSjcGukLs7YQKmmkDe/JeoC18.m1FpIN7xqC.', 'admin', NULL, '2026-09-23 14:04:35'),
(16, 'haga', 'haga123@gmail.com', '$2y$10$ZKSbccy8wI8dbCGtiI0UCe2Yoed5RK0ozHRXEHG1UJSJbpWrXTx.u', 'pelapor', NULL, '2026-09-27 14:20:54');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `assignments`
--
ALTER TABLE `assignments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `report_id` (`report_id`),
  ADD KEY `petugas_id` (`petugas_id`);

--
-- Indeks untuk tabel `case_notes`
--
ALTER TABLE `case_notes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `report_id` (`report_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_laporan` (`kode_laporan`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indeks untuk tabel `report_evidence`
--
ALTER TABLE `report_evidence`
  ADD PRIMARY KEY (`id`),
  ADD KEY `report_id` (`report_id`);

--
-- Indeks untuk tabel `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `assignments`
--
ALTER TABLE `assignments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `case_notes`
--
ALTER TABLE `case_notes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `reports`
--
ALTER TABLE `reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `report_evidence`
--
ALTER TABLE `report_evidence`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `assignments`
--
ALTER TABLE `assignments`
  ADD CONSTRAINT `assignments_ibfk_1` FOREIGN KEY (`report_id`) REFERENCES `reports` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `assignments_ibfk_2` FOREIGN KEY (`petugas_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `case_notes`
--
ALTER TABLE `case_notes`
  ADD CONSTRAINT `case_notes_ibfk_1` FOREIGN KEY (`report_id`) REFERENCES `reports` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `case_notes_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);

--
-- Ketidakleluasaan untuk tabel `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `reports`
--
ALTER TABLE `reports`
  ADD CONSTRAINT `reports_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `reports_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`);

--
-- Ketidakleluasaan untuk tabel `report_evidence`
--
ALTER TABLE `report_evidence`
  ADD CONSTRAINT `report_evidence_ibfk_1` FOREIGN KEY (`report_id`) REFERENCES `reports` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
