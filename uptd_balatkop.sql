-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 10 Mar 2026 pada 21.19
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
-- Database: `uptd_balatkop`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `evaluasi_pelatihan`
--

CREATE TABLE `evaluasi_pelatihan` (
  `id` int(11) NOT NULL,
  `peserta_id` int(11) NOT NULL,
  `pelatihan_id` int(11) NOT NULL,
  `instruktur_id` int(11) NOT NULL,
  `nilai_kehadiran` int(11) DEFAULT NULL,
  `nilai_partisipasi` int(11) DEFAULT NULL,
  `nilai_praktik` int(11) DEFAULT NULL,
  `nilai_sikap` int(11) DEFAULT NULL,
  `nilai_akhir` decimal(5,2) DEFAULT NULL,
  `keterangan` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `evaluasi_pelatihan`
--

INSERT INTO `evaluasi_pelatihan` (`id`, `peserta_id`, `pelatihan_id`, `instruktur_id`, `nilai_kehadiran`, `nilai_partisipasi`, `nilai_praktik`, `nilai_sikap`, `nilai_akhir`, `keterangan`, `created_at`) VALUES
(1, 4, 5, 8, 100, 100, 89, 100, 97.25, 'ini adalah keterang evaluasi', '2026-02-02 09:23:28'),
(2, 4, 1, 7, 100, 68, 89, 40, 74.25, 'ini adalah keterangan', '2026-02-02 09:56:39'),
(3, 4, 2, 10, 20, 40, 50, 60, 42.50, 'paja pakak', '2026-02-02 11:41:36'),
(4, 5, 2, 10, 100, 100, 100, 100, 100.00, 'qSAFA', '2026-02-02 18:15:47'),
(5, 7, 6, 8, 100, 75, 90, 100, 91.25, 'mantap dek', '2026-02-05 16:17:50');

-- --------------------------------------------------------

--
-- Struktur dari tabel `jadwal`
--

CREATE TABLE `jadwal` (
  `id` int(11) NOT NULL,
  `pelatihan_id` int(11) NOT NULL,
  `tanggal` date NOT NULL,
  `jam_mulai` time NOT NULL,
  `jam_selesai` time NOT NULL,
  `ruangan_id` int(11) NOT NULL,
  `lokasi` varchar(150) NOT NULL,
  `keterangan` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `jadwal`
--

INSERT INTO `jadwal` (`id`, `pelatihan_id`, `tanggal`, `jam_mulai`, `jam_selesai`, `ruangan_id`, `lokasi`, `keterangan`, `created_at`, `updated_at`) VALUES
(1, 1, '2026-02-18', '07:30:00', '11:13:00', 3, 'zfdsfsdf', 'ini adalah keterangan', '2026-02-01 17:07:53', '2026-02-08 20:20:00'),
(2, 5, '2026-02-18', '07:30:00', '11:13:00', 4, 'UPTD BALATKOP', 'ini adalah keterangan', '2026-02-02 09:13:36', '2026-02-08 20:20:37'),
(3, 2, '2026-02-03', '18:39:00', '23:44:00', 3, 'Lab Komputer', 'ini adalah keterangan', '2026-02-02 11:40:08', '2026-02-08 20:17:03'),
(4, 6, '2026-02-10', '08:00:00', '12:45:00', 2, 'Gedung Conference', 'ini adalah keterangan', '2026-02-05 10:25:45', '2026-02-08 20:16:43'),
(5, 7, '2026-03-09', '08:00:00', '13:00:00', 2, '', 'DSFSDFSD', '2026-03-09 15:55:45', '2026-03-09 15:55:45');

-- --------------------------------------------------------

--
-- Struktur dari tabel `notifikasi_log`
--

CREATE TABLE `notifikasi_log` (
  `id` int(11) NOT NULL,
  `peserta_id` int(11) NOT NULL,
  `jadwal_id` int(11) NOT NULL,
  `tipe` varchar(50) DEFAULT 'WhatsApp',
  `telepon` varchar(20) NOT NULL,
  `pesan` text DEFAULT NULL,
  `status` enum('pending','sent','failed') DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `sent_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `notifikasi_log`
--

INSERT INTO `notifikasi_log` (`id`, `peserta_id`, `jadwal_id`, `tipe`, `telepon`, `pesan`, `status`, `created_at`, `sent_at`) VALUES
(1, 4, 2, 'WhatsApp', '6285157558469', 'Assalamu\'alaikum,\r\n\r\nReminder: Jadwal Pelatihan Persiapan Jadi UMKM 2026 Batch 1\r\n\r\nTanggal: 18 Februari 2026\r\nJam: 07:30 - 11:30 WIB\r\nLokasi: UPTD BALATKOP\r\n\r\nMohon untuk hadir tepat waktu.\r\nTerima kasih.', 'sent', '2026-02-05 09:14:31', NULL),
(2, 6, 2, 'WhatsApp', '6281374711095', 'Assalamu\'alaikum,\r\n\r\nReminder: Jadwal Pelatihan Persiapan Jadi UMKM 2026 Batch 1\r\n\r\nTanggal: 18 Februari 2026\r\nJam: 07:30 - 11:30 WIB\r\nLokasi: UPTD BALATKOP\r\n\r\nMohon untuk hadir tepat waktu.\r\nTerima kasih.', 'sent', '2026-02-05 09:14:32', NULL),
(3, 4, 2, 'WhatsApp', '6285157558469', 'Assalamu\'alaikum,\r\n\r\nReminder: Jadwal Pelatihan Persiapan Jadi UMKM 2026 Batch 1\r\n\r\nTanggal: 18 Februari 2026\r\nJam: 07:30 - 11:30 WIB\r\nLokasi: UPTD BALATKOP\r\n\r\nMohon untuk hadir tepat waktu.\r\nTerima kasih.', 'sent', '2026-02-05 09:15:47', NULL),
(4, 6, 2, 'WhatsApp', '6289514732426', 'Assalamu\'alaikum,\r\n\r\nReminder: Jadwal Pelatihan Persiapan Jadi UMKM 2026 Batch 1\r\n\r\nTanggal: 18 Februari 2026\r\nJam: 07:30 - 11:30 WIB\r\nLokasi: UPTD BALATKOP\r\n\r\nMohon untuk hadir tepat waktu.\r\nTerima kasih.', 'sent', '2026-02-05 09:15:48', NULL),
(5, 4, 2, 'WhatsApp', '6285157558469', 'Assalamu\'alaikum,\r\n\r\nReminder: Jadwal Pelatihan Persiapan Jadi UMKM 2026 Batch 1\r\n\r\nTanggal: 18 Februari 2026\r\nJam: 07:30 - 11:30 WIB\r\nLokasi: UPTD BALATKOP\r\n\r\nMohon untuk hadir tepat waktu.\r\nTerima kasih.', 'sent', '2026-02-05 10:22:33', NULL),
(6, 6, 2, 'WhatsApp', '6289514732426', 'Assalamu\'alaikum,\r\n\r\nReminder: Jadwal Pelatihan Persiapan Jadi UMKM 2026 Batch 1\r\n\r\nTanggal: 18 Februari 2026\r\nJam: 07:30 - 11:30 WIB\r\nLokasi: UPTD BALATKOP\r\n\r\nMohon untuk hadir tepat waktu.\r\nTerima kasih.', 'sent', '2026-02-05 10:22:33', NULL),
(7, 5, 2, 'WhatsApp', '6281365905047', 'Assalamu\'alaikum,\r\n\r\nReminder: Jadwal Pelatihan Persiapan Jadi UMKM 2026 Batch 1\r\n\r\nTanggal: 18 Februari 2026\r\nJam: 07:30 - 11:30 WIB\r\nLokasi: UPTD BALATKOP\r\n\r\nMohon untuk hadir tepat waktu.\r\nTerima kasih.', 'sent', '2026-02-05 10:22:34', NULL),
(8, 5, 4, 'WhatsApp', '6281365905047', 'Assalamu\'alaikum,\r\n\r\nReminder: Jadwal Pelatihan Belajar Personal Branding UMKM\r\n\r\nTanggal: 10 Februari 2026\r\nJam: 08:00 - 12:45 WIB\r\nLokasi: Gedung Conference\r\nRuangan 2\r\n\r\nMohon untuk hadir tepat waktu.\r\nTerima kasih.', 'sent', '2026-02-05 10:26:14', NULL),
(9, 5, 4, 'WhatsApp', '6281365905047', 'Assalamu\'alaikum,\r\n\r\nReminder: Jadwal Pelatihan Belajar Personal Branding UMKM\r\n\r\nTanggal   : 10 Februari 2026\r\nJam       : 08:00 - 12:45 WIB\r\nLokasi    : Gedung Conference\r\n\r\nKeterangan:\r\nRuangan 2\r\n\r\nMohon untuk hadir tepat waktu.\r\nTerima kasih.\r\n\r\nWassalamu\'alaikum', 'sent', '2026-02-05 10:31:43', NULL),
(10, 5, 2, 'WhatsApp', '6281365905047', 'Assalamu\'alaikum,\r\n\r\nReminder: Jadwal Pelatihan Persiapan Jadi UMKM 2026 Batch 1\r\n\r\nTanggal   : 18 Februari 2026\r\nJam       : 07:30 - 11:30 WIB\r\nLokasi    : UPTD BALATKOP\r\n\r\nKeterangan:\r\nini adalah keterangan\r\n\r\nMohon untuk hadir tepat waktu.\r\nTerima kasih.\r\n\r\nWassalamu\'alaikum', 'sent', '2026-02-05 16:10:58', NULL),
(11, 5, 2, 'WhatsApp', '6281365905047', 'Assalamu\'alaikum,\r\n\r\nReminder: Jadwal Pelatihan Persiapan Jadi UMKM 2026 Batch 1\r\n\r\nTanggal   : 18 Februari 2026\r\nJam       : 07:30 - 11:30 WIB\r\nLokasi    : UPTD BALATKOP\r\n\r\nKeterangan:\r\nini adalah keterangan\r\n\r\nMohon untuk hadir tepat waktu.\r\nTerima kasih.\r\n\r\nWassalamu\'alaikum', 'sent', '2026-02-05 16:13:15', NULL),
(12, 5, 4, 'WhatsApp', '6281365905047', 'Assalamu\'alaikum,\r\n\r\nReminder: Jadwal Pelatihan Belajar Personal Branding UMKM\r\n\r\nTanggal   : 10 Februari 2026\r\nJam       : 08:00 - 12:45 WIB\r\nLokasi    : Gedung Conference\r\n\r\nKeterangan:\r\nRuangan 2\r\n\r\nMohon untuk hadir tepat waktu.\r\nTerima kasih.\r\n\r\nWassalamu\'alaikum', 'sent', '2026-02-05 16:13:35', NULL),
(13, 7, 4, 'WhatsApp', '6285157558469', 'Assalamu\'alaikum,\r\n\r\nReminder: Jadwal Pelatihan Belajar Personal Branding UMKM\r\n\r\nTanggal   : 10 Februari 2026\r\nJam       : 08:00 - 12:45 WIB\r\nLokasi    : Gedung Conference\r\n\r\nKeterangan:\r\nRuangan 2\r\n\r\nMohon untuk hadir tepat waktu.\r\nTerima kasih.\r\n\r\nWassalamu\'alaikum', 'sent', '2026-02-05 16:18:16', NULL),
(14, 5, 2, 'WhatsApp', '6281365905047', 'Assalamu\'alaikum,\r\n\r\nReminder: Jadwal Pelatihan Persiapan Jadi UMKM 2026 Batch 1\r\n\r\nTanggal   : 18 Februari 2026\r\nJam       : 07:30 - 11:30 WIB\r\nLokasi    : UPTD BALATKOP\r\n\r\nKeterangan:\r\nini adalah keterangan\r\n\r\nMohon untuk hadir tepat waktu.\r\nTerima kasih.\r\n\r\nWassalamu\'alaikum', 'sent', '2026-02-05 17:08:25', NULL),
(15, 4, 2, 'WhatsApp', '6285157558469', 'Assalamu\'alaikum,\r\n\r\nReminder: Jadwal Pelatihan Persiapan Jadi UMKM 2026 Batch 1\r\n\r\nTanggal   : 18 Februari 2026\r\nJam       : 07:30 - 11:13 WIB\r\nLokasi    : UPTD BALATKOP\r\n\r\nKeterangan:\r\nini adalah keterangan\r\n\r\nMohon untuk hadir tepat waktu.\r\nTerima kasih.\r\n\r\nWassalamu\'alaikum', 'sent', '2026-02-08 20:33:53', NULL),
(16, 4, 2, 'WhatsApp', '6285157558469', 'Assalamu\'alaikum,\r\n\r\nReminder: Jadwal Pelatihan Persiapan Jadi UMKM 2026 Batch 1\r\n\r\nTanggal   : 18 Februari 2026\r\nJam        : 07:30 - 11:13 WIB\r\nLokasi    : Ruangan C\r\n\r\nKeterangan:\r\nini adalah keterangan\r\n\r\nMohon untuk hadir tepat waktu.\r\nTerima kasih.\r\n\r\nWassalamu\'alaikum', 'sent', '2026-02-08 20:37:51', NULL),
(17, 4, 2, 'WhatsApp', '6285157558469', 'Assalamu\'alaikum,\r\n\r\nReminder: Jadwal Pelatihan Persiapan Jadi UMKM 2026 Batch 1\r\n\r\nTanggal   : 18 Februari 2026\r\nJam        : 07:30 - 11:13 WIB\r\nLokasi    : Ruangan C\r\n\r\nKeterangan:\r\nini adalah keterangan\r\n\r\nMohon untuk hadir tepat waktu.\r\nTerima kasih.\r\n\r\nWassalamu\'alaikum', 'sent', '2026-02-08 20:38:25', NULL),
(18, 4, 2, 'WhatsApp', '6285157558469', 'Assalamu\'alaikum,\r\n\r\nReminder: Jadwal Pelatihan Persiapan Jadi UMKM 2026 Batch 1\r\n\r\nTanggal   : 18 Februari 2026\r\nJam        : 07:30 - 11:13 WIB\r\nRuangan    : Ruangan C\r\n\r\nKeterangan:\r\nini adalah keterangan\r\n\r\nMohon untuk hadir tepat waktu.\r\nTerima kasih.\r\n\r\nWassalamu\'alaikum', 'sent', '2026-02-08 20:38:56', NULL),
(19, 5, 1, 'WhatsApp', '6281365905047', 'Assalamu\'alaikum,\r\n\r\nReminder: Jadwal Pelatihan Pelatihan PHP Dasar\r\n\r\nTanggal   : 18 Februari 2026\r\nJam        : 07:30 - 11:13 WIB\r\nRuangan    : Ruangan B\r\n\r\nKeterangan:\r\nini adalah keterangan\r\n\r\nMohon untuk hadir tepat waktu.\r\nTerima kasih.\r\n\r\nWassalamu\'alaikum', 'sent', '2026-02-11 22:21:53', NULL),
(20, 5, 1, 'WhatsApp', '6281365905047', 'Assalamu\'alaikum,\r\n\r\nReminder: Jadwal Pelatihan Pelatihan PHP Dasar\r\n\r\nTanggal   : 18 Februari 2026\r\nJam        : 07:30 - 11:13 WIB\r\nRuangan    : Ruangan B\r\n\r\nKeterangan:\r\nini adalah keterangan\r\n\r\nMohon untuk hadir tepat waktu.\r\nTerima kasih.\r\n\r\nWassalamu\'alaikum', 'sent', '2026-02-13 19:17:11', NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `pelatihan`
--

CREATE TABLE `pelatihan` (
  `id` int(11) NOT NULL,
  `nama_pelatihan` varchar(100) NOT NULL,
  `penyelenggara` varchar(100) DEFAULT NULL,
  `kuota` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `instruktur_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pelatihan`
--

INSERT INTO `pelatihan` (`id`, `nama_pelatihan`, `penyelenggara`, `kuota`, `created_at`, `instruktur_id`) VALUES
(1, 'Pelatihan PHP Dasar', 'Internal', 20, '2026-01-29 07:49:16', 7),
(2, 'Pelatihan Web Modern', 'Dicoding', 20, '2026-01-29 07:49:16', 10),
(3, 'Pelatihan UI/UX', 'Googles', 20, '2026-01-29 07:49:16', 7),
(5, 'Persiapan Jadi UMKM 2026 Batch 1', 'UPTD BALATKOP', 20, '2026-02-01 18:11:55', 8),
(6, 'Belajar Personal Branding UMKM', 'UPTD BALATKOP', 3, '2026-02-05 10:23:40', 8),
(7, 'Pelatihan Baru', 'UPTD', 35, '2026-03-09 15:55:07', 8);

-- --------------------------------------------------------

--
-- Struktur dari tabel `peserta`
--

CREATE TABLE `peserta` (
  `id` int(11) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `telepon` varchar(20) DEFAULT NULL,
  `tanggal_lahir` date DEFAULT NULL,
  `file_ktp` varchar(255) DEFAULT NULL,
  `file_kk` varchar(255) DEFAULT NULL,
  `pelatihan_id` int(11) DEFAULT NULL,
  `status` enum('Aktif','Tidak Aktif') DEFAULT 'Aktif',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `peserta`
--

INSERT INTO `peserta` (`id`, `nama_lengkap`, `email`, `telepon`, `tanggal_lahir`, `file_ktp`, `file_kk`, `pelatihan_id`, `status`, `created_at`, `user_id`) VALUES
(4, 'Ode Rintik Segara', 'oderintik999@gmail.com', '6285157558469', '1998-01-14', NULL, NULL, NULL, 'Aktif', '2026-02-01 17:51:44', 6),
(5, 'zikri', 'zikri@gmail.com', '081365905047', '2003-07-12', NULL, NULL, NULL, 'Aktif', '2026-02-02 18:09:51', 11),
(6, 'Wawan', 'wawan@gmail.com', '6289514732426', '2000-02-12', NULL, NULL, NULL, 'Aktif', '2026-02-05 07:48:45', 12),
(7, 'Setia Budi', 'setiabd@gmail.com', '085157558469', '1999-04-23', NULL, NULL, NULL, 'Aktif', '2026-02-05 16:16:16', 13),
(8, 'Kontol Raja Iblis', 'rajainlis@gmail.com', '085612345678', '1999-07-08', 'ktp_peserta8_1772981766.png', 'kk_peserta8_1772981766.png', NULL, 'Aktif', '2026-03-07 21:31:22', 16);

-- --------------------------------------------------------

--
-- Struktur dari tabel `riwayat_peserta`
--

CREATE TABLE `riwayat_peserta` (
  `id` int(11) NOT NULL,
  `peserta_id` int(11) NOT NULL,
  `pelatihan_id` int(11) NOT NULL,
  `tanggal_ikut` date DEFAULT NULL,
  `status` enum('Lulus','Tidak Lulus','Proses') DEFAULT 'Proses',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `riwayat_peserta`
--

INSERT INTO `riwayat_peserta` (`id`, `peserta_id`, `pelatihan_id`, `tanggal_ikut`, `status`, `created_at`) VALUES
(1, 4, 1, '2026-02-02', 'Lulus', '2026-02-01 17:51:44'),
(2, 4, 5, '2026-02-02', 'Lulus', '2026-02-01 18:25:15'),
(3, 4, 2, '2026-02-02', 'Tidak Lulus', '2026-02-02 11:35:47'),
(4, 5, 5, '2026-02-03', 'Proses', '2026-02-02 18:09:51'),
(5, 5, 1, '2026-02-03', 'Proses', '2026-02-02 18:09:53'),
(6, 5, 2, '2026-02-03', 'Lulus', '2026-02-02 18:09:56'),
(7, 5, 3, '2026-02-03', 'Proses', '2026-02-02 18:09:58'),
(8, 6, 5, '2026-02-05', 'Proses', '2026-02-05 08:00:07'),
(9, 5, 6, '2026-02-05', 'Proses', '2026-02-05 10:24:05'),
(10, 7, 6, '2026-02-05', 'Lulus', '2026-02-05 16:16:52'),
(11, 4, 6, '2026-02-09', 'Proses', '2026-02-08 20:31:21'),
(12, 4, 3, '2026-02-12', 'Proses', '2026-02-11 20:54:57');

-- --------------------------------------------------------

--
-- Struktur dari tabel `ruangan`
--

CREATE TABLE `ruangan` (
  `id` int(11) NOT NULL,
  `nama_ruangan` varchar(100) NOT NULL,
  `kapasitas` int(11) NOT NULL,
  `keterangan` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `ruangan`
--

INSERT INTO `ruangan` (`id`, `nama_ruangan`, `kapasitas`, `keterangan`, `created_at`) VALUES
(2, 'Ruangan A', 40, 'ini adalah keterangan', '2026-02-08 19:41:49'),
(3, 'Ruangan B', 40, 'ini adalah keterangan', '2026-02-08 19:41:59'),
(4, 'Ruangan C', 40, 'ini adalah keterangan', '2026-02-08 19:42:10');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('Peserta','Admin','Instruktur','Pimpinan') NOT NULL DEFAULT 'Peserta',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `nama_lengkap`, `username`, `password`, `role`, `created_at`, `updated_at`) VALUES
(1, 'Administrator Sistem', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', '2026-01-29 07:18:25', '2026-01-29 07:18:25'),
(2, 'Budi Santoso', 'peserta1', '$2y$10$H9z4R3z5FJwUjJm0XlMZlO2zWq5r3mB0Yy7n7X0z0PpQ1nK1UQ2Ue', 'Peserta', '2026-01-29 07:18:25', '2026-01-29 07:18:25'),
(3, 'Siti Aminah', 'peserta2', '$2y$10$Q6mC5m3H8v2zQzH6R2ZK6u3v3zK6p4xQJ1b8nFZK8FJH6k7P8Y8lC', 'Peserta', '2026-01-29 07:18:25', '2026-01-29 07:18:25'),
(4, 'Andi Pratama', 'instruktur1', '$2y$10$M1U6A1m4H7v1S8U2Z5QZQp6C9K4Z2D3R6Z6L6P3F7K7T1Q2H5U8Y', 'Instruktur', '2026-01-29 07:18:25', '2026-01-29 07:18:25'),
(6, 'aaaaa', 'aaaaa', '$2y$10$UgqSQ8uS.jdXUWysddBydunYusl5u.Oji0zQVUKxyCapJOswQJGV2', 'Peserta', '2026-01-29 08:11:59', '2026-01-29 08:11:59'),
(7, 'Budi Santoso', 'budi01011', '$2y$10$BwoWqAim.cNUCqiY6yi2geslLH72krS9fZo9BuLwLqD4RStawrIg2', 'Instruktur', '2026-02-01 17:20:25', '2026-02-01 17:23:57'),
(8, 'Joko Wididiw', 'jokowid', '$2y$10$se1yKUmf/lU3YJsxPmDC6.DaQBwZW6KD8ydJST4MT8ZJ16Ur5W0bW', 'Instruktur', '2026-02-01 18:18:35', '2026-02-01 18:18:35'),
(9, 'Pimpinan', 'pimpinan1', '$2y$10$OpNylT22GyIgLlv223FbD.16DO4FvoUVYlDOF79hyxO3vZJMhT5MW', 'Pimpinan', '2026-02-02 10:24:07', '2026-02-08 20:25:48'),
(10, 'Andi Pratama Putra', 'instruktur2', '$2y$10$kcviiM9/D5K/7dAn8FRFZ.Bs.bBV9Zrxn1o66JCFCnO7KCATErpIu', 'Instruktur', '2026-02-02 11:36:58', '2026-02-02 11:37:11'),
(11, 'zikri', 'zikri', '$2y$10$kCDBzz/ZnbGBlNv2ddRhLOB8a5CBQESkt8xBoq74dVlco2V9Pat8W', 'Peserta', '2026-02-02 16:49:32', '2026-02-02 16:49:32'),
(12, 'Wawan', 'wawan', '$2y$10$VcpPCWzBemhIzrPVrElNOuo8t1pa3Iow31LbK9y2oVYmA/9ClmVcC', 'Peserta', '2026-02-05 06:25:40', '2026-02-05 06:25:40'),
(13, 'Setia Budi', 'setia', '$2y$10$U.JnBb0E1Ja0KbY6eXUgweJYV0Dohts8L/J0bAAA2BU9WUJvvCJF.', 'Peserta', '2026-02-05 16:16:02', '2026-02-05 16:16:02'),
(14, 'wwwww', 'wwww', '$2y$10$Sjdlq/GZ4YpGubdJEuhF9.RCfM1wp0Q6qx6//SDyUmwb8BiF.0wLa', 'Peserta', '2026-02-11 21:20:13', '2026-02-11 21:20:13'),
(15, 'Raja Iblis', 'rajaiblis', '$2y$10$Yim0DKhUbeQzZVgVRrlCy.O9EXYbZKaDIUWCa7yWRjnMsLw9sSMIy', 'Pimpinan', '2026-02-18 16:52:05', '2026-02-18 16:52:18'),
(16, 'Kontol Raja Iblis', 'tol123', '$2y$10$/dgbEiMeHhHkcvNpElcXvu20wBKFhbICJwzCVEuMBhKYnhUoYzQ1m', 'Peserta', '2026-03-07 21:31:15', '2026-03-10 20:19:04'),
(17, 'Alip Gacor', 'alipppp', '$2y$10$FhDLEvn4ZEy8guB.4eeNXuTVPvHB6il9msQ.F6.Bj5cM.7LA6HlO2', 'Peserta', '2026-03-09 15:52:52', '2026-03-09 15:52:52');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `evaluasi_pelatihan`
--
ALTER TABLE `evaluasi_pelatihan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `peserta_id` (`peserta_id`),
  ADD KEY `pelatihan_id` (`pelatihan_id`),
  ADD KEY `instruktur_id` (`instruktur_id`);

--
-- Indeks untuk tabel `jadwal`
--
ALTER TABLE `jadwal`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_jadwal_pelatihan` (`pelatihan_id`),
  ADD KEY `fk_jadwal_ruangan` (`ruangan_id`);

--
-- Indeks untuk tabel `notifikasi_log`
--
ALTER TABLE `notifikasi_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `peserta_id` (`peserta_id`),
  ADD KEY `jadwal_id` (`jadwal_id`);

--
-- Indeks untuk tabel `pelatihan`
--
ALTER TABLE `pelatihan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_pelatihan_instruktur` (`instruktur_id`);

--
-- Indeks untuk tabel `peserta`
--
ALTER TABLE `peserta`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_peserta_pelatihan` (`pelatihan_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `riwayat_peserta`
--
ALTER TABLE `riwayat_peserta`
  ADD PRIMARY KEY (`id`),
  ADD KEY `peserta_id` (`peserta_id`),
  ADD KEY `pelatihan_id` (`pelatihan_id`);

--
-- Indeks untuk tabel `ruangan`
--
ALTER TABLE `ruangan`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `evaluasi_pelatihan`
--
ALTER TABLE `evaluasi_pelatihan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `jadwal`
--
ALTER TABLE `jadwal`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `notifikasi_log`
--
ALTER TABLE `notifikasi_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT untuk tabel `pelatihan`
--
ALTER TABLE `pelatihan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT untuk tabel `peserta`
--
ALTER TABLE `peserta`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT untuk tabel `riwayat_peserta`
--
ALTER TABLE `riwayat_peserta`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT untuk tabel `ruangan`
--
ALTER TABLE `ruangan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `evaluasi_pelatihan`
--
ALTER TABLE `evaluasi_pelatihan`
  ADD CONSTRAINT `evaluasi_pelatihan_ibfk_1` FOREIGN KEY (`peserta_id`) REFERENCES `peserta` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `evaluasi_pelatihan_ibfk_2` FOREIGN KEY (`pelatihan_id`) REFERENCES `pelatihan` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `evaluasi_pelatihan_ibfk_3` FOREIGN KEY (`instruktur_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `jadwal`
--
ALTER TABLE `jadwal`
  ADD CONSTRAINT `fk_jadwal_pelatihan` FOREIGN KEY (`pelatihan_id`) REFERENCES `pelatihan` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_jadwal_ruangan` FOREIGN KEY (`ruangan_id`) REFERENCES `ruangan` (`id`) ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `notifikasi_log`
--
ALTER TABLE `notifikasi_log`
  ADD CONSTRAINT `notifikasi_log_ibfk_1` FOREIGN KEY (`peserta_id`) REFERENCES `peserta` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notifikasi_log_ibfk_2` FOREIGN KEY (`jadwal_id`) REFERENCES `jadwal` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `pelatihan`
--
ALTER TABLE `pelatihan`
  ADD CONSTRAINT `fk_pelatihan_instruktur` FOREIGN KEY (`instruktur_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `peserta`
--
ALTER TABLE `peserta`
  ADD CONSTRAINT `fk_peserta_pelatihan` FOREIGN KEY (`pelatihan_id`) REFERENCES `pelatihan` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `peserta_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `riwayat_peserta`
--
ALTER TABLE `riwayat_peserta`
  ADD CONSTRAINT `riwayat_peserta_ibfk_1` FOREIGN KEY (`peserta_id`) REFERENCES `peserta` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `riwayat_peserta_ibfk_2` FOREIGN KEY (`pelatihan_id`) REFERENCES `pelatihan` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
