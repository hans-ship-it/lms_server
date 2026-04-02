-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Mar 26, 2026 at 11:14 AM
-- Server version: 8.0.30
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sman4_lms`
--

-- --------------------------------------------------------

--
-- Table structure for table `assignments`
--

CREATE TABLE `assignments` (
  `id` int NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text,
  `deadline` datetime DEFAULT NULL,
  `teacher_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `attachment_path` varchar(255) DEFAULT NULL,
  `class_id` int DEFAULT NULL,
  `status` enum('active','archived') DEFAULT 'active',
  `assignment_type` enum('tugas','absensi') NOT NULL DEFAULT 'tugas',
  `meeting_number` int DEFAULT NULL,
  `teacher_class_id` int DEFAULT NULL,
  `jam_start` time DEFAULT NULL,
  `jam_end` time DEFAULT NULL,
  `subject_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `assignments`
--

INSERT INTO `assignments` (`id`, `title`, `description`, `deadline`, `teacher_id`, `created_at`, `attachment_path`, `class_id`, `status`, `assignment_type`, `meeting_number`, `teacher_class_id`, `jam_start`, `jam_end`, `subject_id`) VALUES
(69, '1', '1', '2026-03-24 22:34:00', 6722, '2026-03-24 14:32:07', NULL, NULL, 'active', 'tugas', NULL, 45, NULL, NULL, 3);

-- --------------------------------------------------------

--
-- Table structure for table `assignment_attachments`
--

CREATE TABLE `assignment_attachments` (
  `id` int NOT NULL,
  `assignment_id` int NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `assignment_classes`
--

CREATE TABLE `assignment_classes` (
  `id` int NOT NULL,
  `assignment_id` int NOT NULL,
  `class_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `assignment_classes`
--

INSERT INTO `assignment_classes` (`id`, `assignment_id`, `class_id`) VALUES
(66, 69, 1);

-- --------------------------------------------------------

--
-- Table structure for table `classes`
--

CREATE TABLE `classes` (
  `id` int NOT NULL,
  `name` varchar(50) NOT NULL,
  `grade_level` int NOT NULL,
  `major` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `classes`
--

INSERT INTO `classes` (`id`, `name`, `grade_level`, `major`) VALUES
(1, 'X-1', 10, NULL),
(2, 'X-2', 10, NULL),
(3, 'X-3', 10, NULL),
(4, 'X-4', 10, NULL),
(5, 'X-5', 10, NULL),
(6, 'X-6', 10, NULL),
(7, 'X-7', 10, NULL),
(8, 'X-8', 10, NULL),
(9, 'X-9', 10, NULL),
(10, 'X-10', 10, NULL),
(11, 'X-11', 10, NULL),
(12, 'XI-1', 11, NULL),
(13, 'XI-2', 11, NULL),
(14, 'XI-3', 11, NULL),
(15, 'XI-4', 11, NULL),
(16, 'XI-5', 11, NULL),
(17, 'XI-6', 11, NULL),
(18, 'XI-7', 11, NULL),
(19, 'XI-8', 11, NULL),
(20, 'XI-9', 11, NULL),
(21, 'XI-10', 11, NULL),
(22, 'XI-11', 11, NULL),
(33, 'XI-12', 11, NULL),
(34, 'XII-1', 12, NULL),
(35, 'XII-2', 12, NULL),
(36, 'XII-3', 12, NULL),
(37, 'XII-4', 12, NULL),
(38, 'XII-5', 12, NULL),
(39, 'XII-6', 12, NULL),
(40, 'XII-7', 12, NULL),
(41, 'XII-8', 12, NULL),
(42, 'XII-9', 12, NULL),
(43, 'XII-10', 12, NULL),
(44, 'XII-11', 12, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `class_members`
--

CREATE TABLE `class_members` (
  `id` int NOT NULL,
  `teacher_class_id` int NOT NULL,
  `student_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `class_members`
--

INSERT INTO `class_members` (`id`, `teacher_class_id`, `student_id`, `created_at`) VALUES
(2, 35, 2481, '2026-03-11 23:58:55'),
(3, 35, 3332, '2026-03-12 00:00:17'),
(6, 35, 2872, '2026-03-12 00:01:02'),
(7, 35, 2574, '2026-03-12 00:01:02'),
(8, 35, 2492, '2026-03-12 00:18:21');

-- --------------------------------------------------------

--
-- Table structure for table `e_counseling`
--

CREATE TABLE `e_counseling` (
  `id` int NOT NULL,
  `nama` varchar(100) DEFAULT NULL,
  `kelas` varchar(50) DEFAULT NULL,
  `kategori` varchar(100) NOT NULL,
  `pesan` text NOT NULL,
  `status` enum('pending','read','replied') DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `e_counseling`
--

INSERT INTO `e_counseling` (`id`, `nama`, `kelas`, `kategori`, `pesan`, `status`, `created_at`) VALUES
(1, '11', '1', 'Konseling Karir / Penjurusan', 'wert', 'read', '2026-03-11 13:35:00');

-- --------------------------------------------------------

--
-- Table structure for table `grades`
--

CREATE TABLE `grades` (
  `id` int NOT NULL,
  `student_id` int NOT NULL,
  `subject_id` int NOT NULL,
  `teacher_id` int NOT NULL,
  `class_id` int NOT NULL,
  `semester` enum('Ganjil','Genap') NOT NULL DEFAULT 'Ganjil',
  `tahun_ajaran` varchar(9) NOT NULL DEFAULT '2024/2025',
  `tugas1` decimal(5,2) DEFAULT NULL,
  `tugas2` decimal(5,2) DEFAULT NULL,
  `tugas3` decimal(5,2) DEFAULT NULL,
  `uts` decimal(5,2) DEFAULT NULL,
  `uas` decimal(5,2) DEFAULT NULL,
  `nilai_akhir` decimal(5,2) GENERATED ALWAYS AS (ifnull(((((((ifnull(`tugas1`,0) + ifnull(`tugas2`,0)) + ifnull(`tugas3`,0)) / 3) * 0.4) + (ifnull(`uts`,0) * 0.3)) + (ifnull(`uas`,0) * 0.3)),0)) STORED,
  `predikat` varchar(1) GENERATED ALWAYS AS ((case when (`nilai_akhir` >= 85) then _utf8mb4'A' when (`nilai_akhir` >= 75) then _utf8mb4'B' when (`nilai_akhir` >= 65) then _utf8mb4'C' when (`nilai_akhir` >= 55) then _utf8mb4'D' else _utf8mb4'E' end)) STORED,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `materials`
--

CREATE TABLE `materials` (
  `id` int NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text,
  `type` enum('video','pdf','word','ppt','link') NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `teacher_id` int NOT NULL,
  `teacher_class_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `class_id` int DEFAULT NULL,
  `subject_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `materials`
--

INSERT INTO `materials` (`id`, `title`, `description`, `type`, `file_path`, `teacher_id`, `teacher_class_id`, `created_at`, `class_id`, `subject_id`) VALUES
(40, '1', '1', 'link', 'https://us05web.zoom.us/j/84044467394?pwd=Zzb80cCafO3pnbbMG7ZpobFW1TUspG.1', 6722, 45, '2026-03-24 14:31:58', 1, 3);

-- --------------------------------------------------------

--
-- Table structure for table `meet_links`
--

CREATE TABLE `meet_links` (
  `id` int NOT NULL,
  `teacher_id` int NOT NULL,
  `teacher_class_id` int NOT NULL,
  `class_id` int NOT NULL,
  `subject_id` int NOT NULL,
  `meet_link` varchar(255) NOT NULL,
  `start_time` datetime DEFAULT NULL,
  `end_time` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `meet_links`
--

INSERT INTO `meet_links` (`id`, `teacher_id`, `teacher_class_id`, `class_id`, `subject_id`, `meet_link`, `start_time`, `end_time`, `created_at`) VALUES
(5, 3331, 30, 1, 3, 'https://meet.google.com/qqw-tikn-uie', NULL, NULL, '2026-03-05 10:48:17'),
(13, 6722, 45, 1, 3, 'https://meet.google.com/hie-yspf-csb', '2026-03-25 07:30:00', '2026-03-25 09:00:00', '2026-03-24 22:32:21');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `news`
--

CREATE TABLE `news` (
  `id` int NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `author_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `news`
--

INSERT INTO `news` (`id`, `title`, `content`, `image`, `author_id`, `created_at`) VALUES
(9, '1', '1', '1774109518_0_full-moon-forest-night-dark-starry-sky-5k-8k-7952x5304-1684.jpg', 1, '2026-03-21 16:11:58'),
(10, '2', '2', '1774109530_0_lavender-fields-5120x2880-21314.jpg', 1, '2026-03-21 16:12:10');

-- --------------------------------------------------------

--
-- Table structure for table `news_images`
--

CREATE TABLE `news_images` (
  `id` int NOT NULL,
  `news_id` int NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `news_images`
--

INSERT INTO `news_images` (`id`, `news_id`, `image_path`, `created_at`) VALUES
(8, 9, '1774109518_0_full-moon-forest-night-dark-starry-sky-5k-8k-7952x5304-1684.jpg', '2026-03-21 16:11:58'),
(9, 10, '1774109530_0_lavender-fields-5120x2880-21314.jpg', '2026-03-21 16:12:10');

-- --------------------------------------------------------

--
-- Table structure for table `pengaduan`
--

CREATE TABLE `pengaduan` (
  `id` int NOT NULL,
  `nama_siswa` varchar(100) DEFAULT NULL,
  `kelas` varchar(50) DEFAULT NULL,
  `kategori` enum('Kendala Belajar','Bullying','Fasilitas','Konsultasi Karir','Lainnya') NOT NULL DEFAULT 'Lainnya',
  `pesan` text NOT NULL,
  `status` enum('Pending','Diproses','Selesai') NOT NULL DEFAULT 'Pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `pengaduan`
--

INSERT INTO `pengaduan` (`id`, `nama_siswa`, `kelas`, `kategori`, `pesan`, `status`, `created_at`) VALUES
(2, '1', '1', 'Fasilitas', '1', 'Selesai', '2026-03-21 21:23:22'),
(3, '2', '2', 'Bullying', '11', 'Pending', '2026-03-21 21:31:57');

-- --------------------------------------------------------

--
-- Table structure for table `schedules`
--

CREATE TABLE `schedules` (
  `id` int NOT NULL,
  `hari` varchar(20) DEFAULT NULL,
  `jam_ke` varchar(10) DEFAULT NULL,
  `waktu` varchar(30) DEFAULT NULL,
  `kelas` varchar(50) DEFAULT NULL,
  `mata_pelajaran` varchar(100) DEFAULT NULL,
  `nama_guru` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `schedules`
--

INSERT INTO `schedules` (`id`, `hari`, `jam_ke`, `waktu`, `kelas`, `mata_pelajaran`, `nama_guru`, `created_at`) VALUES
(1, 'SENIN', '1', '07:30 - 08:15', 'X 1', 'UPACARA BENDERA', '', '2026-03-21 21:14:16'),
(2, 'SENIN', '1', '07:30 - 08:15', 'X 2', 'UPACARA BENDERA', '', '2026-03-21 21:14:16'),
(3, 'SENIN', '1', '07:30 - 08:15', 'X 3', 'UPACARA BENDERA', '', '2026-03-21 21:14:16'),
(4, 'SENIN', '1', '07:30 - 08:15', 'X 4', 'UPACARA BENDERA', '', '2026-03-21 21:14:16'),
(5, 'SENIN', '1', '07:30 - 08:15', 'X 5', 'UPACARA BENDERA', '', '2026-03-21 21:14:16'),
(6, 'SENIN', '1', '07:30 - 08:15', 'X 6', 'UPACARA BENDERA', '', '2026-03-21 21:14:16'),
(7, 'SENIN', '1', '07:30 - 08:15', 'X 7', 'UPACARA BENDERA', '', '2026-03-21 21:14:16'),
(8, 'SENIN', '1', '07:30 - 08:15', 'X 8', 'UPACARA BENDERA', '', '2026-03-21 21:14:16'),
(9, 'SENIN', '1', '07:30 - 08:15', 'X 9', 'UPACARA BENDERA', '', '2026-03-21 21:14:16'),
(10, 'SENIN', '1', '07:30 - 08:15', 'X 10', 'UPACARA BENDERA', '', '2026-03-21 21:14:16'),
(11, 'SENIN', '1', '07:30 - 08:15', 'X 11', 'UPACARA BENDERA', '', '2026-03-21 21:14:16'),
(12, 'SENIN', '1', '07:30 - 08:15', 'X 12', 'UPACARA BENDERA', '', '2026-03-21 21:14:16'),
(13, 'SENIN', '1', '07:30 - 08:15', 'XI 1', 'UPACARA BENDERA', '', '2026-03-21 21:14:16'),
(14, 'SENIN', '1', '07:30 - 08:15', 'XI 2', 'UPACARA BENDERA', '', '2026-03-21 21:14:16'),
(15, 'SENIN', '1', '07:30 - 08:15', 'XI 3', 'UPACARA BENDERA', '', '2026-03-21 21:14:16'),
(16, 'SENIN', '1', '07:30 - 08:15', 'XI 4', 'UPACARA BENDERA', '', '2026-03-21 21:14:16'),
(17, 'SENIN', '1', '07:30 - 08:15', 'XI 5', 'UPACARA BENDERA', '', '2026-03-21 21:14:16'),
(18, 'SENIN', '1', '07:30 - 08:15', 'XI 6', 'UPACARA BENDERA', '', '2026-03-21 21:14:16'),
(19, 'SENIN', '1', '07:30 - 08:15', 'XI 7', 'UPACARA BENDERA', '', '2026-03-21 21:14:16'),
(20, 'SENIN', '1', '07:30 - 08:15', 'XI 8', 'UPACARA BENDERA', '', '2026-03-21 21:14:16'),
(21, 'SENIN', '1', '07:30 - 08:15', 'XI 9', 'UPACARA BENDERA', '', '2026-03-21 21:14:16'),
(22, 'SENIN', '1', '07:30 - 08:15', 'XI 10', 'UPACARA BENDERA', '', '2026-03-21 21:14:16'),
(23, 'SENIN', '1', '07:30 - 08:15', 'XI 11', 'UPACARA BENDERA', '', '2026-03-21 21:14:16'),
(24, 'SENIN', '1', '07:30 - 08:15', 'XI 12', 'UPACARA BENDERA', '', '2026-03-21 21:14:16'),
(25, 'SENIN', '1', '07:30 - 08:15', 'XII 1', 'UPACARA BENDERA', '', '2026-03-21 21:14:16'),
(26, 'SENIN', '1', '07:30 - 08:15', 'XII 2', 'UPACARA BENDERA', '', '2026-03-21 21:14:16'),
(27, 'SENIN', '1', '07:30 - 08:15', 'XII 3', 'UPACARA BENDERA', '', '2026-03-21 21:14:16'),
(28, 'SENIN', '1', '07:30 - 08:15', 'XII 4', 'UPACARA BENDERA', '', '2026-03-21 21:14:16'),
(29, 'SENIN', '1', '07:30 - 08:15', 'XII 5', 'UPACARA BENDERA', '', '2026-03-21 21:14:16'),
(30, 'SENIN', '1', '07:30 - 08:15', 'XII 6', 'UPACARA BENDERA', '', '2026-03-21 21:14:16'),
(31, 'SENIN', '1', '07:30 - 08:15', 'XII 7', 'UPACARA BENDERA', '', '2026-03-21 21:14:16'),
(32, 'SENIN', '1', '07:30 - 08:15', 'XII 8', 'UPACARA BENDERA', '', '2026-03-21 21:14:16'),
(33, 'SENIN', '1', '07:30 - 08:15', 'XII 9', 'UPACARA BENDERA', '', '2026-03-21 21:14:16'),
(34, 'SENIN', '1', '07:30 - 08:15', 'XII 10', 'UPACARA BENDERA', '', '2026-03-21 21:14:16'),
(35, 'SENIN', '1', '07:30 - 08:15', 'XII 11', 'UPACARA BENDERA', '', '2026-03-21 21:14:16'),
(36, 'SENIN', '1', '07:30 - 08:15', 'XII 12', 'UPACARA BENDERA', '', '2026-03-21 21:14:16'),
(37, 'SENIN', '2', '08:15 - 09:00', 'X 1', 'PJOK', 'ASRIANTO, S.Or., Gr.', '2026-03-21 21:14:16'),
(38, 'SENIN', '2', '08:15 - 09:00', 'X 2', 'Geografi', 'SANTI KUSUMA DEWI, S.Pd.', '2026-03-21 21:14:16'),
(39, 'SENIN', '2', '08:15 - 09:00', 'X 3', 'Sosiologi', 'ADE HANDAYANI, S.Sos., M.Pd., Gr.', '2026-03-21 21:14:16'),
(40, 'SENIN', '2', '08:15 - 09:00', 'X 4', 'Sejarah', 'ALI TAHIR, S.Pd.', '2026-03-21 21:14:16'),
(41, 'SENIN', '2', '08:15 - 09:00', 'X 5', 'Ekonomi', 'A. VEBHY INDAH BELLIANTHARI, S.Pd.', '2026-03-21 21:14:16'),
(42, 'SENIN', '2', '08:15 - 09:00', 'X 6', 'Bahasa Indonesia', 'NURFATWA, S.Pd.', '2026-03-21 21:14:16'),
(43, 'SENIN', '2', '08:15 - 09:00', 'X 7', 'Matematika', 'SYAMSIAR, S.Pd.', '2026-03-21 21:14:16'),
(44, 'SENIN', '2', '08:15 - 09:00', 'X 8', 'Biologi', 'HAMIDATUSSIFAH, S.Si.', '2026-03-21 21:14:16'),
(45, 'SENIN', '2', '08:15 - 09:00', 'X 9', 'Ekonomi', 'Dr. Hj. ROSMAWATI, SE.,MA.', '2026-03-21 21:14:16'),
(46, 'SENIN', '2', '08:15 - 09:00', 'X 10', 'Pendidikan Agama', 'MUH. ARSYAD HY., S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(47, 'SENIN', '2', '08:15 - 09:00', 'X 11', 'Fisika', 'MUH. TRI PRASETIA NUA, S.Pd., Gr.', '2026-03-21 21:14:16'),
(48, 'SENIN', '2', '08:15 - 09:00', 'XI 1', 'Fisika (Pilihan)', 'EVI YULIATI, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(49, 'SENIN', '2', '08:15 - 09:00', 'XI 2', 'PJOK', 'DWI SETIAWATI B., S.Pd.', '2026-03-21 21:14:16'),
(50, 'SENIN', '2', '08:15 - 09:00', 'XI 3', 'Kimia (Pilihan)', 'SAPRUDDIN, S.Pd.', '2026-03-21 21:14:16'),
(51, 'SENIN', '2', '08:15 - 09:00', 'XI 4', 'Kimia (Pilihan)', 'MARTHINA SATTU ARI, M.Pd.', '2026-03-21 21:14:16'),
(52, 'SENIN', '2', '08:15 - 09:00', 'XI 5', 'Informatika (Pilihan)', 'RIZA RISKY YULIANTI, S.Pd., Gr.', '2026-03-21 21:14:16'),
(53, 'SENIN', '2', '08:15 - 09:00', 'XI 6', 'Kimia (Pilihan)', 'ISMAIL LA HASSE, S.Si., S.Pd.', '2026-03-21 21:14:16'),
(54, 'SENIN', '2', '08:15 - 09:00', 'XI 7', 'Biologi (Pilihan)', 'MINDJAH WATY M., S.Si.', '2026-03-21 21:14:16'),
(55, 'SENIN', '2', '08:15 - 09:00', 'XI 8', 'Bahasa Indonesia', 'FITRIANI RAHMAT, S.Pd.', '2026-03-21 21:14:16'),
(56, 'SENIN', '2', '08:15 - 09:00', 'XI 9', 'Matematika', 'HARTIWI PRADIKTA, S.Pd., Gr.', '2026-03-21 21:14:16'),
(57, 'SENIN', '2', '08:15 - 09:00', 'XI 10', 'Sosiologi (Pilihan)', 'SRI UTAMI WULANSARI, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(58, 'SENIN', '2', '08:15 - 09:00', 'XI 11', 'Biologi (Pilihan)', 'NURSANTY, S.Pd.', '2026-03-21 21:14:16'),
(59, 'SENIN', '2', '08:15 - 09:00', 'XI 12', 'Matematika', 'NURAFNI PEBRIANTY, S.Pd., Gr.', '2026-03-21 21:14:16'),
(60, 'SENIN', '2', '08:15 - 09:00', 'XII 1', 'Matematika', 'MASNIATI, S.Pd.', '2026-03-21 21:14:16'),
(61, 'SENIN', '2', '08:15 - 09:00', 'XII 2', 'Matematika', 'LUDIHARISTIADI, S.Pd.', '2026-03-21 21:14:16'),
(62, 'SENIN', '2', '08:15 - 09:00', 'XII 3', 'Pendidikan Agama', 'SITTI NAFI, S.Pd.I.', '2026-03-21 21:14:16'),
(63, 'SENIN', '2', '08:15 - 09:00', 'XII 4', 'Bahasa Indonesia', 'FITRIYANI, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(64, 'SENIN', '2', '08:15 - 09:00', 'XII 5', 'Sejarah', 'DAMAYANTI, S.Pd.', '2026-03-21 21:14:16'),
(65, 'SENIN', '2', '08:15 - 09:00', 'XII 6', 'Pendidikan Pancasila', 'EKA RUSNAENI, S.Pd., Gr.', '2026-03-21 21:14:16'),
(66, 'SENIN', '2', '08:15 - 09:00', 'XII 7', 'Bahasa Inggris', 'AFIAH SAKIAH, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(67, 'SENIN', '2', '08:15 - 09:00', 'XII 8', 'Bahasa Inggris', 'MUHAMMAD SALEH, S.Pd., MM.', '2026-03-21 21:14:16'),
(68, 'SENIN', '2', '08:15 - 09:00', 'XII 9', 'Pendidikan Agama', 'YANTI NOVIANTI NAWAWI, S.Pd.I.', '2026-03-21 21:14:16'),
(69, 'SENIN', '2', '08:15 - 09:00', 'XII 10', 'PJOK', 'ABD. KAMAL, S.Pd.', '2026-03-21 21:14:16'),
(70, 'SENIN', '2', '08:15 - 09:00', 'XII 11', 'Bahasa Indonesia', 'ST. ROSMAWATI M., S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(71, 'SENIN', '2', '08:15 - 09:00', 'XII 12', 'PJOK', 'MUH. GILANG FAJARI, S.Pd.', '2026-03-21 21:14:16'),
(72, 'SENIN', '3', '09:00 - 09:45', 'X 1', 'PJOK', 'ASRIANTO, S.Or., Gr.', '2026-03-21 21:14:16'),
(73, 'SENIN', '3', '09:00 - 09:45', 'X 2', 'Geografi', 'SANTI KUSUMA DEWI, S.Pd.', '2026-03-21 21:14:16'),
(74, 'SENIN', '3', '09:00 - 09:45', 'X 3', 'Sosiologi', 'ADE HANDAYANI, S.Sos., M.Pd., Gr.', '2026-03-21 21:14:16'),
(75, 'SENIN', '3', '09:00 - 09:45', 'X 4', 'Sejarah', 'ALI TAHIR, S.Pd.', '2026-03-21 21:14:16'),
(76, 'SENIN', '3', '09:00 - 09:45', 'X 5', 'Ekonomi', 'A. VEBHY INDAH BELLIANTHARI, S.Pd.', '2026-03-21 21:14:16'),
(77, 'SENIN', '3', '09:00 - 09:45', 'X 6', 'Bahasa Indonesia', 'NURFATWA, S.Pd.', '2026-03-21 21:14:16'),
(78, 'SENIN', '3', '09:00 - 09:45', 'X 7', 'Matematika', 'SYAMSIAR, S.Pd.', '2026-03-21 21:14:16'),
(79, 'SENIN', '3', '09:00 - 09:45', 'X 8', 'Biologi', 'HAMIDATUSSIFAH, S.Si.', '2026-03-21 21:14:16'),
(80, 'SENIN', '3', '09:00 - 09:45', 'X 9', 'Ekonomi', 'Dr. Hj. ROSMAWATI, SE.,MA.', '2026-03-21 21:14:16'),
(81, 'SENIN', '3', '09:00 - 09:45', 'X 10', 'Pendidikan Agama', 'MUH. ARSYAD HY., S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(82, 'SENIN', '3', '09:00 - 09:45', 'X 11', 'Fisika', 'MUH. TRI PRASETIA NUA, S.Pd., Gr.', '2026-03-21 21:14:16'),
(83, 'SENIN', '3', '09:00 - 09:45', 'XI 1', 'Fisika (Pilihan)', 'EVI YULIATI, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(84, 'SENIN', '3', '09:00 - 09:45', 'XI 2', 'PJOK', 'DWI SETIAWATI B., S.Pd.', '2026-03-21 21:14:16'),
(85, 'SENIN', '3', '09:00 - 09:45', 'XI 3', 'Kimia (Pilihan)', 'SAPRUDDIN, S.Pd.', '2026-03-21 21:14:16'),
(86, 'SENIN', '3', '09:00 - 09:45', 'XI 4', 'Kimia (Pilihan)', 'MARTHINA SATTU ARI, M.Pd.', '2026-03-21 21:14:16'),
(87, 'SENIN', '3', '09:00 - 09:45', 'XI 5', 'Informatika (Pilihan)', 'RIZA RISKY YULIANTI, S.Pd., Gr.', '2026-03-21 21:14:16'),
(88, 'SENIN', '3', '09:00 - 09:45', 'XI 6', 'Kimia (Pilihan)', 'ISMAIL LA HASSE, S.Si., S.Pd.', '2026-03-21 21:14:16'),
(89, 'SENIN', '3', '09:00 - 09:45', 'XI 7', 'Biologi (Pilihan)', 'MINDJAH WATY M., S.Si.', '2026-03-21 21:14:16'),
(90, 'SENIN', '3', '09:00 - 09:45', 'XI 8', 'Bahasa Indonesia', 'FITRIANI RAHMAT, S.Pd.', '2026-03-21 21:14:16'),
(91, 'SENIN', '3', '09:00 - 09:45', 'XI 9', 'Matematika', 'HARTIWI PRADIKTA, S.Pd., Gr.', '2026-03-21 21:14:16'),
(92, 'SENIN', '3', '09:00 - 09:45', 'XI 10', 'Sosiologi (Pilihan)', 'SRI UTAMI WULANSARI, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(93, 'SENIN', '3', '09:00 - 09:45', 'XI 11', 'Biologi (Pilihan)', 'NURSANTY, S.Pd.', '2026-03-21 21:14:16'),
(94, 'SENIN', '3', '09:00 - 09:45', 'XI 12', 'Matematika', 'NURAFNI PEBRIANTY, S.Pd., Gr.', '2026-03-21 21:14:16'),
(95, 'SENIN', '3', '09:00 - 09:45', 'XII 1', 'Matematika', 'MASNIATI, S.Pd.', '2026-03-21 21:14:16'),
(96, 'SENIN', '3', '09:00 - 09:45', 'XII 2', 'Matematika', 'LUDIHARISTIADI, S.Pd.', '2026-03-21 21:14:16'),
(97, 'SENIN', '3', '09:00 - 09:45', 'XII 3', 'Pendidikan Agama', 'SITTI NAFI, S.Pd.I.', '2026-03-21 21:14:16'),
(98, 'SENIN', '3', '09:00 - 09:45', 'XII 4', 'Bahasa Indonesia', 'FITRIYANI, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(99, 'SENIN', '3', '09:00 - 09:45', 'XII 5', 'Sejarah', 'DAMAYANTI, S.Pd.', '2026-03-21 21:14:16'),
(100, 'SENIN', '3', '09:00 - 09:45', 'XII 6', 'Pendidikan Pancasila', 'EKA RUSNAENI, S.Pd., Gr.', '2026-03-21 21:14:16'),
(101, 'SENIN', '3', '09:00 - 09:45', 'XII 7', 'Bahasa Inggris', 'AFIAH SAKIAH, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(102, 'SENIN', '3', '09:00 - 09:45', 'XII 8', 'Bahasa Inggris', 'MUHAMMAD SALEH, S.Pd., MM.', '2026-03-21 21:14:16'),
(103, 'SENIN', '3', '09:00 - 09:45', 'XII 9', 'Pendidikan Agama', 'YANTI NOVIANTI NAWAWI, S.Pd.I.', '2026-03-21 21:14:16'),
(104, 'SENIN', '3', '09:00 - 09:45', 'XII 10', 'PJOK', 'ABD. KAMAL, S.Pd.', '2026-03-21 21:14:16'),
(105, 'SENIN', '3', '09:00 - 09:45', 'XII 11', 'Bahasa Indonesia', 'ST. ROSMAWATI M., S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(106, 'SENIN', '3', '09:00 - 09:45', 'XII 12', 'PJOK', 'MUH. GILANG FAJARI, S.Pd.', '2026-03-21 21:14:16'),
(107, 'SENIN', '4', '09:45 - 10:30', 'X 1', 'PJOK', 'ASRIANTO, S.Or., Gr.', '2026-03-21 21:14:16'),
(108, 'SENIN', '4', '09:45 - 10:30', 'X 2', 'Geografi', 'SANTI KUSUMA DEWI, S.Pd.', '2026-03-21 21:14:16'),
(109, 'SENIN', '4', '09:45 - 10:30', 'X 3', 'Sosiologi', 'ADE HANDAYANI, S.Sos., M.Pd., Gr.', '2026-03-21 21:14:16'),
(110, 'SENIN', '4', '09:45 - 10:30', 'X 4', 'Sejarah', 'ALI TAHIR, S.Pd.', '2026-03-21 21:14:16'),
(111, 'SENIN', '4', '09:45 - 10:30', 'X 5', 'Ekonomi', 'A. VEBHY INDAH BELLIANTHARI, S.Pd.', '2026-03-21 21:14:16'),
(112, 'SENIN', '4', '09:45 - 10:30', 'X 6', 'Matematika', 'SYAMSIAR, S.Pd.', '2026-03-21 21:14:16'),
(113, 'SENIN', '4', '09:45 - 10:30', 'X 7', 'Kimia', 'LENNI BAKRI, S.Pd.', '2026-03-21 21:14:16'),
(114, 'SENIN', '4', '09:45 - 10:30', 'X 8', 'Biologi', 'HAMIDATUSSIFAH, S.Si.', '2026-03-21 21:14:16'),
(115, 'SENIN', '4', '09:45 - 10:30', 'X 9', 'Ekonomi', 'Dr. Hj. ROSMAWATI, SE.,MA.', '2026-03-21 21:14:16'),
(116, 'SENIN', '4', '09:45 - 10:30', 'X 10', 'Pendidikan Agama', 'MUH. ARSYAD HY., S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(117, 'SENIN', '4', '09:45 - 10:30', 'X 11', 'Fisika', 'MUH. TRI PRASETIA NUA, S.Pd., Gr.', '2026-03-21 21:14:16'),
(118, 'SENIN', '4', '09:45 - 10:30', 'XI 1', 'Fisika (Pilihan)', 'EVI YULIATI, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(119, 'SENIN', '4', '09:45 - 10:30', 'XI 2', 'PJOK', 'DWI SETIAWATI B., S.Pd.', '2026-03-21 21:14:16'),
(120, 'SENIN', '4', '09:45 - 10:30', 'XI 3', 'Kimia (Pilihan)', 'SAPRUDDIN, S.Pd.', '2026-03-21 21:14:16'),
(121, 'SENIN', '4', '09:45 - 10:30', 'XI 4', 'Kimia (Pilihan)', 'MARTHINA SATTU ARI, M.Pd.', '2026-03-21 21:14:16'),
(122, 'SENIN', '4', '09:45 - 10:30', 'XI 5', 'Informatika (Pilihan)', 'RIZA RISKY YULIANTI, S.Pd., Gr.', '2026-03-21 21:14:16'),
(123, 'SENIN', '4', '09:45 - 10:30', 'XI 6', 'Kimia (Pilihan)', 'ISMAIL LA HASSE, S.Si., S.Pd.', '2026-03-21 21:14:16'),
(124, 'SENIN', '4', '09:45 - 10:30', 'XI 7', 'Biologi (Pilihan)', 'MINDJAH WATY M., S.Si.', '2026-03-21 21:14:16'),
(125, 'SENIN', '4', '09:45 - 10:30', 'XI 8', 'Pendidikan Agama', 'ACHMAD MUFTI AL GALZA S., S.Pd.I.', '2026-03-21 21:14:16'),
(126, 'SENIN', '4', '09:45 - 10:30', 'XI 9', 'Matematika', 'HARTIWI PRADIKTA, S.Pd., Gr.', '2026-03-21 21:14:16'),
(127, 'SENIN', '4', '09:45 - 10:30', 'XI 10', 'Sosiologi (Pilihan)', 'SRI UTAMI WULANSARI, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(128, 'SENIN', '4', '09:45 - 10:30', 'XI 11', 'Biologi (Pilihan)', 'NURSANTY, S.Pd.', '2026-03-21 21:14:16'),
(129, 'SENIN', '4', '09:45 - 10:30', 'XI 12', 'Bahasa Indonesia', 'FITRIANI RAHMAT, S.Pd.', '2026-03-21 21:14:16'),
(130, 'SENIN', '4', '09:45 - 10:30', 'XII 1', 'Seni Budaya', 'ST. NADIA KHAERATY, S.Pd.', '2026-03-21 21:14:16'),
(131, 'SENIN', '4', '09:45 - 10:30', 'XII 2', 'Matematika', 'LUDIHARISTIADI, S.Pd.', '2026-03-21 21:14:16'),
(132, 'SENIN', '4', '09:45 - 10:30', 'XII 3', 'Pendidikan Agama', 'SITTI NAFI, S.Pd.I.', '2026-03-21 21:14:16'),
(133, 'SENIN', '4', '09:45 - 10:30', 'XII 4', 'Bahasa Indonesia', 'FITRIYANI, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(134, 'SENIN', '4', '09:45 - 10:30', 'XII 5', 'Matematika', 'LILIES RATNA RIYANTINI, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(135, 'SENIN', '4', '09:45 - 10:30', 'XII 6', 'Matematika', 'MASNIATI, S.Pd.', '2026-03-21 21:14:16'),
(136, 'SENIN', '4', '09:45 - 10:30', 'XII 7', 'Bahasa Inggris', 'AFIAH SAKIAH, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(137, 'SENIN', '4', '09:45 - 10:30', 'XII 8', 'Bahasa Inggris', 'MUHAMMAD SALEH, S.Pd., MM.', '2026-03-21 21:14:16'),
(138, 'SENIN', '4', '09:45 - 10:30', 'XII 9', 'Prakarya', 'SARWANA, S.Pd.', '2026-03-21 21:14:16'),
(139, 'SENIN', '4', '09:45 - 10:30', 'XII 10', 'PJOK', 'ABD. KAMAL, S.Pd.', '2026-03-21 21:14:16'),
(140, 'SENIN', '4', '09:45 - 10:30', 'XII 11', 'Pendidikan Agama', 'YANTI NOVIANTI NAWAWI, S.Pd.I.', '2026-03-21 21:14:16'),
(141, 'SENIN', '4', '09:45 - 10:30', 'XII 12', 'PJOK', 'MUH. GILANG FAJARI, S.Pd.', '2026-03-21 21:14:16'),
(142, 'SENIN', '5', '10:45 - 11:30', 'X 1', 'Sejarah', 'ERNIATI, S.Pd.', '2026-03-21 21:14:16'),
(143, 'SENIN', '5', '10:45 - 11:30', 'X 2', 'PJOK', 'ASRIANTO, S.Or., Gr.', '2026-03-21 21:14:16'),
(144, 'SENIN', '5', '10:45 - 11:30', 'X 3', 'Bahasa Inggris', 'RISTA JUNIANTI SAHAR, SS.', '2026-03-21 21:14:16'),
(145, 'SENIN', '5', '10:45 - 11:30', 'X 4', 'Sosiologi', 'ADE HANDAYANI, S.Sos., M.Pd., Gr.', '2026-03-21 21:14:16'),
(146, 'SENIN', '5', '10:45 - 11:30', 'X 5', 'Biologi', 'NURSANTY, S.Pd.', '2026-03-21 21:14:16'),
(147, 'SENIN', '5', '10:45 - 11:30', 'X 6', 'Matematika', 'SYAMSIAR, S.Pd.', '2026-03-21 21:14:16'),
(148, 'SENIN', '5', '10:45 - 11:30', 'X 7', 'Pendidikan Agama', 'ACHMAD MUFTI AL GALZA S., S.Pd.I.', '2026-03-21 21:14:16'),
(149, 'SENIN', '5', '10:45 - 11:30', 'X 8', 'PJOK', 'MUH. GILANG FAJARI, S.Pd.', '2026-03-21 21:14:16'),
(150, 'SENIN', '5', '10:45 - 11:30', 'X 9', 'Kimia', 'LENNI BAKRI, S.Pd.', '2026-03-21 21:14:16'),
(151, 'SENIN', '5', '10:45 - 11:30', 'X 10', 'Fisika', 'MUH. TRI PRASETIA NUA, S.Pd., Gr.', '2026-03-21 21:14:16'),
(152, 'SENIN', '5', '10:45 - 11:30', 'X 11', 'Informatika', 'RIZA RISKY YULIANTI, S.Pd., Gr.', '2026-03-21 21:14:16'),
(153, 'SENIN', '5', '10:45 - 11:30', 'XI 1', 'Informatika (Pilihan)', 'MUHAMMAD IDRIS RIFAI SARRO, S.Si.', '2026-03-21 21:14:16'),
(154, 'SENIN', '5', '10:45 - 11:30', 'XI 2', 'Kimia (Pilihan)', 'SAPRUDDIN, S.Pd.', '2026-03-21 21:14:16'),
(155, 'SENIN', '5', '10:45 - 11:30', 'XI 3', 'Biologi (Pilihan)', 'CATHARINA NOVENI DWIDJADJANTI, S.Pd.', '2026-03-21 21:14:16'),
(156, 'SENIN', '5', '10:45 - 11:30', 'XI 4', 'Bahasa Inggris (Pilihan)', 'AFIAH SAKIAH, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(157, 'SENIN', '5', '10:45 - 11:30', 'XI 5', 'Bahasa Inggris (Pilihan)', 'RANDIKA DHANI SYAPUTRA, S.Pd.', '2026-03-21 21:14:16'),
(158, 'SENIN', '5', '10:45 - 11:30', 'XI 6', 'Sosiologi (Pilihan)', 'SRI UTAMI WULANSARI, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(159, 'SENIN', '5', '10:45 - 11:30', 'XI 7', 'Kimia (Pilihan)', 'ISMAIL LA HASSE, S.Si., S.Pd.', '2026-03-21 21:14:16'),
(160, 'SENIN', '5', '10:45 - 11:30', 'XI 8', 'PJOK', 'DWI SETIAWATI B., S.Pd.', '2026-03-21 21:14:16'),
(161, 'SENIN', '5', '10:45 - 11:30', 'XI 9', 'Bahasa Indonesia', 'HASNA BAKRI, S.Pd.', '2026-03-21 21:14:16'),
(162, 'SENIN', '5', '10:45 - 11:30', 'XI 10', 'Pendidikan Agama', 'YANTI NOVIANTI NAWAWI, S.Pd.I.', '2026-03-21 21:14:16'),
(163, 'SENIN', '5', '10:45 - 11:30', 'XI 11', 'Bahasa Indonesia', 'NURFATWA, S.Pd.', '2026-03-21 21:14:16'),
(164, 'SENIN', '5', '10:45 - 11:30', 'XI 12', 'Bahasa Indonesia (Pilihan)', 'FITRIANI RAHMAT, S.Pd.', '2026-03-21 21:14:16'),
(165, 'SENIN', '5', '10:45 - 11:30', 'XII 1', 'Biologi (Pilihan)', 'Drs. MUHAMMAD HAMZAH, MM.', '2026-03-21 21:14:16'),
(166, 'SENIN', '5', '10:45 - 11:30', 'XII 2', 'Fisika (Pilihan)', 'SAPPEWATI, S.Pd.', '2026-03-21 21:14:16'),
(167, 'SENIN', '5', '10:45 - 11:30', 'XII 3', 'Ekonomi (Pilihan)', 'Dr. Hj. ROSMAWATI, SE.,MA.', '2026-03-21 21:14:16'),
(168, 'SENIN', '5', '10:45 - 11:30', 'XII 4', 'Geografi (Pilihan)', 'Drs. BAHARUDDIN', '2026-03-21 21:14:16'),
(169, 'SENIN', '5', '10:45 - 11:30', 'XII 5', 'Ekonomi (Pilihan)', 'MUHAMMAD YUSUF, S.Pd.', '2026-03-21 21:14:16'),
(170, 'SENIN', '5', '10:45 - 11:30', 'XII 6', 'Matematika (Pilihan)', 'HAMKA, S.Si., S.Pd., Gr.', '2026-03-21 21:14:16'),
(171, 'SENIN', '5', '10:45 - 11:30', 'XII 7', 'Biologi (Pilihan)', 'HAMIDATUSSIFAH, S.Si.', '2026-03-21 21:14:16'),
(172, 'SENIN', '5', '10:45 - 11:30', 'XII 8', 'Ekonomi (Pilihan)', 'A. VEBHY INDAH BELLIANTHARI, S.Pd.', '2026-03-21 21:14:16'),
(173, 'SENIN', '5', '10:45 - 11:30', 'XII 9', 'Bahasa Indonesia (Pilihan)', 'NUR AKBAR, S.Pd.', '2026-03-21 21:14:16'),
(174, 'SENIN', '5', '10:45 - 11:30', 'XII 10', 'Bahasa Inggris (Pilihan)', 'MUHAMMAD SALEH, S.Pd., MM.', '2026-03-21 21:14:16'),
(175, 'SENIN', '5', '10:45 - 11:30', 'XII 11', 'Kimia (Pilihan)', 'MARTHINA SATTU ARI, M.Pd.', '2026-03-21 21:14:16'),
(176, 'SENIN', '5', '10:45 - 11:30', 'XII 12', 'Fisika (Pilihan)', 'EVI YULIATI, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(177, 'SENIN', '6', '11:30 - 12:15', 'X 1', 'Sejarah', 'ERNIATI, S.Pd.', '2026-03-21 21:14:16'),
(178, 'SENIN', '6', '11:30 - 12:15', 'X 2', 'PJOK', 'ASRIANTO, S.Or., Gr.', '2026-03-21 21:14:16'),
(179, 'SENIN', '6', '11:30 - 12:15', 'X 3', 'Bahasa Inggris', 'RISTA JUNIANTI SAHAR, SS.', '2026-03-21 21:14:16'),
(180, 'SENIN', '6', '11:30 - 12:15', 'X 4', 'Sosiologi', 'ADE HANDAYANI, S.Sos., M.Pd., Gr.', '2026-03-21 21:14:16'),
(181, 'SENIN', '6', '11:30 - 12:15', 'X 5', 'Biologi', 'NURSANTY, S.Pd.', '2026-03-21 21:14:16'),
(182, 'SENIN', '6', '11:30 - 12:15', 'X 6', 'Seni Budaya', 'ASWAN ANWAR, S.Pd.', '2026-03-21 21:14:16'),
(183, 'SENIN', '6', '11:30 - 12:15', 'X 7', 'Pendidikan Agama', 'ACHMAD MUFTI AL GALZA S., S.Pd.I.', '2026-03-21 21:14:16'),
(184, 'SENIN', '6', '11:30 - 12:15', 'X 8', 'PJOK', 'MUH. GILANG FAJARI, S.Pd.', '2026-03-21 21:14:16'),
(185, 'SENIN', '6', '11:30 - 12:15', 'X 9', 'Kimia', 'LENNI BAKRI, S.Pd.', '2026-03-21 21:14:16'),
(186, 'SENIN', '6', '11:30 - 12:15', 'X 10', 'Fisika', 'MUH. TRI PRASETIA NUA, S.Pd., Gr.', '2026-03-21 21:14:16'),
(187, 'SENIN', '6', '11:30 - 12:15', 'X 11', 'Informatika', 'RIZA RISKY YULIANTI, S.Pd., Gr.', '2026-03-21 21:14:16'),
(188, 'SENIN', '6', '11:30 - 12:15', 'XI 1', 'Informatika (Pilihan)', 'MUHAMMAD IDRIS RIFAI SARRO, S.Si.', '2026-03-21 21:14:16'),
(189, 'SENIN', '6', '11:30 - 12:15', 'XI 2', 'Kimia (Pilihan)', 'SAPRUDDIN, S.Pd.', '2026-03-21 21:14:16'),
(190, 'SENIN', '6', '11:30 - 12:15', 'XI 3', 'Biologi (Pilihan)', 'CATHARINA NOVENI DWIDJADJANTI, S.Pd.', '2026-03-21 21:14:16'),
(191, 'SENIN', '6', '11:30 - 12:15', 'XI 4', 'Bahasa Inggris (Pilihan)', 'AFIAH SAKIAH, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(192, 'SENIN', '6', '11:30 - 12:15', 'XI 5', 'Bahasa Inggris (Pilihan)', 'RANDIKA DHANI SYAPUTRA, S.Pd.', '2026-03-21 21:14:16'),
(193, 'SENIN', '6', '11:30 - 12:15', 'XI 6', 'Sosiologi (Pilihan)', 'SRI UTAMI WULANSARI, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(194, 'SENIN', '6', '11:30 - 12:15', 'XI 7', 'Kimia (Pilihan)', 'ISMAIL LA HASSE, S.Si., S.Pd.', '2026-03-21 21:14:16'),
(195, 'SENIN', '6', '11:30 - 12:15', 'XI 8', 'PJOK', 'DWI SETIAWATI B., S.Pd.', '2026-03-21 21:14:16'),
(196, 'SENIN', '6', '11:30 - 12:15', 'XI 9', 'Bahasa Indonesia', 'HASNA BAKRI, S.Pd.', '2026-03-21 21:14:16'),
(197, 'SENIN', '6', '11:30 - 12:15', 'XI 10', 'Pendidikan Agama', 'YANTI NOVIANTI NAWAWI, S.Pd.I.', '2026-03-21 21:14:16'),
(198, 'SENIN', '6', '11:30 - 12:15', 'XI 11', 'Bahasa Indonesia', 'NURFATWA, S.Pd.', '2026-03-21 21:14:16'),
(199, 'SENIN', '6', '11:30 - 12:15', 'XI 12', 'Bahasa Indonesia (Pilihan)', 'FITRIANI RAHMAT, S.Pd.', '2026-03-21 21:14:16'),
(200, 'SENIN', '6', '11:30 - 12:15', 'XII 1', 'Biologi (Pilihan)', 'Drs. MUHAMMAD HAMZAH, MM.', '2026-03-21 21:14:16'),
(201, 'SENIN', '6', '11:30 - 12:15', 'XII 2', 'Fisika (Pilihan)', 'SAPPEWATI, S.Pd.', '2026-03-21 21:14:16'),
(202, 'SENIN', '6', '11:30 - 12:15', 'XII 3', 'Ekonomi (Pilihan)', 'Dr. Hj. ROSMAWATI, SE.,MA.', '2026-03-21 21:14:16'),
(203, 'SENIN', '6', '11:30 - 12:15', 'XII 4', 'Geografi (Pilihan)', 'Drs. BAHARUDDIN', '2026-03-21 21:14:16'),
(204, 'SENIN', '6', '11:30 - 12:15', 'XII 5', 'Ekonomi (Pilihan)', 'MUHAMMAD YUSUF, S.Pd.', '2026-03-21 21:14:16'),
(205, 'SENIN', '6', '11:30 - 12:15', 'XII 6', 'Matematika (Pilihan)', 'HAMKA, S.Si., S.Pd., Gr.', '2026-03-21 21:14:16'),
(206, 'SENIN', '6', '11:30 - 12:15', 'XII 7', 'Biologi (Pilihan)', 'HAMIDATUSSIFAH, S.Si.', '2026-03-21 21:14:16'),
(207, 'SENIN', '6', '11:30 - 12:15', 'XII 8', 'Ekonomi (Pilihan)', 'A. VEBHY INDAH BELLIANTHARI, S.Pd.', '2026-03-21 21:14:16'),
(208, 'SENIN', '6', '11:30 - 12:15', 'XII 9', 'Bahasa Indonesia (Pilihan)', 'NUR AKBAR, S.Pd.', '2026-03-21 21:14:16'),
(209, 'SENIN', '6', '11:30 - 12:15', 'XII 10', 'Bahasa Inggris (Pilihan)', 'MUHAMMAD SALEH, S.Pd., MM.', '2026-03-21 21:14:16'),
(210, 'SENIN', '6', '11:30 - 12:15', 'XII 11', 'Kimia (Pilihan)', 'MARTHINA SATTU ARI, M.Pd.', '2026-03-21 21:14:16'),
(211, 'SENIN', '6', '11:30 - 12:15', 'XII 12', 'Fisika (Pilihan)', 'EVI YULIATI, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(212, 'SENIN', '7', '12:50 - 13:30', 'X 1', 'Sejarah', 'ERNIATI, S.Pd.', '2026-03-21 21:14:16'),
(213, 'SENIN', '7', '12:50 - 13:30', 'X 2', 'PJOK', 'ASRIANTO, S.Or., Gr.', '2026-03-21 21:14:16'),
(214, 'SENIN', '7', '12:50 - 13:30', 'X 3', 'Bahasa Inggris', 'RISTA JUNIANTI SAHAR, SS.', '2026-03-21 21:14:16'),
(215, 'SENIN', '7', '12:50 - 13:30', 'X 4', 'Sosiologi', 'ADE HANDAYANI, S.Sos., M.Pd., Gr.', '2026-03-21 21:14:16'),
(216, 'SENIN', '7', '12:50 - 13:30', 'X 5', 'Biologi', 'NURSANTY, S.Pd.', '2026-03-21 21:14:16'),
(217, 'SENIN', '7', '12:50 - 13:30', 'X 6', 'Seni Budaya', 'ASWAN ANWAR, S.Pd.', '2026-03-21 21:14:16'),
(218, 'SENIN', '7', '12:50 - 13:30', 'X 7', 'Pendidikan Agama', 'ACHMAD MUFTI AL GALZA S., S.Pd.I.', '2026-03-21 21:14:16'),
(219, 'SENIN', '7', '12:50 - 13:30', 'X 8', 'PJOK', 'MUH. GILANG FAJARI, S.Pd.', '2026-03-21 21:14:16'),
(220, 'SENIN', '7', '12:50 - 13:30', 'X 9', 'Kimia', 'LENNI BAKRI, S.Pd.', '2026-03-21 21:14:16'),
(221, 'SENIN', '7', '12:50 - 13:30', 'X 10', 'Fisika', 'MUH. TRI PRASETIA NUA, S.Pd., Gr.', '2026-03-21 21:14:16'),
(222, 'SENIN', '7', '12:50 - 13:30', 'X 11', 'Bahasa Inggris', 'ARIFIN, S.Pd.', '2026-03-21 21:14:16'),
(223, 'SENIN', '7', '12:50 - 13:30', 'XI 1', 'Informatika (Pilihan)', 'MUHAMMAD IDRIS RIFAI SARRO, S.Si.', '2026-03-21 21:14:16'),
(224, 'SENIN', '7', '12:50 - 13:30', 'XI 2', 'Kimia (Pilihan)', 'SAPRUDDIN, S.Pd.', '2026-03-21 21:14:16'),
(225, 'SENIN', '7', '12:50 - 13:30', 'XI 3', 'Biologi (Pilihan)', 'CATHARINA NOVENI DWIDJADJANTI, S.Pd.', '2026-03-21 21:14:16'),
(226, 'SENIN', '7', '12:50 - 13:30', 'XI 4', 'Bahasa Inggris (Pilihan)', 'AFIAH SAKIAH, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(227, 'SENIN', '7', '12:50 - 13:30', 'XI 5', 'Bahasa Inggris (Pilihan)', 'RANDIKA DHANI SYAPUTRA, S.Pd.', '2026-03-21 21:14:16'),
(228, 'SENIN', '7', '12:50 - 13:30', 'XI 6', 'Sosiologi (Pilihan)', 'SRI UTAMI WULANSARI, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(229, 'SENIN', '7', '12:50 - 13:30', 'XI 7', 'Kimia (Pilihan)', 'ISMAIL LA HASSE, S.Si., S.Pd.', '2026-03-21 21:14:16'),
(230, 'SENIN', '7', '12:50 - 13:30', 'XI 8', 'PJOK', 'DWI SETIAWATI B., S.Pd.', '2026-03-21 21:14:16'),
(231, 'SENIN', '7', '12:50 - 13:30', 'XI 9', 'Pendidikan Pancasila', 'YEKTIE NUR PRAYOGA LM, S.Pd., Gr.', '2026-03-21 21:14:16'),
(232, 'SENIN', '7', '12:50 - 13:30', 'XI 10', 'Pendidikan Agama', 'YANTI NOVIANTI NAWAWI, S.Pd.I.', '2026-03-21 21:14:16'),
(233, 'SENIN', '7', '12:50 - 13:30', 'XI 11', 'Sejarah', 'ALI TAHIR, S.Pd.', '2026-03-21 21:14:16'),
(234, 'SENIN', '7', '12:50 - 13:30', 'XI 12', 'Bahasa Indonesia (Pilihan)', 'FITRIANI RAHMAT, S.Pd.', '2026-03-21 21:14:16'),
(235, 'SENIN', '7', '12:50 - 13:30', 'XII 1', 'Biologi (Pilihan)', 'Drs. MUHAMMAD HAMZAH, MM.', '2026-03-21 21:14:16'),
(236, 'SENIN', '7', '12:50 - 13:30', 'XII 2', 'Fisika (Pilihan)', 'SAPPEWATI, S.Pd.', '2026-03-21 21:14:16'),
(237, 'SENIN', '7', '12:50 - 13:30', 'XII 3', 'Ekonomi (Pilihan)', 'Dr. Hj. ROSMAWATI, SE.,MA.', '2026-03-21 21:14:16'),
(238, 'SENIN', '7', '12:50 - 13:30', 'XII 4', 'Geografi (Pilihan)', 'Drs. BAHARUDDIN', '2026-03-21 21:14:16'),
(239, 'SENIN', '7', '12:50 - 13:30', 'XII 5', 'Ekonomi (Pilihan)', 'MUHAMMAD YUSUF, S.Pd.', '2026-03-21 21:14:16'),
(240, 'SENIN', '7', '12:50 - 13:30', 'XII 6', 'Matematika (Pilihan)', 'HAMKA, S.Si., S.Pd., Gr.', '2026-03-21 21:14:16'),
(241, 'SENIN', '7', '12:50 - 13:30', 'XII 7', 'Biologi (Pilihan)', 'HAMIDATUSSIFAH, S.Si.', '2026-03-21 21:14:16'),
(242, 'SENIN', '7', '12:50 - 13:30', 'XII 8', 'Ekonomi (Pilihan)', 'A. VEBHY INDAH BELLIANTHARI, S.Pd.', '2026-03-21 21:14:16'),
(243, 'SENIN', '7', '12:50 - 13:30', 'XII 9', 'Bahasa Indonesia (Pilihan)', 'NUR AKBAR, S.Pd.', '2026-03-21 21:14:16'),
(244, 'SENIN', '7', '12:50 - 13:30', 'XII 10', 'Bahasa Inggris (Pilihan)', 'MUHAMMAD SALEH, S.Pd., MM.', '2026-03-21 21:14:16'),
(245, 'SENIN', '7', '12:50 - 13:30', 'XII 11', 'Kimia (Pilihan)', 'MARTHINA SATTU ARI, M.Pd.', '2026-03-21 21:14:16'),
(246, 'SENIN', '7', '12:50 - 13:30', 'XII 12', 'Fisika (Pilihan)', 'EVI YULIATI, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(247, 'SENIN', '8', '13:30 - 14:10', 'X 1', 'Biologi', 'MINDJAH WATY M., S.Si.', '2026-03-21 21:14:16'),
(248, 'SENIN', '8', '13:30 - 14:10', 'X 2', 'Sejarah', 'ERNIATI, S.Pd.', '2026-03-21 21:14:16'),
(249, 'SENIN', '8', '13:30 - 14:10', 'X 3', 'Fisika', 'EVI YULIATI, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(250, 'SENIN', '8', '13:30 - 14:10', 'X 4', 'Bahasa Inggris', 'RISTA JUNIANTI SAHAR, SS.', '2026-03-21 21:14:16'),
(251, 'SENIN', '8', '13:30 - 14:10', 'X 5', 'Sosiologi', 'ADE HANDAYANI, S.Sos., M.Pd., Gr.', '2026-03-21 21:14:16'),
(252, 'SENIN', '8', '13:30 - 14:10', 'X 6', 'Ekonomi', 'A. VEBHY INDAH BELLIANTHARI, S.Pd.', '2026-03-21 21:14:16'),
(253, 'SENIN', '8', '13:30 - 14:10', 'X 7', 'Fisika', 'MUH. TRI PRASETIA NUA, S.Pd., Gr.', '2026-03-21 21:14:16'),
(254, 'SENIN', '8', '13:30 - 14:10', 'X 8', 'Bahasa Inggris', 'ARIFIN, S.Pd.', '2026-03-21 21:14:16'),
(255, 'SENIN', '8', '13:30 - 14:10', 'X 9', 'Biologi', 'NURSANTY, S.Pd.', '2026-03-21 21:14:16'),
(256, 'SENIN', '8', '13:30 - 14:10', 'X 10', 'Geografi', 'SANTI KUSUMA DEWI, S.Pd.', '2026-03-21 21:14:16'),
(257, 'SENIN', '8', '13:30 - 14:10', 'X 11', 'Kimia', 'LENNI BAKRI, S.Pd.', '2026-03-21 21:14:16'),
(258, 'SENIN', '8', '13:30 - 14:10', 'XI 1', 'Bahasa Inggris (Pilihan)', 'RANDIKA DHANI SYAPUTRA, S.Pd.', '2026-03-21 21:14:16'),
(259, 'SENIN', '8', '13:30 - 14:10', 'XI 2', 'Matematika', 'NUR QALBI RUSDIN, S.Pd.,M.Pd.', '2026-03-21 21:14:16'),
(260, 'SENIN', '8', '13:30 - 14:10', 'XI 3', 'Pendidikan Agama', 'YANTI NOVIANTI NAWAWI, S.Pd.I.', '2026-03-21 21:14:16'),
(261, 'SENIN', '8', '13:30 - 14:10', 'XI 4', 'Biologi (Pilihan)', 'CATHARINA NOVENI DWIDJADJANTI, S.Pd.', '2026-03-21 21:14:16'),
(262, 'SENIN', '8', '13:30 - 14:10', 'XI 5', 'Kimia (Pilihan)', 'MARTHINA SATTU ARI, M.Pd.', '2026-03-21 21:14:16'),
(263, 'SENIN', '8', '13:30 - 14:10', 'XI 6', 'Bahasa Indonesia (Pilihan)', 'NUR AKBAR, S.Pd.', '2026-03-21 21:14:16'),
(264, 'SENIN', '8', '13:30 - 14:10', 'XI 7', 'Sosiologi (Pilihan)', 'SRI UTAMI WULANSARI, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(265, 'SENIN', '8', '13:30 - 14:10', 'XI 8', 'Geografi (Pilihan)', 'Drs. BAHARUDDIN', '2026-03-21 21:14:16'),
(266, 'SENIN', '8', '13:30 - 14:10', 'XI 9', 'Pendidikan Pancasila', 'YEKTIE NUR PRAYOGA LM, S.Pd., Gr.', '2026-03-21 21:14:16'),
(267, 'SENIN', '8', '13:30 - 14:10', 'XI 10', 'Geografi (Pilihan)', 'HALIFUDDIN, S.Pd.', '2026-03-21 21:14:16'),
(268, 'SENIN', '8', '13:30 - 14:10', 'XI 11', 'Sejarah', 'ALI TAHIR, S.Pd.', '2026-03-21 21:14:16'),
(269, 'SENIN', '8', '13:30 - 14:10', 'XI 12', 'Pendidikan Agama', 'MUH. ARSYAD HY., S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(270, 'SENIN', '8', '13:30 - 14:10', 'XII 1', 'Matematika (Pilihan)', 'HARTIWI PRADIKTA, S.Pd., Gr.', '2026-03-21 21:14:16'),
(271, 'SENIN', '8', '13:30 - 14:10', 'XII 2', 'Biologi (Pilihan)', 'Drs. MUHAMMAD HAMZAH, MM.', '2026-03-21 21:14:16'),
(272, 'SENIN', '8', '13:30 - 14:10', 'XII 3', 'Informatika (Pilihan)', 'MUHAMMAD IDRIS RIFAI SARRO, S.Si.', '2026-03-21 21:14:16'),
(273, 'SENIN', '8', '13:30 - 14:10', 'XII 4', 'Biologi (Pilihan)', 'HAMIDATUSSIFAH, S.Si.', '2026-03-21 21:14:16'),
(274, 'SENIN', '8', '13:30 - 14:10', 'XII 5', 'Kimia (Pilihan)', 'SAPRUDDIN, S.Pd.', '2026-03-21 21:14:16'),
(275, 'SENIN', '8', '13:30 - 14:10', 'XII 6', 'Bahasa Indonesia (Pilihan)', 'HASNA BAKRI, S.Pd.', '2026-03-21 21:14:16'),
(276, 'SENIN', '8', '13:30 - 14:10', 'XII 7', 'Ekonomi (Pilihan)', 'MUHAMMAD YUSUF, S.Pd.', '2026-03-21 21:14:16'),
(277, 'SENIN', '8', '13:30 - 14:10', 'XII 8', 'Bahasa Indonesia (Pilihan)', 'ST. ROSMAWATI M., S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(278, 'SENIN', '8', '13:30 - 14:10', 'XII 9', 'Fisika (Pilihan)', 'SAPPEWATI, S.Pd.', '2026-03-21 21:14:16'),
(279, 'SENIN', '8', '13:30 - 14:10', 'XII 10', 'Ekonomi (Pilihan)', 'Dr. Hj. ROSMAWATI, SE.,MA.', '2026-03-21 21:14:16'),
(280, 'SENIN', '8', '13:30 - 14:10', 'XII 11', 'Bahasa Inggris (Pilihan)', 'AFIAH SAKIAH, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(281, 'SENIN', '8', '13:30 - 14:10', 'XII 12', 'Matematika (Pilihan)', 'HAMKA, S.Si., S.Pd., Gr.', '2026-03-21 21:14:16'),
(282, 'SENIN', '9', '14:10 - 14:50', 'X 1', 'Biologi', 'MINDJAH WATY M., S.Si.', '2026-03-21 21:14:16'),
(283, 'SENIN', '9', '14:10 - 14:50', 'X 2', 'Sejarah', 'ERNIATI, S.Pd.', '2026-03-21 21:14:16'),
(284, 'SENIN', '9', '14:10 - 14:50', 'X 3', 'Fisika', 'EVI YULIATI, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(285, 'SENIN', '9', '14:10 - 14:50', 'X 4', 'Bahasa Inggris', 'RISTA JUNIANTI SAHAR, SS.', '2026-03-21 21:14:16'),
(286, 'SENIN', '9', '14:10 - 14:50', 'X 5', 'Sosiologi', 'ADE HANDAYANI, S.Sos., M.Pd., Gr.', '2026-03-21 21:14:16'),
(287, 'SENIN', '9', '14:10 - 14:50', 'X 6', 'Ekonomi', 'A. VEBHY INDAH BELLIANTHARI, S.Pd.', '2026-03-21 21:14:16'),
(288, 'SENIN', '9', '14:10 - 14:50', 'X 7', 'Fisika', 'MUH. TRI PRASETIA NUA, S.Pd., Gr.', '2026-03-21 21:14:16'),
(289, 'SENIN', '9', '14:10 - 14:50', 'X 8', 'Bahasa Inggris', 'ARIFIN, S.Pd.', '2026-03-21 21:14:16'),
(290, 'SENIN', '9', '14:10 - 14:50', 'X 9', 'Biologi', 'NURSANTY, S.Pd.', '2026-03-21 21:14:16'),
(291, 'SENIN', '9', '14:10 - 14:50', 'X 10', 'Geografi', 'SANTI KUSUMA DEWI, S.Pd.', '2026-03-21 21:14:16'),
(292, 'SENIN', '9', '14:10 - 14:50', 'X 11', 'Kimia', 'LENNI BAKRI, S.Pd.', '2026-03-21 21:14:16'),
(293, 'SENIN', '9', '14:10 - 14:50', 'XI 1', 'Bahasa Inggris (Pilihan)', 'RANDIKA DHANI SYAPUTRA, S.Pd.', '2026-03-21 21:14:16'),
(294, 'SENIN', '9', '14:10 - 14:50', 'XI 2', 'Matematika', 'NUR QALBI RUSDIN, S.Pd.,M.Pd.', '2026-03-21 21:14:16'),
(295, 'SENIN', '9', '14:10 - 14:50', 'XI 3', 'Pendidikan Agama', 'YANTI NOVIANTI NAWAWI, S.Pd.I.', '2026-03-21 21:14:16'),
(296, 'SENIN', '9', '14:10 - 14:50', 'XI 4', 'Biologi (Pilihan)', 'CATHARINA NOVENI DWIDJADJANTI, S.Pd.', '2026-03-21 21:14:16'),
(297, 'SENIN', '9', '14:10 - 14:50', 'XI 5', 'Kimia (Pilihan)', 'MARTHINA SATTU ARI, M.Pd.', '2026-03-21 21:14:16'),
(298, 'SENIN', '9', '14:10 - 14:50', 'XI 6', 'Bahasa Indonesia (Pilihan)', 'NUR AKBAR, S.Pd.', '2026-03-21 21:14:16'),
(299, 'SENIN', '9', '14:10 - 14:50', 'XI 7', 'Sosiologi (Pilihan)', 'SRI UTAMI WULANSARI, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(300, 'SENIN', '9', '14:10 - 14:50', 'XI 8', 'Geografi (Pilihan)', 'Drs. BAHARUDDIN', '2026-03-21 21:14:16'),
(301, 'SENIN', '9', '14:10 - 14:50', 'XI 9', 'Pendidikan Agama', 'ACHMAD MUFTI AL GALZA S., S.Pd.I.', '2026-03-21 21:14:16'),
(302, 'SENIN', '9', '14:10 - 14:50', 'XI 10', 'Geografi (Pilihan)', 'HALIFUDDIN, S.Pd.', '2026-03-21 21:14:16'),
(303, 'SENIN', '9', '14:10 - 14:50', 'XI 11', 'Pendidikan Pancasila', 'YEKTIE NUR PRAYOGA LM, S.Pd., Gr.', '2026-03-21 21:14:16'),
(304, 'SENIN', '9', '14:10 - 14:50', 'XI 12', 'Pendidikan Agama', 'MUH. ARSYAD HY., S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(305, 'SENIN', '9', '14:10 - 14:50', 'XII 1', 'Matematika (Pilihan)', 'HARTIWI PRADIKTA, S.Pd., Gr.', '2026-03-21 21:14:16'),
(306, 'SENIN', '9', '14:10 - 14:50', 'XII 2', 'Biologi (Pilihan)', 'Drs. MUHAMMAD HAMZAH, MM.', '2026-03-21 21:14:16'),
(307, 'SENIN', '9', '14:10 - 14:50', 'XII 3', 'Informatika (Pilihan)', 'MUHAMMAD IDRIS RIFAI SARRO, S.Si.', '2026-03-21 21:14:16'),
(308, 'SENIN', '9', '14:10 - 14:50', 'XII 4', 'Biologi (Pilihan)', 'HAMIDATUSSIFAH, S.Si.', '2026-03-21 21:14:16'),
(309, 'SENIN', '9', '14:10 - 14:50', 'XII 5', 'Kimia (Pilihan)', 'SAPRUDDIN, S.Pd.', '2026-03-21 21:14:16'),
(310, 'SENIN', '9', '14:10 - 14:50', 'XII 6', 'Bahasa Indonesia (Pilihan)', 'HASNA BAKRI, S.Pd.', '2026-03-21 21:14:16'),
(311, 'SENIN', '9', '14:10 - 14:50', 'XII 7', 'Ekonomi (Pilihan)', 'MUHAMMAD YUSUF, S.Pd.', '2026-03-21 21:14:16'),
(312, 'SENIN', '9', '14:10 - 14:50', 'XII 8', 'Bahasa Indonesia (Pilihan)', 'ST. ROSMAWATI M., S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(313, 'SENIN', '9', '14:10 - 14:50', 'XII 9', 'Fisika (Pilihan)', 'SAPPEWATI, S.Pd.', '2026-03-21 21:14:16'),
(314, 'SENIN', '9', '14:10 - 14:50', 'XII 10', 'Ekonomi (Pilihan)', 'Dr. Hj. ROSMAWATI, SE.,MA.', '2026-03-21 21:14:16'),
(315, 'SENIN', '9', '14:10 - 14:50', 'XII 11', 'Bahasa Inggris (Pilihan)', 'AFIAH SAKIAH, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(316, 'SENIN', '9', '14:10 - 14:50', 'XII 12', 'Matematika (Pilihan)', 'HAMKA, S.Si., S.Pd., Gr.', '2026-03-21 21:14:16'),
(317, 'SENIN', '10', '14:50 - 15:30', 'X 1', 'Biologi', 'MINDJAH WATY M., S.Si.', '2026-03-21 21:14:16'),
(318, 'SENIN', '10', '14:50 - 15:30', 'X 2', 'Sejarah', 'ERNIATI, S.Pd.', '2026-03-21 21:14:16'),
(319, 'SENIN', '10', '14:50 - 15:30', 'X 3', 'Fisika', 'EVI YULIATI, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(320, 'SENIN', '10', '14:50 - 15:30', 'X 4', 'Bahasa Inggris', 'RISTA JUNIANTI SAHAR, SS.', '2026-03-21 21:14:16'),
(321, 'SENIN', '10', '14:50 - 15:30', 'X 5', 'Sosiologi', 'ADE HANDAYANI, S.Sos., M.Pd., Gr.', '2026-03-21 21:14:16'),
(322, 'SENIN', '10', '14:50 - 15:30', 'X 6', 'Ekonomi', 'A. VEBHY INDAH BELLIANTHARI, S.Pd.', '2026-03-21 21:14:16'),
(323, 'SENIN', '10', '14:50 - 15:30', 'X 7', 'Fisika', 'MUH. TRI PRASETIA NUA, S.Pd., Gr.', '2026-03-21 21:14:16'),
(324, 'SENIN', '10', '14:50 - 15:30', 'X 8', 'Bahasa Inggris', 'ARIFIN, S.Pd.', '2026-03-21 21:14:16'),
(325, 'SENIN', '10', '14:50 - 15:30', 'X 9', 'Biologi', 'NURSANTY, S.Pd.', '2026-03-21 21:14:16'),
(326, 'SENIN', '10', '14:50 - 15:30', 'X 10', 'Geografi', 'SANTI KUSUMA DEWI, S.Pd.', '2026-03-21 21:14:16'),
(327, 'SENIN', '10', '14:50 - 15:30', 'X 11', 'Kimia', 'LENNI BAKRI, S.Pd.', '2026-03-21 21:14:16'),
(328, 'SENIN', '10', '14:50 - 15:30', 'XI 1', 'Bahasa Inggris (Pilihan)', 'RANDIKA DHANI SYAPUTRA, S.Pd.', '2026-03-21 21:14:16'),
(329, 'SENIN', '10', '14:50 - 15:30', 'XI 2', 'Matematika', 'NUR QALBI RUSDIN, S.Pd.,M.Pd.', '2026-03-21 21:14:16'),
(330, 'SENIN', '10', '14:50 - 15:30', 'XI 3', 'Pendidikan Agama', 'YANTI NOVIANTI NAWAWI, S.Pd.I.', '2026-03-21 21:14:16'),
(331, 'SENIN', '10', '14:50 - 15:30', 'XI 4', 'Biologi (Pilihan)', 'CATHARINA NOVENI DWIDJADJANTI, S.Pd.', '2026-03-21 21:14:16'),
(332, 'SENIN', '10', '14:50 - 15:30', 'XI 5', 'Kimia (Pilihan)', 'MARTHINA SATTU ARI, M.Pd.', '2026-03-21 21:14:16'),
(333, 'SENIN', '10', '14:50 - 15:30', 'XI 6', 'Bahasa Indonesia (Pilihan)', 'NUR AKBAR, S.Pd.', '2026-03-21 21:14:16'),
(334, 'SENIN', '10', '14:50 - 15:30', 'XI 7', 'Sosiologi (Pilihan)', 'SRI UTAMI WULANSARI, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(335, 'SENIN', '10', '14:50 - 15:30', 'XI 8', 'Geografi (Pilihan)', 'Drs. BAHARUDDIN', '2026-03-21 21:14:16'),
(336, 'SENIN', '10', '14:50 - 15:30', 'XI 9', 'Pendidikan Agama', 'ACHMAD MUFTI AL GALZA S., S.Pd.I.', '2026-03-21 21:14:16'),
(337, 'SENIN', '10', '14:50 - 15:30', 'XI 10', 'Geografi (Pilihan)', 'HALIFUDDIN, S.Pd.', '2026-03-21 21:14:16'),
(338, 'SENIN', '10', '14:50 - 15:30', 'XI 11', 'Pendidikan Pancasila', 'YEKTIE NUR PRAYOGA LM, S.Pd., Gr.', '2026-03-21 21:14:16'),
(339, 'SENIN', '10', '14:50 - 15:30', 'XI 12', 'Pendidikan Agama', 'MUH. ARSYAD HY., S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(340, 'SENIN', '10', '14:50 - 15:30', 'XII 1', 'Matematika (Pilihan)', 'HARTIWI PRADIKTA, S.Pd., Gr.', '2026-03-21 21:14:16'),
(341, 'SENIN', '10', '14:50 - 15:30', 'XII 2', 'Biologi (Pilihan)', 'Drs. MUHAMMAD HAMZAH, MM.', '2026-03-21 21:14:16'),
(342, 'SENIN', '10', '14:50 - 15:30', 'XII 3', 'Informatika (Pilihan)', 'MUHAMMAD IDRIS RIFAI SARRO, S.Si.', '2026-03-21 21:14:16'),
(343, 'SENIN', '10', '14:50 - 15:30', 'XII 4', 'Biologi (Pilihan)', 'HAMIDATUSSIFAH, S.Si.', '2026-03-21 21:14:16'),
(344, 'SENIN', '10', '14:50 - 15:30', 'XII 5', 'Kimia (Pilihan)', 'SAPRUDDIN, S.Pd.', '2026-03-21 21:14:16'),
(345, 'SENIN', '10', '14:50 - 15:30', 'XII 6', 'Bahasa Indonesia (Pilihan)', 'HASNA BAKRI, S.Pd.', '2026-03-21 21:14:16'),
(346, 'SENIN', '10', '14:50 - 15:30', 'XII 7', 'Ekonomi (Pilihan)', 'MUHAMMAD YUSUF, S.Pd.', '2026-03-21 21:14:16'),
(347, 'SENIN', '10', '14:50 - 15:30', 'XII 8', 'Bahasa Indonesia (Pilihan)', 'ST. ROSMAWATI M., S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(348, 'SENIN', '10', '14:50 - 15:30', 'XII 9', 'Fisika (Pilihan)', 'SAPPEWATI, S.Pd.', '2026-03-21 21:14:16'),
(349, 'SENIN', '10', '14:50 - 15:30', 'XII 10', 'Ekonomi (Pilihan)', 'Dr. Hj. ROSMAWATI, SE.,MA.', '2026-03-21 21:14:16'),
(350, 'SENIN', '10', '14:50 - 15:30', 'XII 11', 'Bahasa Inggris (Pilihan)', 'AFIAH SAKIAH, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(351, 'SENIN', '10', '14:50 - 15:30', 'XII 12', 'Matematika (Pilihan)', 'HAMKA, S.Si., S.Pd., Gr.', '2026-03-21 21:14:16'),
(352, 'SELASA', '1', '07:30 - 08:15', 'X 1', 'Sosiologi', 'ADE HANDAYANI, S.Sos., M.Pd., Gr.', '2026-03-21 21:14:16'),
(353, 'SELASA', '1', '07:30 - 08:15', 'X 2', 'Kimia', 'LENNI BAKRI, S.Pd.', '2026-03-21 21:14:16'),
(354, 'SELASA', '1', '07:30 - 08:15', 'X 3', 'PJOK', 'ASRIANTO, S.Or., Gr.', '2026-03-21 21:14:16'),
(355, 'SELASA', '1', '07:30 - 08:15', 'X 4', 'Matematika', 'SYAMSIAR, S.Pd.', '2026-03-21 21:14:16'),
(356, 'SELASA', '1', '07:30 - 08:15', 'X 5', 'Sejarah', 'ERNIATI, S.Pd.', '2026-03-21 21:14:16'),
(357, 'SELASA', '1', '07:30 - 08:15', 'X 6', 'Fisika', 'SAPPEWATI, S.Pd.', '2026-03-21 21:14:16'),
(358, 'SELASA', '1', '07:30 - 08:15', 'X 7', 'Geografi', 'SANTI KUSUMA DEWI, S.Pd.', '2026-03-21 21:14:16'),
(359, 'SELASA', '1', '07:30 - 08:15', 'X 8', 'Fisika', 'MUH. TRI PRASETIA NUA, S.Pd., Gr.', '2026-03-21 21:14:16'),
(360, 'SELASA', '1', '07:30 - 08:15', 'X 9', 'Pendidikan Agama', 'ACHMAD MUFTI AL GALZA S., S.Pd.I.', '2026-03-21 21:14:16'),
(361, 'SELASA', '1', '07:30 - 08:15', 'X 10', 'Ekonomi', 'A. VEBHY INDAH BELLIANTHARI, S.Pd.', '2026-03-21 21:14:16'),
(362, 'SELASA', '1', '07:30 - 08:15', 'X 11', 'Geografi', 'HALIFUDDIN, S.Pd.', '2026-03-21 21:14:16'),
(363, 'SELASA', '1', '07:30 - 08:15', 'XI 1', 'Matematika', 'NURAFNI PEBRIANTY, S.Pd., Gr.', '2026-03-21 21:14:16'),
(364, 'SELASA', '1', '07:30 - 08:15', 'XI 2', 'Pendidikan Pancasila', 'YEKTIE NUR PRAYOGA LM, S.Pd., Gr.', '2026-03-21 21:14:16'),
(365, 'SELASA', '1', '07:30 - 08:15', 'XI 3', 'PJOK', 'ABD. KAMAL, S.Pd.', '2026-03-21 21:14:16'),
(366, 'SELASA', '1', '07:30 - 08:15', 'XI 4', 'Bahasa Inggris', 'ARIFIN, S.Pd.', '2026-03-21 21:14:16'),
(367, 'SELASA', '1', '07:30 - 08:15', 'XI 5', 'Bahasa Inggris (Pilihan)', 'RANDIKA DHANI SYAPUTRA, S.Pd.', '2026-03-21 21:14:16'),
(368, 'SELASA', '1', '07:30 - 08:15', 'XI 6', 'Kimia (Pilihan)', 'ISMAIL LA HASSE, S.Si., S.Pd.', '2026-03-21 21:14:16'),
(369, 'SELASA', '1', '07:30 - 08:15', 'XI 7', 'Biologi (Pilihan)', 'MINDJAH WATY M., S.Si.', '2026-03-21 21:14:16'),
(370, 'SELASA', '1', '07:30 - 08:15', 'XI 8', 'Seni Budaya', 'ANUGRAYANTI, S.Pd.', '2026-03-21 21:14:16'),
(371, 'SELASA', '1', '07:30 - 08:15', 'XI 9', 'PJOK', 'DWI SETIAWATI B., S.Pd.', '2026-03-21 21:14:16'),
(372, 'SELASA', '1', '07:30 - 08:15', 'XI 10', 'Sosiologi (Pilihan)', 'SRI UTAMI WULANSARI, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(373, 'SELASA', '1', '07:30 - 08:15', 'XI 11', 'Ekonomi (Pilihan)', 'Dr. Hj. ROSMAWATI, SE.,MA.', '2026-03-21 21:14:16'),
(374, 'SELASA', '1', '07:30 - 08:15', 'XI 12', 'Bahasa Indonesia (Pilihan)', 'FITRIANI RAHMAT, S.Pd.', '2026-03-21 21:14:16'),
(375, 'SELASA', '1', '07:30 - 08:15', 'XII 1', 'Pendidikan Agama', 'SITTI NAFI, S.Pd.I.', '2026-03-21 21:14:16'),
(376, 'SELASA', '1', '07:30 - 08:15', 'XII 2', 'PJOK', 'MUH. GILANG FAJARI, S.Pd.', '2026-03-21 21:14:16'),
(377, 'SELASA', '1', '07:30 - 08:15', 'XII 3', 'Pendidikan Pancasila', 'EKA RUSNAENI, S.Pd., Gr.', '2026-03-21 21:14:16'),
(378, 'SELASA', '1', '07:30 - 08:15', 'XII 4', 'Matematika', 'LILIES RATNA RIYANTINI, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(379, 'SELASA', '1', '07:30 - 08:15', 'XII 5', 'Bahasa Inggris', 'MUHAMMAD SALEH, S.Pd., MM.', '2026-03-21 21:14:16'),
(380, 'SELASA', '1', '07:30 - 08:15', 'XII 6', 'Bahasa Indonesia', 'FITRIYANI, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(381, 'SELASA', '1', '07:30 - 08:15', 'XII 7', 'Seni Budaya', 'ST. NADIA KHAERATY, S.Pd.', '2026-03-21 21:14:16'),
(382, 'SELASA', '1', '07:30 - 08:15', 'XII 8', 'Bahasa Indonesia', 'ST. ROSMAWATI M., S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(383, 'SELASA', '1', '07:30 - 08:15', 'XII 9', 'Matematika', 'LUDIHARISTIADI, S.Pd.', '2026-03-21 21:14:16'),
(384, 'SELASA', '1', '07:30 - 08:15', 'XII 10', 'Pendidikan Agama', 'YANTI NOVIANTI NAWAWI, S.Pd.I.', '2026-03-21 21:14:16'),
(385, 'SELASA', '1', '07:30 - 08:15', 'XII 11', 'Sejarah', 'DAMAYANTI, S.Pd.', '2026-03-21 21:14:16'),
(386, 'SELASA', '1', '07:30 - 08:15', 'XII 12', 'Prakarya', 'MUTMAINNAH, S.Pd., M.Biomed.', '2026-03-21 21:14:16'),
(387, 'SELASA', '2', '08:15 - 09:00', 'X 1', 'Sosiologi', 'ADE HANDAYANI, S.Sos., M.Pd., Gr.', '2026-03-21 21:14:16'),
(388, 'SELASA', '2', '08:15 - 09:00', 'X 2', 'Kimia', 'LENNI BAKRI, S.Pd.', '2026-03-21 21:14:16'),
(389, 'SELASA', '2', '08:15 - 09:00', 'X 3', 'PJOK', 'ASRIANTO, S.Or., Gr.', '2026-03-21 21:14:16'),
(390, 'SELASA', '2', '08:15 - 09:00', 'X 4', 'Matematika', 'SYAMSIAR, S.Pd.', '2026-03-21 21:14:16'),
(391, 'SELASA', '2', '08:15 - 09:00', 'X 5', 'Sejarah', 'ERNIATI, S.Pd.', '2026-03-21 21:14:16'),
(392, 'SELASA', '2', '08:15 - 09:00', 'X 6', 'Fisika', 'SAPPEWATI, S.Pd.', '2026-03-21 21:14:16'),
(393, 'SELASA', '2', '08:15 - 09:00', 'X 7', 'Geografi', 'SANTI KUSUMA DEWI, S.Pd.', '2026-03-21 21:14:16'),
(394, 'SELASA', '2', '08:15 - 09:00', 'X 8', 'Fisika', 'MUH. TRI PRASETIA NUA, S.Pd., Gr.', '2026-03-21 21:14:16'),
(395, 'SELASA', '2', '08:15 - 09:00', 'X 9', 'Seni Budaya', 'ASWAN ANWAR, S.Pd.', '2026-03-21 21:14:16'),
(396, 'SELASA', '2', '08:15 - 09:00', 'X 10', 'Ekonomi', 'A. VEBHY INDAH BELLIANTHARI, S.Pd.', '2026-03-21 21:14:16'),
(397, 'SELASA', '2', '08:15 - 09:00', 'X 11', 'Geografi', 'HALIFUDDIN, S.Pd.', '2026-03-21 21:14:16'),
(398, 'SELASA', '2', '08:15 - 09:00', 'XI 1', 'Matematika', 'NURAFNI PEBRIANTY, S.Pd., Gr.', '2026-03-21 21:14:16'),
(399, 'SELASA', '2', '08:15 - 09:00', 'XI 2', 'Pendidikan Pancasila', 'YEKTIE NUR PRAYOGA LM, S.Pd., Gr.', '2026-03-21 21:14:16'),
(400, 'SELASA', '2', '08:15 - 09:00', 'XI 3', 'PJOK', 'ABD. KAMAL, S.Pd.', '2026-03-21 21:14:16'),
(401, 'SELASA', '2', '08:15 - 09:00', 'XI 4', 'Bahasa Inggris', 'ARIFIN, S.Pd.', '2026-03-21 21:14:16'),
(402, 'SELASA', '2', '08:15 - 09:00', 'XI 5', 'Bahasa Inggris (Pilihan)', 'RANDIKA DHANI SYAPUTRA, S.Pd.', '2026-03-21 21:14:16'),
(403, 'SELASA', '2', '08:15 - 09:00', 'XI 6', 'Kimia (Pilihan)', 'ISMAIL LA HASSE, S.Si., S.Pd.', '2026-03-21 21:14:16'),
(404, 'SELASA', '2', '08:15 - 09:00', 'XI 7', 'Biologi (Pilihan)', 'MINDJAH WATY M., S.Si.', '2026-03-21 21:14:16'),
(405, 'SELASA', '2', '08:15 - 09:00', 'XI 8', 'Seni Budaya', 'ANUGRAYANTI, S.Pd.', '2026-03-21 21:14:16'),
(406, 'SELASA', '2', '08:15 - 09:00', 'XI 9', 'PJOK', 'DWI SETIAWATI B., S.Pd.', '2026-03-21 21:14:16'),
(407, 'SELASA', '2', '08:15 - 09:00', 'XI 10', 'Sosiologi (Pilihan)', 'SRI UTAMI WULANSARI, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(408, 'SELASA', '2', '08:15 - 09:00', 'XI 11', 'Ekonomi (Pilihan)', 'Dr. Hj. ROSMAWATI, SE.,MA.', '2026-03-21 21:14:16'),
(409, 'SELASA', '2', '08:15 - 09:00', 'XI 12', 'Bahasa Indonesia (Pilihan)', 'FITRIANI RAHMAT, S.Pd.', '2026-03-21 21:14:16'),
(410, 'SELASA', '2', '08:15 - 09:00', 'XII 1', 'Pendidikan Agama', 'SITTI NAFI, S.Pd.I.', '2026-03-21 21:14:16'),
(411, 'SELASA', '2', '08:15 - 09:00', 'XII 2', 'PJOK', 'MUH. GILANG FAJARI, S.Pd.', '2026-03-21 21:14:16'),
(412, 'SELASA', '2', '08:15 - 09:00', 'XII 3', 'Pendidikan Pancasila', 'EKA RUSNAENI, S.Pd., Gr.', '2026-03-21 21:14:16'),
(413, 'SELASA', '2', '08:15 - 09:00', 'XII 4', 'Matematika', 'LILIES RATNA RIYANTINI, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(414, 'SELASA', '2', '08:15 - 09:00', 'XII 5', 'Bahasa Inggris', 'MUHAMMAD SALEH, S.Pd., MM.', '2026-03-21 21:14:16'),
(415, 'SELASA', '2', '08:15 - 09:00', 'XII 6', 'Bahasa Indonesia', 'FITRIYANI, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(416, 'SELASA', '2', '08:15 - 09:00', 'XII 7', 'Seni Budaya', 'ST. NADIA KHAERATY, S.Pd.', '2026-03-21 21:14:16'),
(417, 'SELASA', '2', '08:15 - 09:00', 'XII 8', 'Bahasa Indonesia', 'ST. ROSMAWATI M., S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(418, 'SELASA', '2', '08:15 - 09:00', 'XII 9', 'Matematika', 'LUDIHARISTIADI, S.Pd.', '2026-03-21 21:14:16'),
(419, 'SELASA', '2', '08:15 - 09:00', 'XII 10', 'Pendidikan Agama', 'YANTI NOVIANTI NAWAWI, S.Pd.I.', '2026-03-21 21:14:16'),
(420, 'SELASA', '2', '08:15 - 09:00', 'XII 11', 'Sejarah', 'DAMAYANTI, S.Pd.', '2026-03-21 21:14:16'),
(421, 'SELASA', '2', '08:15 - 09:00', 'XII 12', 'Prakarya', 'MUTMAINNAH, S.Pd., M.Biomed.', '2026-03-21 21:14:16'),
(422, 'SELASA', '3', '09:00 - 09:45', 'X 1', 'Sosiologi', 'ADE HANDAYANI, S.Sos., M.Pd., Gr.', '2026-03-21 21:14:16'),
(423, 'SELASA', '3', '09:00 - 09:45', 'X 2', 'Kimia', 'LENNI BAKRI, S.Pd.', '2026-03-21 21:14:16'),
(424, 'SELASA', '3', '09:00 - 09:45', 'X 3', 'PJOK', 'ASRIANTO, S.Or., Gr.', '2026-03-21 21:14:16'),
(425, 'SELASA', '3', '09:00 - 09:45', 'X 4', 'Bahasa Indonesia', 'NURFATWA, S.Pd.', '2026-03-21 21:14:16'),
(426, 'SELASA', '3', '09:00 - 09:45', 'X 5', 'Sejarah', 'ERNIATI, S.Pd.', '2026-03-21 21:14:16'),
(427, 'SELASA', '3', '09:00 - 09:45', 'X 6', 'Fisika', 'SAPPEWATI, S.Pd.', '2026-03-21 21:14:16'),
(428, 'SELASA', '3', '09:00 - 09:45', 'X 7', 'Geografi', 'SANTI KUSUMA DEWI, S.Pd.', '2026-03-21 21:14:16'),
(429, 'SELASA', '3', '09:00 - 09:45', 'X 8', 'Fisika', 'MUH. TRI PRASETIA NUA, S.Pd., Gr.', '2026-03-21 21:14:16'),
(430, 'SELASA', '3', '09:00 - 09:45', 'X 9', 'Seni Budaya', 'ASWAN ANWAR, S.Pd.', '2026-03-21 21:14:16'),
(431, 'SELASA', '3', '09:00 - 09:45', 'X 10', 'Ekonomi', 'A. VEBHY INDAH BELLIANTHARI, S.Pd.', '2026-03-21 21:14:16'),
(432, 'SELASA', '3', '09:00 - 09:45', 'X 11', 'Geografi', 'HALIFUDDIN, S.Pd.', '2026-03-21 21:14:16'),
(433, 'SELASA', '3', '09:00 - 09:45', 'XI 1', 'Sejarah', 'ALI TAHIR, S.Pd.', '2026-03-21 21:14:16'),
(434, 'SELASA', '3', '09:00 - 09:45', 'XI 2', 'Kimia (Pilihan)', 'SAPRUDDIN, S.Pd.', '2026-03-21 21:14:16'),
(435, 'SELASA', '3', '09:00 - 09:45', 'XI 3', 'PJOK', 'ABD. KAMAL, S.Pd.', '2026-03-21 21:14:16'),
(436, 'SELASA', '3', '09:00 - 09:45', 'XI 4', 'Bahasa Inggris', 'ARIFIN, S.Pd.', '2026-03-21 21:14:16'),
(437, 'SELASA', '3', '09:00 - 09:45', 'XI 5', 'Bahasa Indonesia', 'NUR AKBAR, S.Pd.', '2026-03-21 21:14:16'),
(438, 'SELASA', '3', '09:00 - 09:45', 'XI 6', 'Sosiologi (Pilihan)', 'SRI UTAMI WULANSARI, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(439, 'SELASA', '3', '09:00 - 09:45', 'XI 7', 'Kimia (Pilihan)', 'ISMAIL LA HASSE, S.Si., S.Pd.', '2026-03-21 21:14:16'),
(440, 'SELASA', '3', '09:00 - 09:45', 'XI 8', 'Geografi (Pilihan)', 'Drs. BAHARUDDIN', '2026-03-21 21:14:16'),
(441, 'SELASA', '3', '09:00 - 09:45', 'XI 9', 'PJOK', 'DWI SETIAWATI B., S.Pd.', '2026-03-21 21:14:16'),
(442, 'SELASA', '3', '09:00 - 09:45', 'XI 10', 'Ekonomi (Pilihan)', 'MUHAMMAD YUSUF, S.Pd.', '2026-03-21 21:14:16'),
(443, 'SELASA', '3', '09:00 - 09:45', 'XI 11', 'Pendidikan Agama', 'ACHMAD MUFTI AL GALZA S., S.Pd.I.', '2026-03-21 21:14:16'),
(444, 'SELASA', '3', '09:00 - 09:45', 'XI 12', 'Ekonomi (Pilihan)', 'Dr. Hj. ROSMAWATI, SE.,MA.', '2026-03-21 21:14:16'),
(445, 'SELASA', '3', '09:00 - 09:45', 'XII 1', 'Pendidikan Agama', 'SITTI NAFI, S.Pd.I.', '2026-03-21 21:14:16'),
(446, 'SELASA', '3', '09:00 - 09:45', 'XII 2', 'PJOK', 'MUH. GILANG FAJARI, S.Pd.', '2026-03-21 21:14:16'),
(447, 'SELASA', '3', '09:00 - 09:45', 'XII 3', 'Matematika', 'LILIES RATNA RIYANTINI, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(448, 'SELASA', '3', '09:00 - 09:45', 'XII 4', 'Pendidikan Pancasila', 'EKA RUSNAENI, S.Pd., Gr.', '2026-03-21 21:14:16'),
(449, 'SELASA', '3', '09:00 - 09:45', 'XII 5', 'Bahasa Inggris', 'MUHAMMAD SALEH, S.Pd., MM.', '2026-03-21 21:14:16');
INSERT INTO `schedules` (`id`, `hari`, `jam_ke`, `waktu`, `kelas`, `mata_pelajaran`, `nama_guru`, `created_at`) VALUES
(450, 'SELASA', '3', '09:00 - 09:45', 'XII 6', 'Seni Budaya', 'ST. NADIA KHAERATY, S.Pd.', '2026-03-21 21:14:16'),
(451, 'SELASA', '3', '09:00 - 09:45', 'XII 7', 'Bahasa Indonesia', 'FITRIYANI, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(452, 'SELASA', '3', '09:00 - 09:45', 'XII 8', 'Matematika', 'MASNIATI, S.Pd.', '2026-03-21 21:14:16'),
(453, 'SELASA', '3', '09:00 - 09:45', 'XII 9', 'Prakarya', 'SARWANA, S.Pd.', '2026-03-21 21:14:16'),
(454, 'SELASA', '3', '09:00 - 09:45', 'XII 10', 'Bahasa Indonesia', 'ST. ROSMAWATI M., S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(455, 'SELASA', '3', '09:00 - 09:45', 'XII 11', 'Pendidikan Agama', 'YANTI NOVIANTI NAWAWI, S.Pd.I.', '2026-03-21 21:14:16'),
(456, 'SELASA', '3', '09:00 - 09:45', 'XII 12', 'Sejarah', 'DAMAYANTI, S.Pd.', '2026-03-21 21:14:16'),
(457, 'SELASA', '4', '09:45 - 10:30', 'X 1', 'Fisika', 'EVI YULIATI, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(458, 'SELASA', '4', '09:45 - 10:30', 'X 2', 'Muatan Lokal (BTQ)', 'SURYADI SYARIF, S.Pd.', '2026-03-21 21:14:16'),
(459, 'SELASA', '4', '09:45 - 10:30', 'X 3', 'Matematika', 'NURAFNI PEBRIANTY, S.Pd., Gr.', '2026-03-21 21:14:16'),
(460, 'SELASA', '4', '09:45 - 10:30', 'X 4', 'Geografi', 'SANTI KUSUMA DEWI, S.Pd.', '2026-03-21 21:14:16'),
(461, 'SELASA', '4', '09:45 - 10:30', 'X 5', 'Bahasa Indonesia', 'NURFATWA, S.Pd.', '2026-03-21 21:14:16'),
(462, 'SELASA', '4', '09:45 - 10:30', 'X 6', 'Kimia', 'LENNI BAKRI, S.Pd.', '2026-03-21 21:14:16'),
(463, 'SELASA', '4', '09:45 - 10:30', 'X 7', 'Sejarah', 'ERNIATI, S.Pd.', '2026-03-21 21:14:16'),
(464, 'SELASA', '4', '09:45 - 10:30', 'X 8', 'Ekonomi', 'A. VEBHY INDAH BELLIANTHARI, S.Pd.', '2026-03-21 21:14:16'),
(465, 'SELASA', '4', '09:45 - 10:30', 'X 9', 'Pendidikan Agama', 'ACHMAD MUFTI AL GALZA S., S.Pd.I.', '2026-03-21 21:14:16'),
(466, 'SELASA', '4', '09:45 - 10:30', 'X 10', 'Pendidikan Pancasila', 'YEKTIE NUR PRAYOGA LM, S.Pd., Gr.', '2026-03-21 21:14:16'),
(467, 'SELASA', '4', '09:45 - 10:30', 'X 11', 'Pendidikan Agama', 'MUH. ARSYAD HY., S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(468, 'SELASA', '4', '09:45 - 10:30', 'XI 1', 'Sejarah', 'ALI TAHIR, S.Pd.', '2026-03-21 21:14:16'),
(469, 'SELASA', '4', '09:45 - 10:30', 'XI 2', 'Kimia (Pilihan)', 'SAPRUDDIN, S.Pd.', '2026-03-21 21:14:16'),
(470, 'SELASA', '4', '09:45 - 10:30', 'XI 3', 'Bahasa Inggris', 'ARIFIN, S.Pd.', '2026-03-21 21:14:16'),
(471, 'SELASA', '4', '09:45 - 10:30', 'XI 4', 'PJOK', 'DWI SETIAWATI B., S.Pd.', '2026-03-21 21:14:16'),
(472, 'SELASA', '4', '09:45 - 10:30', 'XI 5', 'Bahasa Indonesia', 'NUR AKBAR, S.Pd.', '2026-03-21 21:14:16'),
(473, 'SELASA', '4', '09:45 - 10:30', 'XI 6', 'Sosiologi (Pilihan)', 'SRI UTAMI WULANSARI, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(474, 'SELASA', '4', '09:45 - 10:30', 'XI 7', 'Kimia (Pilihan)', 'ISMAIL LA HASSE, S.Si., S.Pd.', '2026-03-21 21:14:16'),
(475, 'SELASA', '4', '09:45 - 10:30', 'XI 8', 'Geografi (Pilihan)', 'Drs. BAHARUDDIN', '2026-03-21 21:14:16'),
(476, 'SELASA', '4', '09:45 - 10:30', 'XI 9', 'Matematika', 'HARTIWI PRADIKTA, S.Pd., Gr.', '2026-03-21 21:14:16'),
(477, 'SELASA', '4', '09:45 - 10:30', 'XI 10', 'Ekonomi (Pilihan)', 'MUHAMMAD YUSUF, S.Pd.', '2026-03-21 21:14:16'),
(478, 'SELASA', '4', '09:45 - 10:30', 'XI 11', 'PJOK', 'ASRIANTO, S.Or., Gr.', '2026-03-21 21:14:16'),
(479, 'SELASA', '4', '09:45 - 10:30', 'XI 12', 'Ekonomi (Pilihan)', 'Dr. Hj. ROSMAWATI, SE.,MA.', '2026-03-21 21:14:16'),
(480, 'SELASA', '4', '09:45 - 10:30', 'XII 1', 'PJOK', 'MUH. GILANG FAJARI, S.Pd.', '2026-03-21 21:14:16'),
(481, 'SELASA', '4', '09:45 - 10:30', 'XII 2', 'Pendidikan Agama', 'SITTI NAFI, S.Pd.I.', '2026-03-21 21:14:16'),
(482, 'SELASA', '4', '09:45 - 10:30', 'XII 3', 'Matematika', 'LILIES RATNA RIYANTINI, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(483, 'SELASA', '4', '09:45 - 10:30', 'XII 4', 'Pendidikan Pancasila', 'EKA RUSNAENI, S.Pd., Gr.', '2026-03-21 21:14:16'),
(484, 'SELASA', '4', '09:45 - 10:30', 'XII 5', 'Prakarya', 'MUTMAINNAH, S.Pd., M.Biomed.', '2026-03-21 21:14:16'),
(485, 'SELASA', '4', '09:45 - 10:30', 'XII 6', 'Bahasa Inggris', 'MUHAMMAD SALEH, S.Pd., MM.', '2026-03-21 21:14:16'),
(486, 'SELASA', '4', '09:45 - 10:30', 'XII 7', 'Bahasa Indonesia', 'FITRIYANI, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(487, 'SELASA', '4', '09:45 - 10:30', 'XII 8', 'Matematika', 'MASNIATI, S.Pd.', '2026-03-21 21:14:16'),
(488, 'SELASA', '4', '09:45 - 10:30', 'XII 9', 'PJOK', 'ABD. KAMAL, S.Pd.', '2026-03-21 21:14:16'),
(489, 'SELASA', '4', '09:45 - 10:30', 'XII 10', 'Bahasa Indonesia', 'ST. ROSMAWATI M., S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(490, 'SELASA', '4', '09:45 - 10:30', 'XII 11', 'Pendidikan Agama', 'YANTI NOVIANTI NAWAWI, S.Pd.I.', '2026-03-21 21:14:16'),
(491, 'SELASA', '4', '09:45 - 10:30', 'XII 12', 'Sejarah', 'DAMAYANTI, S.Pd.', '2026-03-21 21:14:16'),
(492, 'SELASA', '5', '10:45 - 11:30', 'X 1', 'Fisika', 'EVI YULIATI, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(493, 'SELASA', '5', '10:45 - 11:30', 'X 2', 'Muatan Lokal (BTQ)', 'SURYADI SYARIF, S.Pd.', '2026-03-21 21:14:16'),
(494, 'SELASA', '5', '10:45 - 11:30', 'X 3', 'Matematika', 'NURAFNI PEBRIANTY, S.Pd., Gr.', '2026-03-21 21:14:16'),
(495, 'SELASA', '5', '10:45 - 11:30', 'X 4', 'Geografi', 'SANTI KUSUMA DEWI, S.Pd.', '2026-03-21 21:14:16'),
(496, 'SELASA', '5', '10:45 - 11:30', 'X 5', 'Bahasa Indonesia', 'NURFATWA, S.Pd.', '2026-03-21 21:14:16'),
(497, 'SELASA', '5', '10:45 - 11:30', 'X 6', 'Kimia', 'LENNI BAKRI, S.Pd.', '2026-03-21 21:14:16'),
(498, 'SELASA', '5', '10:45 - 11:30', 'X 7', 'Sejarah', 'ERNIATI, S.Pd.', '2026-03-21 21:14:16'),
(499, 'SELASA', '5', '10:45 - 11:30', 'X 8', 'Ekonomi', 'A. VEBHY INDAH BELLIANTHARI, S.Pd.', '2026-03-21 21:14:16'),
(500, 'SELASA', '5', '10:45 - 11:30', 'X 9', 'Pendidikan Agama', 'ACHMAD MUFTI AL GALZA S., S.Pd.I.', '2026-03-21 21:14:16'),
(501, 'SELASA', '5', '10:45 - 11:30', 'X 10', 'Pendidikan Pancasila', 'YEKTIE NUR PRAYOGA LM, S.Pd., Gr.', '2026-03-21 21:14:16'),
(502, 'SELASA', '5', '10:45 - 11:30', 'X 11', 'Pendidikan Agama', 'MUH. ARSYAD HY., S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(503, 'SELASA', '5', '10:45 - 11:30', 'XI 1', 'Bahasa Indonesia', 'HASNA BAKRI, S.Pd.', '2026-03-21 21:14:16'),
(504, 'SELASA', '5', '10:45 - 11:30', 'XI 2', 'Informatika (Pilihan)', 'MUHAMMAD IDRIS RIFAI SARRO, S.Si.', '2026-03-21 21:14:16'),
(505, 'SELASA', '5', '10:45 - 11:30', 'XI 3', 'Bahasa Inggris', 'ARIFIN, S.Pd.', '2026-03-21 21:14:16'),
(506, 'SELASA', '5', '10:45 - 11:30', 'XI 4', 'PJOK', 'DWI SETIAWATI B., S.Pd.', '2026-03-21 21:14:16'),
(507, 'SELASA', '5', '10:45 - 11:30', 'XI 5', 'Kimia (Pilihan)', 'MARTHINA SATTU ARI, M.Pd.', '2026-03-21 21:14:16'),
(508, 'SELASA', '5', '10:45 - 11:30', 'XI 6', 'Bahasa Indonesia (Pilihan)', 'NUR AKBAR, S.Pd.', '2026-03-21 21:14:16'),
(509, 'SELASA', '5', '10:45 - 11:30', 'XI 7', 'Sosiologi (Pilihan)', 'SRI UTAMI WULANSARI, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(510, 'SELASA', '5', '10:45 - 11:30', 'XI 8', 'Ekonomi (Pilihan)', 'MUHAMMAD YUSUF, S.Pd.', '2026-03-21 21:14:16'),
(511, 'SELASA', '5', '10:45 - 11:30', 'XI 9', 'Bahasa Inggris (Pilihan)', 'RANDIKA DHANI SYAPUTRA, S.Pd.', '2026-03-21 21:14:16'),
(512, 'SELASA', '5', '10:45 - 11:30', 'XI 10', 'Bahasa Indonesia', 'FITRIANI RAHMAT, S.Pd.', '2026-03-21 21:14:16'),
(513, 'SELASA', '5', '10:45 - 11:30', 'XI 11', 'PJOK', 'ASRIANTO, S.Or., Gr.', '2026-03-21 21:14:16'),
(514, 'SELASA', '5', '10:45 - 11:30', 'XI 12', 'Geografi (Pilihan)', 'Drs. BAHARUDDIN', '2026-03-21 21:14:16'),
(515, 'SELASA', '5', '10:45 - 11:30', 'XII 1', 'PJOK', 'MUH. GILANG FAJARI, S.Pd.', '2026-03-21 21:14:16'),
(516, 'SELASA', '5', '10:45 - 11:30', 'XII 2', 'Pendidikan Agama', 'SITTI NAFI, S.Pd.I.', '2026-03-21 21:14:16'),
(517, 'SELASA', '5', '10:45 - 11:30', 'XII 3', 'Bahasa Indonesia', 'FITRIYANI, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(518, 'SELASA', '5', '10:45 - 11:30', 'XII 4', 'Sejarah', 'DAMAYANTI, S.Pd.', '2026-03-21 21:14:16'),
(519, 'SELASA', '5', '10:45 - 11:30', 'XII 5', 'Bahasa Indonesia', 'ST. ROSMAWATI M., S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(520, 'SELASA', '5', '10:45 - 11:30', 'XII 6', 'Bahasa Inggris', 'MUHAMMAD SALEH, S.Pd., MM.', '2026-03-21 21:14:16'),
(521, 'SELASA', '5', '10:45 - 11:30', 'XII 7', 'Matematika', 'LUDIHARISTIADI, S.Pd.', '2026-03-21 21:14:16'),
(522, 'SELASA', '5', '10:45 - 11:30', 'XII 8', 'Pendidikan Pancasila', 'EKA RUSNAENI, S.Pd., Gr.', '2026-03-21 21:14:16'),
(523, 'SELASA', '5', '10:45 - 11:30', 'XII 9', 'PJOK', 'ABD. KAMAL, S.Pd.', '2026-03-21 21:14:16'),
(524, 'SELASA', '5', '10:45 - 11:30', 'XII 10', 'Prakarya', 'SARWANA, S.Pd.', '2026-03-21 21:14:16'),
(525, 'SELASA', '5', '10:45 - 11:30', 'XII 11', 'Matematika', 'LILIES RATNA RIYANTINI, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(526, 'SELASA', '5', '10:45 - 11:30', 'XII 12', 'Matematika', 'MASNIATI, S.Pd.', '2026-03-21 21:14:16'),
(527, 'SELASA', '6', '11:30 - 12:15', 'X 1', 'Fisika', 'EVI YULIATI, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(528, 'SELASA', '6', '11:30 - 12:15', 'X 2', 'Seni Budaya', 'ASWAN ANWAR, S.Pd.', '2026-03-21 21:14:16'),
(529, 'SELASA', '6', '11:30 - 12:15', 'X 3', 'Pendidikan Agama', 'SURYADI SYARIF, S.Pd.', '2026-03-21 21:14:16'),
(530, 'SELASA', '6', '11:30 - 12:15', 'X 4', 'Geografi', 'SANTI KUSUMA DEWI, S.Pd.', '2026-03-21 21:14:16'),
(531, 'SELASA', '6', '11:30 - 12:15', 'X 5', 'Pendidikan Agama', 'ACHMAD MUFTI AL GALZA S., S.Pd.I.', '2026-03-21 21:14:16'),
(532, 'SELASA', '6', '11:30 - 12:15', 'X 6', 'Kimia', 'LENNI BAKRI, S.Pd.', '2026-03-21 21:14:16'),
(533, 'SELASA', '6', '11:30 - 12:15', 'X 7', 'Sejarah', 'ERNIATI, S.Pd.', '2026-03-21 21:14:16'),
(534, 'SELASA', '6', '11:30 - 12:15', 'X 8', 'Ekonomi', 'A. VEBHY INDAH BELLIANTHARI, S.Pd.', '2026-03-21 21:14:16'),
(535, 'SELASA', '6', '11:30 - 12:15', 'X 9', 'Pendidikan Pancasila', 'YEKTIE NUR PRAYOGA LM, S.Pd., Gr.', '2026-03-21 21:14:16'),
(536, 'SELASA', '6', '11:30 - 12:15', 'X 10', 'Informatika', 'RIZA RISKY YULIANTI, S.Pd., Gr.', '2026-03-21 21:14:16'),
(537, 'SELASA', '6', '11:30 - 12:15', 'X 11', 'Pendidikan Agama', 'MUH. ARSYAD HY., S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(538, 'SELASA', '6', '11:30 - 12:15', 'XI 1', 'Bahasa Inggris', 'RISTA JUNIANTI SAHAR, SS.', '2026-03-21 21:14:16'),
(539, 'SELASA', '6', '11:30 - 12:15', 'XI 2', 'Informatika (Pilihan)', 'MUHAMMAD IDRIS RIFAI SARRO, S.Si.', '2026-03-21 21:14:16'),
(540, 'SELASA', '6', '11:30 - 12:15', 'XI 3', 'Bahasa Inggris', 'ARIFIN, S.Pd.', '2026-03-21 21:14:16'),
(541, 'SELASA', '6', '11:30 - 12:15', 'XI 4', 'PJOK', 'DWI SETIAWATI B., S.Pd.', '2026-03-21 21:14:16'),
(542, 'SELASA', '6', '11:30 - 12:15', 'XI 5', 'Kimia (Pilihan)', 'MARTHINA SATTU ARI, M.Pd.', '2026-03-21 21:14:16'),
(543, 'SELASA', '6', '11:30 - 12:15', 'XI 6', 'Bahasa Indonesia (Pilihan)', 'NUR AKBAR, S.Pd.', '2026-03-21 21:14:16'),
(544, 'SELASA', '6', '11:30 - 12:15', 'XI 7', 'Sosiologi (Pilihan)', 'SRI UTAMI WULANSARI, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(545, 'SELASA', '6', '11:30 - 12:15', 'XI 8', 'Ekonomi (Pilihan)', 'MUHAMMAD YUSUF, S.Pd.', '2026-03-21 21:14:16'),
(546, 'SELASA', '6', '11:30 - 12:15', 'XI 9', 'Bahasa Inggris (Pilihan)', 'RANDIKA DHANI SYAPUTRA, S.Pd.', '2026-03-21 21:14:16'),
(547, 'SELASA', '6', '11:30 - 12:15', 'XI 10', 'Bahasa Indonesia', 'FITRIANI RAHMAT, S.Pd.', '2026-03-21 21:14:16'),
(548, 'SELASA', '6', '11:30 - 12:15', 'XI 11', 'PJOK', 'ASRIANTO, S.Or., Gr.', '2026-03-21 21:14:16'),
(549, 'SELASA', '6', '11:30 - 12:15', 'XI 12', 'Geografi (Pilihan)', 'Drs. BAHARUDDIN', '2026-03-21 21:14:16'),
(550, 'SELASA', '6', '11:30 - 12:15', 'XII 1', 'PJOK', 'MUH. GILANG FAJARI, S.Pd.', '2026-03-21 21:14:16'),
(551, 'SELASA', '6', '11:30 - 12:15', 'XII 2', 'Pendidikan Agama', 'SITTI NAFI, S.Pd.I.', '2026-03-21 21:14:16'),
(552, 'SELASA', '6', '11:30 - 12:15', 'XII 3', 'Bahasa Indonesia', 'FITRIYANI, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(553, 'SELASA', '6', '11:30 - 12:15', 'XII 4', 'Sejarah', 'DAMAYANTI, S.Pd.', '2026-03-21 21:14:16'),
(554, 'SELASA', '6', '11:30 - 12:15', 'XII 5', 'Bahasa Indonesia', 'ST. ROSMAWATI M., S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(555, 'SELASA', '6', '11:30 - 12:15', 'XII 6', 'Bahasa Inggris', 'MUHAMMAD SALEH, S.Pd., MM.', '2026-03-21 21:14:16'),
(556, 'SELASA', '6', '11:30 - 12:15', 'XII 7', 'Matematika', 'LUDIHARISTIADI, S.Pd.', '2026-03-21 21:14:16'),
(557, 'SELASA', '6', '11:30 - 12:15', 'XII 8', 'Pendidikan Pancasila', 'EKA RUSNAENI, S.Pd., Gr.', '2026-03-21 21:14:16'),
(558, 'SELASA', '6', '11:30 - 12:15', 'XII 9', 'PJOK', 'ABD. KAMAL, S.Pd.', '2026-03-21 21:14:16'),
(559, 'SELASA', '6', '11:30 - 12:15', 'XII 10', 'Prakarya', 'SARWANA, S.Pd.', '2026-03-21 21:14:16'),
(560, 'SELASA', '6', '11:30 - 12:15', 'XII 11', 'Matematika', 'LILIES RATNA RIYANTINI, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(561, 'SELASA', '6', '11:30 - 12:15', 'XII 12', 'Matematika', 'MASNIATI, S.Pd.', '2026-03-21 21:14:16'),
(562, 'SELASA', '7', '12:50 - 13:30', 'X 1', 'Bahasa Indonesia', 'NURFATWA, S.Pd.', '2026-03-21 21:14:16'),
(563, 'SELASA', '7', '12:50 - 13:30', 'X 2', 'Seni Budaya', 'ASWAN ANWAR, S.Pd.', '2026-03-21 21:14:16'),
(564, 'SELASA', '7', '12:50 - 13:30', 'X 3', 'Pendidikan Agama', 'SURYADI SYARIF, S.Pd.', '2026-03-21 21:14:16'),
(565, 'SELASA', '7', '12:50 - 13:30', 'X 4', 'Ekonomi', 'A. VEBHY INDAH BELLIANTHARI, S.Pd.', '2026-03-21 21:14:16'),
(566, 'SELASA', '7', '12:50 - 13:30', 'X 5', 'Pendidikan Agama', 'ACHMAD MUFTI AL GALZA S., S.Pd.I.', '2026-03-21 21:14:16'),
(567, 'SELASA', '7', '12:50 - 13:30', 'X 6', 'Pendidikan Agama', 'MUH. ARSYAD HY., S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(568, 'SELASA', '7', '12:50 - 13:30', 'X 7', 'Kimia', 'LENNI BAKRI, S.Pd.', '2026-03-21 21:14:16'),
(569, 'SELASA', '7', '12:50 - 13:30', 'X 8', 'Geografi', 'SANTI KUSUMA DEWI, S.Pd.', '2026-03-21 21:14:16'),
(570, 'SELASA', '7', '12:50 - 13:30', 'X 9', 'Pendidikan Pancasila', 'YEKTIE NUR PRAYOGA LM, S.Pd., Gr.', '2026-03-21 21:14:16'),
(571, 'SELASA', '7', '12:50 - 13:30', 'X 10', 'Informatika', 'RIZA RISKY YULIANTI, S.Pd., Gr.', '2026-03-21 21:14:16'),
(572, 'SELASA', '7', '12:50 - 13:30', 'X 11', 'Matematika', 'HARTIWI PRADIKTA, S.Pd., Gr.', '2026-03-21 21:14:16'),
(573, 'SELASA', '7', '12:50 - 13:30', 'XI 1', 'Bahasa Inggris', 'RISTA JUNIANTI SAHAR, SS.', '2026-03-21 21:14:16'),
(574, 'SELASA', '7', '12:50 - 13:30', 'XI 2', 'Prakarya', 'MUTMAINNAH, S.Pd., M.Biomed.', '2026-03-21 21:14:16'),
(575, 'SELASA', '7', '12:50 - 13:30', 'XI 3', 'Bahasa Inggris (Pilihan)', 'RANDIKA DHANI SYAPUTRA, S.Pd.', '2026-03-21 21:14:16'),
(576, 'SELASA', '7', '12:50 - 13:30', 'XI 4', 'Bahasa Indonesia', 'FITRIANI RAHMAT, S.Pd.', '2026-03-21 21:14:16'),
(577, 'SELASA', '7', '12:50 - 13:30', 'XI 5', 'Biologi (Pilihan)', 'CATHARINA NOVENI DWIDJADJANTI, S.Pd.', '2026-03-21 21:14:16'),
(578, 'SELASA', '7', '12:50 - 13:30', 'XI 6', 'Sejarah', 'ALI TAHIR, S.Pd.', '2026-03-21 21:14:16'),
(579, 'SELASA', '7', '12:50 - 13:30', 'XI 7', 'Pendidikan Pancasila', 'EKA RUSNAENI, S.Pd., Gr.', '2026-03-21 21:14:16'),
(580, 'SELASA', '7', '12:50 - 13:30', 'XI 8', 'Sosiologi (Pilihan)', 'SRI UTAMI WULANSARI, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(581, 'SELASA', '7', '12:50 - 13:30', 'XI 9', 'Ekonomi (Pilihan)', 'MUHAMMAD YUSUF, S.Pd.', '2026-03-21 21:14:16'),
(582, 'SELASA', '7', '12:50 - 13:30', 'XI 10', 'Bahasa Inggris (Pilihan)', 'AFIAH SAKIAH, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(583, 'SELASA', '7', '12:50 - 13:30', 'XI 11', 'Geografi (Pilihan)', 'HALIFUDDIN, S.Pd.', '2026-03-21 21:14:16'),
(584, 'SELASA', '7', '12:50 - 13:30', 'XI 12', 'Prakarya', 'SARWANA, S.Pd.', '2026-03-21 21:14:16'),
(585, 'SELASA', '7', '12:50 - 13:30', 'XII 1', 'Bahasa Indonesia (Pilihan)', 'NUR AKBAR, S.Pd.', '2026-03-21 21:14:16'),
(586, 'SELASA', '7', '12:50 - 13:30', 'XII 2', 'Ekonomi (Pilihan)', 'Dr. Hj. ROSMAWATI, SE.,MA.', '2026-03-21 21:14:16'),
(587, 'SELASA', '7', '12:50 - 13:30', 'XII 3', 'Kimia (Pilihan)', 'ISMAIL LA HASSE, S.Si., S.Pd.', '2026-03-21 21:14:16'),
(588, 'SELASA', '7', '12:50 - 13:30', 'XII 4', 'Kimia (Pilihan)', 'SAPRUDDIN, S.Pd.', '2026-03-21 21:14:16'),
(589, 'SELASA', '7', '12:50 - 13:30', 'XII 5', 'Bahasa Indonesia (Pilihan)', 'HASNA BAKRI, S.Pd.', '2026-03-21 21:14:16'),
(590, 'SELASA', '7', '12:50 - 13:30', 'XII 6', 'Biologi (Pilihan)', 'HAMIDATUSSIFAH, S.Si.', '2026-03-21 21:14:16'),
(591, 'SELASA', '7', '12:50 - 13:30', 'XII 7', 'Informatika (Pilihan)', 'MUHAMMAD IDRIS RIFAI SARRO, S.Si.', '2026-03-21 21:14:16'),
(592, 'SELASA', '7', '12:50 - 13:30', 'XII 8', 'Kimia (Pilihan)', 'MARTHINA SATTU ARI, M.Pd.', '2026-03-21 21:14:16'),
(593, 'SELASA', '7', '12:50 - 13:30', 'XII 9', 'Matematika (Pilihan)', 'NUR QALBI RUSDIN, S.Pd.,M.Pd.', '2026-03-21 21:14:16'),
(594, 'SELASA', '7', '12:50 - 13:30', 'XII 10', 'Biologi (Pilihan)', 'MINDJAH WATY M., S.Si.', '2026-03-21 21:14:16'),
(595, 'SELASA', '7', '12:50 - 13:30', 'XII 11', 'Matematika (Pilihan)', 'HAMKA, S.Si., S.Pd., Gr.', '2026-03-21 21:14:16'),
(596, 'SELASA', '7', '12:50 - 13:30', 'XII 12', 'Geografi (Pilihan)', 'Drs. BAHARUDDIN', '2026-03-21 21:14:16'),
(597, 'SELASA', '8', '13:30 - 14:10', 'X 1', 'Bahasa Indonesia', 'NURFATWA, S.Pd.', '2026-03-21 21:14:16'),
(598, 'SELASA', '8', '13:30 - 14:10', 'X 2', 'Ekonomi', 'A. VEBHY INDAH BELLIANTHARI, S.Pd.', '2026-03-21 21:14:16'),
(599, 'SELASA', '8', '13:30 - 14:10', 'X 3', 'Pendidikan Agama', 'SURYADI SYARIF, S.Pd.', '2026-03-21 21:14:16'),
(600, 'SELASA', '8', '13:30 - 14:10', 'X 4', 'Pendidikan Agama', 'MUH. ARSYAD HY., S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(601, 'SELASA', '8', '13:30 - 14:10', 'X 5', 'Pendidikan Agama', 'ACHMAD MUFTI AL GALZA S., S.Pd.I.', '2026-03-21 21:14:16'),
(602, 'SELASA', '8', '13:30 - 14:10', 'X 6', 'Sosiologi', 'ADE HANDAYANI, S.Sos., M.Pd., Gr.', '2026-03-21 21:14:16'),
(603, 'SELASA', '8', '13:30 - 14:10', 'X 7', 'Kimia', 'LENNI BAKRI, S.Pd.', '2026-03-21 21:14:16'),
(604, 'SELASA', '8', '13:30 - 14:10', 'X 8', 'Geografi', 'SANTI KUSUMA DEWI, S.Pd.', '2026-03-21 21:14:16'),
(605, 'SELASA', '8', '13:30 - 14:10', 'X 9', 'Bahasa Inggris', 'ARIFIN, S.Pd.', '2026-03-21 21:14:16'),
(606, 'SELASA', '8', '13:30 - 14:10', 'X 10', 'Sejarah', 'ERNIATI, S.Pd.', '2026-03-21 21:14:16'),
(607, 'SELASA', '8', '13:30 - 14:10', 'X 11', 'Matematika', 'HARTIWI PRADIKTA, S.Pd., Gr.', '2026-03-21 21:14:16'),
(608, 'SELASA', '8', '13:30 - 14:10', 'XI 1', 'Bahasa Inggris', 'RISTA JUNIANTI SAHAR, SS.', '2026-03-21 21:14:16'),
(609, 'SELASA', '8', '13:30 - 14:10', 'XI 2', 'Prakarya', 'MUTMAINNAH, S.Pd., M.Biomed.', '2026-03-21 21:14:16'),
(610, 'SELASA', '8', '13:30 - 14:10', 'XI 3', 'Bahasa Inggris (Pilihan)', 'RANDIKA DHANI SYAPUTRA, S.Pd.', '2026-03-21 21:14:16'),
(611, 'SELASA', '8', '13:30 - 14:10', 'XI 4', 'Bahasa Indonesia', 'FITRIANI RAHMAT, S.Pd.', '2026-03-21 21:14:16'),
(612, 'SELASA', '8', '13:30 - 14:10', 'XI 5', 'Biologi (Pilihan)', 'CATHARINA NOVENI DWIDJADJANTI, S.Pd.', '2026-03-21 21:14:16'),
(613, 'SELASA', '8', '13:30 - 14:10', 'XI 6', 'Sejarah', 'ALI TAHIR, S.Pd.', '2026-03-21 21:14:16'),
(614, 'SELASA', '8', '13:30 - 14:10', 'XI 7', 'Pendidikan Pancasila', 'EKA RUSNAENI, S.Pd., Gr.', '2026-03-21 21:14:16'),
(615, 'SELASA', '8', '13:30 - 14:10', 'XI 8', 'Sosiologi (Pilihan)', 'SRI UTAMI WULANSARI, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(616, 'SELASA', '8', '13:30 - 14:10', 'XI 9', 'Ekonomi (Pilihan)', 'MUHAMMAD YUSUF, S.Pd.', '2026-03-21 21:14:16'),
(617, 'SELASA', '8', '13:30 - 14:10', 'XI 10', 'Bahasa Inggris (Pilihan)', 'AFIAH SAKIAH, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(618, 'SELASA', '8', '13:30 - 14:10', 'XI 11', 'Geografi (Pilihan)', 'HALIFUDDIN, S.Pd.', '2026-03-21 21:14:16'),
(619, 'SELASA', '8', '13:30 - 14:10', 'XI 12', 'Prakarya', 'SARWANA, S.Pd.', '2026-03-21 21:14:16'),
(620, 'SELASA', '8', '13:30 - 14:10', 'XII 1', 'Bahasa Indonesia (Pilihan)', 'NUR AKBAR, S.Pd.', '2026-03-21 21:14:16'),
(621, 'SELASA', '8', '13:30 - 14:10', 'XII 2', 'Ekonomi (Pilihan)', 'Dr. Hj. ROSMAWATI, SE.,MA.', '2026-03-21 21:14:16'),
(622, 'SELASA', '8', '13:30 - 14:10', 'XII 3', 'Kimia (Pilihan)', 'ISMAIL LA HASSE, S.Si., S.Pd.', '2026-03-21 21:14:16'),
(623, 'SELASA', '8', '13:30 - 14:10', 'XII 4', 'Kimia (Pilihan)', 'SAPRUDDIN, S.Pd.', '2026-03-21 21:14:16'),
(624, 'SELASA', '8', '13:30 - 14:10', 'XII 5', 'Bahasa Indonesia (Pilihan)', 'HASNA BAKRI, S.Pd.', '2026-03-21 21:14:16'),
(625, 'SELASA', '8', '13:30 - 14:10', 'XII 6', 'Biologi (Pilihan)', 'HAMIDATUSSIFAH, S.Si.', '2026-03-21 21:14:16'),
(626, 'SELASA', '8', '13:30 - 14:10', 'XII 7', 'Informatika (Pilihan)', 'MUHAMMAD IDRIS RIFAI SARRO, S.Si.', '2026-03-21 21:14:16'),
(627, 'SELASA', '8', '13:30 - 14:10', 'XII 8', 'Kimia (Pilihan)', 'MARTHINA SATTU ARI, M.Pd.', '2026-03-21 21:14:16'),
(628, 'SELASA', '8', '13:30 - 14:10', 'XII 9', 'Matematika (Pilihan)', 'NUR QALBI RUSDIN, S.Pd.,M.Pd.', '2026-03-21 21:14:16'),
(629, 'SELASA', '8', '13:30 - 14:10', 'XII 10', 'Biologi (Pilihan)', 'MINDJAH WATY M., S.Si.', '2026-03-21 21:14:16'),
(630, 'SELASA', '8', '13:30 - 14:10', 'XII 11', 'Matematika (Pilihan)', 'HAMKA, S.Si., S.Pd., Gr.', '2026-03-21 21:14:16'),
(631, 'SELASA', '8', '13:30 - 14:10', 'XII 12', 'Geografi (Pilihan)', 'Drs. BAHARUDDIN', '2026-03-21 21:14:16'),
(632, 'SELASA', '9', '14:10 - 14:50', 'X 1', 'Informatika', 'MUH. TRI PRASETIA NUA, S.Pd., Gr.', '2026-03-21 21:14:16'),
(633, 'SELASA', '9', '14:10 - 14:50', 'X 2', 'Ekonomi', 'A. VEBHY INDAH BELLIANTHARI, S.Pd.', '2026-03-21 21:14:16'),
(634, 'SELASA', '9', '14:10 - 14:50', 'X 3', 'Muatan Lokal (BTQ)', 'ACHMAD MUFTI AL GALZA S., S.Pd.I.', '2026-03-21 21:14:16'),
(635, 'SELASA', '9', '14:10 - 14:50', 'X 4', 'Pendidikan Agama', 'MUH. ARSYAD HY., S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(636, 'SELASA', '9', '14:10 - 14:50', 'X 5', 'Seni Budaya', 'ASWAN ANWAR, S.Pd.', '2026-03-21 21:14:16'),
(637, 'SELASA', '9', '14:10 - 14:50', 'X 6', 'Sosiologi', 'ADE HANDAYANI, S.Sos., M.Pd., Gr.', '2026-03-21 21:14:16'),
(638, 'SELASA', '9', '14:10 - 14:50', 'X 7', 'Bahasa Indonesia', 'NURFATWA, S.Pd.', '2026-03-21 21:14:16'),
(639, 'SELASA', '9', '14:10 - 14:50', 'X 8', 'Matematika', 'NURAFNI PEBRIANTY, S.Pd., Gr.', '2026-03-21 21:14:16'),
(640, 'SELASA', '9', '14:10 - 14:50', 'X 9', 'Bahasa Inggris', 'ARIFIN, S.Pd.', '2026-03-21 21:14:16'),
(641, 'SELASA', '9', '14:10 - 14:50', 'X 10', 'Muatan Lokal (BTQ)', 'SURYADI SYARIF, S.Pd.', '2026-03-21 21:14:16'),
(642, 'SELASA', '9', '14:10 - 14:50', 'X 11', 'Pendidikan Pancasila', 'YEKTIE NUR PRAYOGA LM, S.Pd., Gr.', '2026-03-21 21:14:16'),
(643, 'SELASA', '9', '14:10 - 14:50', 'XI 1', 'Prakarya', 'SARWANA, S.Pd.', '2026-03-21 21:14:16'),
(644, 'SELASA', '9', '14:10 - 14:50', 'XI 2', 'Bahasa Inggris (Pilihan)', 'RANDIKA DHANI SYAPUTRA, S.Pd.', '2026-03-21 21:14:16'),
(645, 'SELASA', '9', '14:10 - 14:50', 'XI 3', 'Informatika (Pilihan)', 'MUHAMMAD IDRIS RIFAI SARRO, S.Si.', '2026-03-21 21:14:16'),
(646, 'SELASA', '9', '14:10 - 14:50', 'XI 4', 'Kimia (Pilihan)', 'MARTHINA SATTU ARI, M.Pd.', '2026-03-21 21:14:16'),
(647, 'SELASA', '9', '14:10 - 14:50', 'XI 5', 'Bahasa Inggris', 'RISTA JUNIANTI SAHAR, SS.', '2026-03-21 21:14:16'),
(648, 'SELASA', '9', '14:10 - 14:50', 'XI 6', 'Matematika', 'HARTIWI PRADIKTA, S.Pd., Gr.', '2026-03-21 21:14:16'),
(649, 'SELASA', '9', '14:10 - 14:50', 'XI 7', 'Bahasa Indonesia (Pilihan)', 'NUR AKBAR, S.Pd.', '2026-03-21 21:14:16'),
(650, 'SELASA', '9', '14:10 - 14:50', 'XI 8', 'Sejarah', 'ALI TAHIR, S.Pd.', '2026-03-21 21:14:16'),
(651, 'SELASA', '9', '14:10 - 14:50', 'XI 9', 'Sosiologi (Pilihan)', 'SRI UTAMI WULANSARI, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(652, 'SELASA', '9', '14:10 - 14:50', 'XI 10', 'Seni Budaya', 'ANUGRAYANTI, S.Pd.', '2026-03-21 21:14:16'),
(653, 'SELASA', '9', '14:10 - 14:50', 'XI 11', 'Prakarya', 'MUTMAINNAH, S.Pd., M.Biomed.', '2026-03-21 21:14:16'),
(654, 'SELASA', '9', '14:10 - 14:50', 'XI 12', 'Pendidikan Pancasila', 'EKA RUSNAENI, S.Pd., Gr.', '2026-03-21 21:14:16'),
(655, 'SELASA', '9', '14:10 - 14:50', 'XII 1', 'Bahasa Inggris (Pilihan)', 'AFIAH SAKIAH, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(656, 'SELASA', '9', '14:10 - 14:50', 'XII 2', 'Geografi (Pilihan)', 'SANTI KUSUMA DEWI, S.Pd.', '2026-03-21 21:14:16'),
(657, 'SELASA', '9', '14:10 - 14:50', 'XII 3', 'Matematika (Pilihan)', 'NUR QALBI RUSDIN, S.Pd.,M.Pd.', '2026-03-21 21:14:16'),
(658, 'SELASA', '9', '14:10 - 14:50', 'XII 4', 'Ekonomi (Pilihan)', 'MUHAMMAD YUSUF, S.Pd.', '2026-03-21 21:14:16'),
(659, 'SELASA', '9', '14:10 - 14:50', 'XII 5', 'Geografi (Pilihan)', 'Drs. BAHARUDDIN', '2026-03-21 21:14:16'),
(660, 'SELASA', '9', '14:10 - 14:50', 'XII 6', 'Geografi (Pilihan)', 'HALIFUDDIN, S.Pd.', '2026-03-21 21:14:16'),
(661, 'SELASA', '9', '14:10 - 14:50', 'XII 7', 'Bahasa Indonesia (Pilihan)', 'HASNA BAKRI, S.Pd.', '2026-03-21 21:14:16'),
(662, 'SELASA', '9', '14:10 - 14:50', 'XII 8', 'Biologi (Pilihan)', 'Drs. MUHAMMAD HAMZAH, MM.', '2026-03-21 21:14:16'),
(663, 'SELASA', '9', '14:10 - 14:50', 'XII 9', 'Ekonomi (Pilihan)', 'Dr. Hj. ROSMAWATI, SE.,MA.', '2026-03-21 21:14:16'),
(664, 'SELASA', '9', '14:10 - 14:50', 'XII 10', 'Matematika (Pilihan)', 'HAMKA, S.Si., S.Pd., Gr.', '2026-03-21 21:14:16'),
(665, 'SELASA', '9', '14:10 - 14:50', 'XII 11', 'Biologi (Pilihan)', 'HAMIDATUSSIFAH, S.Si.', '2026-03-21 21:14:16'),
(666, 'SELASA', '9', '14:10 - 14:50', 'XII 12', 'Kimia (Pilihan)', 'SAPRUDDIN, S.Pd.', '2026-03-21 21:14:16'),
(667, 'SELASA', '10', '14:50 - 15:30', 'X 1', 'Informatika', 'MUH. TRI PRASETIA NUA, S.Pd., Gr.', '2026-03-21 21:14:16'),
(668, 'SELASA', '10', '14:50 - 15:30', 'X 2', 'Ekonomi', 'A. VEBHY INDAH BELLIANTHARI, S.Pd.', '2026-03-21 21:14:16'),
(669, 'SELASA', '10', '14:50 - 15:30', 'X 3', 'Muatan Lokal (BTQ)', 'ACHMAD MUFTI AL GALZA S., S.Pd.I.', '2026-03-21 21:14:16'),
(670, 'SELASA', '10', '14:50 - 15:30', 'X 4', 'Pendidikan Agama', 'MUH. ARSYAD HY., S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(671, 'SELASA', '10', '14:50 - 15:30', 'X 5', 'Seni Budaya', 'ASWAN ANWAR, S.Pd.', '2026-03-21 21:14:16'),
(672, 'SELASA', '10', '14:50 - 15:30', 'X 6', 'Sosiologi', 'ADE HANDAYANI, S.Sos., M.Pd., Gr.', '2026-03-21 21:14:16'),
(673, 'SELASA', '10', '14:50 - 15:30', 'X 7', 'Bahasa Indonesia', 'NURFATWA, S.Pd.', '2026-03-21 21:14:16'),
(674, 'SELASA', '10', '14:50 - 15:30', 'X 8', 'Matematika', 'NURAFNI PEBRIANTY, S.Pd., Gr.', '2026-03-21 21:14:16'),
(675, 'SELASA', '10', '14:50 - 15:30', 'X 9', 'Bahasa Inggris', 'ARIFIN, S.Pd.', '2026-03-21 21:14:16'),
(676, 'SELASA', '10', '14:50 - 15:30', 'X 10', 'Muatan Lokal (BTQ)', 'SURYADI SYARIF, S.Pd.', '2026-03-21 21:14:16'),
(677, 'SELASA', '10', '14:50 - 15:30', 'X 11', 'Pendidikan Pancasila', 'YEKTIE NUR PRAYOGA LM, S.Pd., Gr.', '2026-03-21 21:14:16'),
(678, 'SELASA', '10', '14:50 - 15:30', 'XI 1', 'Prakarya', 'SARWANA, S.Pd.', '2026-03-21 21:14:16'),
(679, 'SELASA', '10', '14:50 - 15:30', 'XI 2', 'Bahasa Inggris (Pilihan)', 'RANDIKA DHANI SYAPUTRA, S.Pd.', '2026-03-21 21:14:16'),
(680, 'SELASA', '10', '14:50 - 15:30', 'XI 3', 'Informatika (Pilihan)', 'MUHAMMAD IDRIS RIFAI SARRO, S.Si.', '2026-03-21 21:14:16'),
(681, 'SELASA', '10', '14:50 - 15:30', 'XI 4', 'Kimia (Pilihan)', 'MARTHINA SATTU ARI, M.Pd.', '2026-03-21 21:14:16'),
(682, 'SELASA', '10', '14:50 - 15:30', 'XI 5', 'Bahasa Inggris', 'RISTA JUNIANTI SAHAR, SS.', '2026-03-21 21:14:16'),
(683, 'SELASA', '10', '14:50 - 15:30', 'XI 6', 'Matematika', 'HARTIWI PRADIKTA, S.Pd., Gr.', '2026-03-21 21:14:16'),
(684, 'SELASA', '10', '14:50 - 15:30', 'XI 7', 'Bahasa Indonesia (Pilihan)', 'NUR AKBAR, S.Pd.', '2026-03-21 21:14:16'),
(685, 'SELASA', '10', '14:50 - 15:30', 'XI 8', 'Sejarah', 'ALI TAHIR, S.Pd.', '2026-03-21 21:14:16'),
(686, 'SELASA', '10', '14:50 - 15:30', 'XI 9', 'Sosiologi (Pilihan)', 'SRI UTAMI WULANSARI, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(687, 'SELASA', '10', '14:50 - 15:30', 'XI 10', 'Seni Budaya', 'ANUGRAYANTI, S.Pd.', '2026-03-21 21:14:16'),
(688, 'SELASA', '10', '14:50 - 15:30', 'XI 11', 'Prakarya', 'MUTMAINNAH, S.Pd., M.Biomed.', '2026-03-21 21:14:16'),
(689, 'SELASA', '10', '14:50 - 15:30', 'XI 12', 'Pendidikan Pancasila', 'EKA RUSNAENI, S.Pd., Gr.', '2026-03-21 21:14:16'),
(690, 'SELASA', '10', '14:50 - 15:30', 'XII 1', 'Bahasa Inggris (Pilihan)', 'AFIAH SAKIAH, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(691, 'SELASA', '10', '14:50 - 15:30', 'XII 2', 'Geografi (Pilihan)', 'SANTI KUSUMA DEWI, S.Pd.', '2026-03-21 21:14:16'),
(692, 'SELASA', '10', '14:50 - 15:30', 'XII 3', 'Matematika (Pilihan)', 'NUR QALBI RUSDIN, S.Pd.,M.Pd.', '2026-03-21 21:14:16'),
(693, 'SELASA', '10', '14:50 - 15:30', 'XII 4', 'Ekonomi (Pilihan)', 'MUHAMMAD YUSUF, S.Pd.', '2026-03-21 21:14:16'),
(694, 'SELASA', '10', '14:50 - 15:30', 'XII 5', 'Geografi (Pilihan)', 'Drs. BAHARUDDIN', '2026-03-21 21:14:16'),
(695, 'SELASA', '10', '14:50 - 15:30', 'XII 6', 'Geografi (Pilihan)', 'HALIFUDDIN, S.Pd.', '2026-03-21 21:14:16'),
(696, 'SELASA', '10', '14:50 - 15:30', 'XII 7', 'Bahasa Indonesia (Pilihan)', 'HASNA BAKRI, S.Pd.', '2026-03-21 21:14:16'),
(697, 'SELASA', '10', '14:50 - 15:30', 'XII 8', 'Biologi (Pilihan)', 'Drs. MUHAMMAD HAMZAH, MM.', '2026-03-21 21:14:16'),
(698, 'SELASA', '10', '14:50 - 15:30', 'XII 9', 'Ekonomi (Pilihan)', 'Dr. Hj. ROSMAWATI, SE.,MA.', '2026-03-21 21:14:16'),
(699, 'SELASA', '10', '14:50 - 15:30', 'XII 10', 'Matematika (Pilihan)', 'HAMKA, S.Si., S.Pd., Gr.', '2026-03-21 21:14:16'),
(700, 'SELASA', '10', '14:50 - 15:30', 'XII 11', 'Biologi (Pilihan)', 'HAMIDATUSSIFAH, S.Si.', '2026-03-21 21:14:16'),
(701, 'SELASA', '10', '14:50 - 15:30', 'XII 12', 'Kimia (Pilihan)', 'SAPRUDDIN, S.Pd.', '2026-03-21 21:14:16'),
(702, 'RABU', '1', '07:30 - 08:15', 'X 1', 'Kimia', 'LENNI BAKRI, S.Pd.', '2026-03-21 21:14:16'),
(703, 'RABU', '1', '07:30 - 08:15', 'X 2', 'Fisika', 'EVI YULIATI, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(704, 'RABU', '1', '07:30 - 08:15', 'X 3', 'Matematika', 'NURAFNI PEBRIANTY, S.Pd., Gr.', '2026-03-21 21:14:16'),
(705, 'RABU', '1', '07:30 - 08:15', 'X 4', 'Biologi', 'MINDJAH WATY M., S.Si.', '2026-03-21 21:14:16'),
(706, 'RABU', '1', '07:30 - 08:15', 'X 5', 'Matematika', 'HARTIWI PRADIKTA, S.Pd., Gr.', '2026-03-21 21:14:16'),
(707, 'RABU', '1', '07:30 - 08:15', 'X 6', 'PJOK', 'ASRIANTO, S.Or., Gr.', '2026-03-21 21:14:16'),
(708, 'RABU', '1', '07:30 - 08:15', 'X 7', 'Ekonomi', 'A. VEBHY INDAH BELLIANTHARI, S.Pd.', '2026-03-21 21:14:16'),
(709, 'RABU', '1', '07:30 - 08:15', 'X 8', 'Sejarah', 'ERNIATI, S.Pd.', '2026-03-21 21:14:16'),
(710, 'RABU', '1', '07:30 - 08:15', 'X 9', 'Sosiologi', 'ADE HANDAYANI, S.Sos., M.Pd., Gr.', '2026-03-21 21:14:16'),
(711, 'RABU', '1', '07:30 - 08:15', 'X 10', 'Kimia', 'ISMAIL LA HASSE, S.Si., S.Pd.', '2026-03-21 21:14:16'),
(712, 'RABU', '1', '07:30 - 08:15', 'X 11', 'PJOK', 'DWI SETIAWATI B., S.Pd.', '2026-03-21 21:14:16'),
(713, 'RABU', '1', '07:30 - 08:15', 'XI 1', 'Matematika (Pilihan)', 'NUR QALBI RUSDIN, S.Pd.,M.Pd.', '2026-03-21 21:14:16'),
(714, 'RABU', '1', '07:30 - 08:15', 'XI 2', 'Informatika (Pilihan)', 'MUHAMMAD IDRIS RIFAI SARRO, S.Si.', '2026-03-21 21:14:16'),
(715, 'RABU', '1', '07:30 - 08:15', 'XI 3', 'Bahasa Inggris (Pilihan)', 'RANDIKA DHANI SYAPUTRA, S.Pd.', '2026-03-21 21:14:16'),
(716, 'RABU', '1', '07:30 - 08:15', 'XI 4', 'Pendidikan Pancasila', 'YEKTIE NUR PRAYOGA LM, S.Pd., Gr.', '2026-03-21 21:14:16'),
(717, 'RABU', '1', '07:30 - 08:15', 'XI 5', 'Biologi (Pilihan)', 'CATHARINA NOVENI DWIDJADJANTI, S.Pd.', '2026-03-21 21:14:16'),
(718, 'RABU', '1', '07:30 - 08:15', 'XI 6', 'Bahasa Inggris', 'RISTA JUNIANTI SAHAR, SS.', '2026-03-21 21:14:16'),
(719, 'RABU', '1', '07:30 - 08:15', 'XI 7', 'Bahasa Indonesia', 'A. FERAWATI SUT, S.Pd.', '2026-03-21 21:14:16'),
(720, 'RABU', '1', '07:30 - 08:15', 'XI 8', 'Matematika', 'HAMKA, S.Si., S.Pd., Gr.', '2026-03-21 21:14:16'),
(721, 'RABU', '1', '07:30 - 08:15', 'XI 9', 'Sosiologi (Pilihan)', 'SRI UTAMI WULANSARI, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(722, 'RABU', '1', '07:30 - 08:15', 'XI 10', 'Bahasa Inggris', 'ARIFIN, S.Pd.', '2026-03-21 21:14:16'),
(723, 'RABU', '1', '07:30 - 08:15', 'XI 11', 'Geografi (Pilihan)', 'HALIFUDDIN, S.Pd.', '2026-03-21 21:14:16'),
(724, 'RABU', '1', '07:30 - 08:15', 'XI 12', 'Ekonomi (Pilihan)', 'Dr. Hj. ROSMAWATI, SE.,MA.', '2026-03-21 21:14:16'),
(725, 'RABU', '1', '07:30 - 08:15', 'XII 1', 'Matematika', 'MASNIATI, S.Pd.', '2026-03-21 21:14:16'),
(726, 'RABU', '1', '07:30 - 08:15', 'XII 2', 'Bahasa Indonesia', 'FITRIYANI, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(727, 'RABU', '1', '07:30 - 08:15', 'XII 3', 'PJOK', 'MUH. GILANG FAJARI, S.Pd.', '2026-03-21 21:14:16'),
(728, 'RABU', '1', '07:30 - 08:15', 'XII 4', 'Bahasa Inggris', 'MUHAMMAD SALEH, S.Pd., MM.', '2026-03-21 21:14:16'),
(729, 'RABU', '1', '07:30 - 08:15', 'XII 5', 'Seni Budaya', 'ST. NADIA KHAERATY, S.Pd.', '2026-03-21 21:14:16'),
(730, 'RABU', '1', '07:30 - 08:15', 'XII 6', 'Pendidikan Agama', 'SITTI NAFI, S.Pd.I.', '2026-03-21 21:14:16'),
(731, 'RABU', '1', '07:30 - 08:15', 'XII 7', 'Matematika', 'LUDIHARISTIADI, S.Pd.', '2026-03-21 21:14:16'),
(732, 'RABU', '1', '07:30 - 08:15', 'XII 8', 'PJOK', 'ABD. KAMAL, S.Pd.', '2026-03-21 21:14:16'),
(733, 'RABU', '1', '07:30 - 08:15', 'XII 9', 'Seni Budaya', 'ANUGRAYANTI, S.Pd.', '2026-03-21 21:14:16'),
(734, 'RABU', '1', '07:30 - 08:15', 'XII 10', 'Sejarah', 'DAMAYANTI, S.Pd.', '2026-03-21 21:14:16'),
(735, 'RABU', '1', '07:30 - 08:15', 'XII 11', 'Bahasa Indonesia', 'ST. ROSMAWATI M., S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(736, 'RABU', '1', '07:30 - 08:15', 'XII 12', 'Pendidikan Pancasila', 'EKA RUSNAENI, S.Pd., Gr.', '2026-03-21 21:14:16'),
(737, 'RABU', '2', '08:15 - 09:00', 'X 1', 'Kimia', 'LENNI BAKRI, S.Pd.', '2026-03-21 21:14:16'),
(738, 'RABU', '2', '08:15 - 09:00', 'X 2', 'Fisika', 'EVI YULIATI, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(739, 'RABU', '2', '08:15 - 09:00', 'X 3', 'Matematika', 'NURAFNI PEBRIANTY, S.Pd., Gr.', '2026-03-21 21:14:16'),
(740, 'RABU', '2', '08:15 - 09:00', 'X 4', 'Biologi', 'MINDJAH WATY M., S.Si.', '2026-03-21 21:14:16'),
(741, 'RABU', '2', '08:15 - 09:00', 'X 5', 'Matematika', 'HARTIWI PRADIKTA, S.Pd., Gr.', '2026-03-21 21:14:16'),
(742, 'RABU', '2', '08:15 - 09:00', 'X 6', 'PJOK', 'ASRIANTO, S.Or., Gr.', '2026-03-21 21:14:16'),
(743, 'RABU', '2', '08:15 - 09:00', 'X 7', 'Ekonomi', 'A. VEBHY INDAH BELLIANTHARI, S.Pd.', '2026-03-21 21:14:16'),
(744, 'RABU', '2', '08:15 - 09:00', 'X 8', 'Sejarah', 'ERNIATI, S.Pd.', '2026-03-21 21:14:16'),
(745, 'RABU', '2', '08:15 - 09:00', 'X 9', 'Sosiologi', 'ADE HANDAYANI, S.Sos., M.Pd., Gr.', '2026-03-21 21:14:16'),
(746, 'RABU', '2', '08:15 - 09:00', 'X 10', 'Kimia', 'ISMAIL LA HASSE, S.Si., S.Pd.', '2026-03-21 21:14:16'),
(747, 'RABU', '2', '08:15 - 09:00', 'X 11', 'PJOK', 'DWI SETIAWATI B., S.Pd.', '2026-03-21 21:14:16'),
(748, 'RABU', '2', '08:15 - 09:00', 'XI 1', 'Matematika (Pilihan)', 'NUR QALBI RUSDIN, S.Pd.,M.Pd.', '2026-03-21 21:14:16'),
(749, 'RABU', '2', '08:15 - 09:00', 'XI 2', 'Informatika (Pilihan)', 'MUHAMMAD IDRIS RIFAI SARRO, S.Si.', '2026-03-21 21:14:16'),
(750, 'RABU', '2', '08:15 - 09:00', 'XI 3', 'Bahasa Inggris (Pilihan)', 'RANDIKA DHANI SYAPUTRA, S.Pd.', '2026-03-21 21:14:16'),
(751, 'RABU', '2', '08:15 - 09:00', 'XI 4', 'Pendidikan Pancasila', 'YEKTIE NUR PRAYOGA LM, S.Pd., Gr.', '2026-03-21 21:14:16'),
(752, 'RABU', '2', '08:15 - 09:00', 'XI 5', 'Biologi (Pilihan)', 'CATHARINA NOVENI DWIDJADJANTI, S.Pd.', '2026-03-21 21:14:16'),
(753, 'RABU', '2', '08:15 - 09:00', 'XI 6', 'Bahasa Inggris', 'RISTA JUNIANTI SAHAR, SS.', '2026-03-21 21:14:16'),
(754, 'RABU', '2', '08:15 - 09:00', 'XI 7', 'Bahasa Indonesia', 'A. FERAWATI SUT, S.Pd.', '2026-03-21 21:14:16'),
(755, 'RABU', '2', '08:15 - 09:00', 'XI 8', 'Matematika', 'HAMKA, S.Si., S.Pd., Gr.', '2026-03-21 21:14:16'),
(756, 'RABU', '2', '08:15 - 09:00', 'XI 9', 'Sosiologi (Pilihan)', 'SRI UTAMI WULANSARI, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(757, 'RABU', '2', '08:15 - 09:00', 'XI 10', 'Bahasa Inggris', 'ARIFIN, S.Pd.', '2026-03-21 21:14:16'),
(758, 'RABU', '2', '08:15 - 09:00', 'XI 11', 'Geografi (Pilihan)', 'HALIFUDDIN, S.Pd.', '2026-03-21 21:14:16'),
(759, 'RABU', '2', '08:15 - 09:00', 'XI 12', 'Ekonomi (Pilihan)', 'Dr. Hj. ROSMAWATI, SE.,MA.', '2026-03-21 21:14:16'),
(760, 'RABU', '2', '08:15 - 09:00', 'XII 1', 'Matematika', 'MASNIATI, S.Pd.', '2026-03-21 21:14:16'),
(761, 'RABU', '2', '08:15 - 09:00', 'XII 2', 'Bahasa Indonesia', 'FITRIYANI, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(762, 'RABU', '2', '08:15 - 09:00', 'XII 3', 'PJOK', 'MUH. GILANG FAJARI, S.Pd.', '2026-03-21 21:14:16'),
(763, 'RABU', '2', '08:15 - 09:00', 'XII 4', 'Bahasa Inggris', 'MUHAMMAD SALEH, S.Pd., MM.', '2026-03-21 21:14:16'),
(764, 'RABU', '2', '08:15 - 09:00', 'XII 5', 'Seni Budaya', 'ST. NADIA KHAERATY, S.Pd.', '2026-03-21 21:14:16'),
(765, 'RABU', '2', '08:15 - 09:00', 'XII 6', 'Pendidikan Agama', 'SITTI NAFI, S.Pd.I.', '2026-03-21 21:14:16'),
(766, 'RABU', '2', '08:15 - 09:00', 'XII 7', 'Matematika', 'LUDIHARISTIADI, S.Pd.', '2026-03-21 21:14:16'),
(767, 'RABU', '2', '08:15 - 09:00', 'XII 8', 'PJOK', 'ABD. KAMAL, S.Pd.', '2026-03-21 21:14:16'),
(768, 'RABU', '2', '08:15 - 09:00', 'XII 9', 'Seni Budaya', 'ANUGRAYANTI, S.Pd.', '2026-03-21 21:14:16'),
(769, 'RABU', '2', '08:15 - 09:00', 'XII 10', 'Sejarah', 'DAMAYANTI, S.Pd.', '2026-03-21 21:14:16'),
(770, 'RABU', '2', '08:15 - 09:00', 'XII 11', 'Bahasa Indonesia', 'ST. ROSMAWATI M., S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(771, 'RABU', '2', '08:15 - 09:00', 'XII 12', 'Pendidikan Pancasila', 'EKA RUSNAENI, S.Pd., Gr.', '2026-03-21 21:14:16'),
(772, 'RABU', '3', '09:00 - 09:45', 'X 1', 'Kimia', 'LENNI BAKRI, S.Pd.', '2026-03-21 21:14:16'),
(773, 'RABU', '3', '09:00 - 09:45', 'X 2', 'Fisika', 'EVI YULIATI, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(774, 'RABU', '3', '09:00 - 09:45', 'X 3', 'Seni Budaya', 'ASWAN ANWAR, S.Pd.', '2026-03-21 21:14:16'),
(775, 'RABU', '3', '09:00 - 09:45', 'X 4', 'Biologi', 'MINDJAH WATY M., S.Si.', '2026-03-21 21:14:16'),
(776, 'RABU', '3', '09:00 - 09:45', 'X 5', 'Muatan Lokal (BTQ)', 'SURYADI SYARIF, S.Pd.', '2026-03-21 21:14:16'),
(777, 'RABU', '3', '09:00 - 09:45', 'X 6', 'PJOK', 'ASRIANTO, S.Or., Gr.', '2026-03-21 21:14:16'),
(778, 'RABU', '3', '09:00 - 09:45', 'X 7', 'Ekonomi', 'A. VEBHY INDAH BELLIANTHARI, S.Pd.', '2026-03-21 21:14:16'),
(779, 'RABU', '3', '09:00 - 09:45', 'X 8', 'Sejarah', 'ERNIATI, S.Pd.', '2026-03-21 21:14:16'),
(780, 'RABU', '3', '09:00 - 09:45', 'X 9', 'Sosiologi', 'ADE HANDAYANI, S.Sos., M.Pd., Gr.', '2026-03-21 21:14:16'),
(781, 'RABU', '3', '09:00 - 09:45', 'X 10', 'Kimia', 'ISMAIL LA HASSE, S.Si., S.Pd.', '2026-03-21 21:14:16'),
(782, 'RABU', '3', '09:00 - 09:45', 'X 11', 'PJOK', 'DWI SETIAWATI B., S.Pd.', '2026-03-21 21:14:16'),
(783, 'RABU', '3', '09:00 - 09:45', 'XI 1', 'Matematika (Pilihan)', 'NUR QALBI RUSDIN, S.Pd.,M.Pd.', '2026-03-21 21:14:16'),
(784, 'RABU', '3', '09:00 - 09:45', 'XI 2', 'Informatika (Pilihan)', 'MUHAMMAD IDRIS RIFAI SARRO, S.Si.', '2026-03-21 21:14:16'),
(785, 'RABU', '3', '09:00 - 09:45', 'XI 3', 'Bahasa Inggris (Pilihan)', 'RANDIKA DHANI SYAPUTRA, S.Pd.', '2026-03-21 21:14:16'),
(786, 'RABU', '3', '09:00 - 09:45', 'XI 4', 'Pendidikan Agama', 'YANTI NOVIANTI NAWAWI, S.Pd.I.', '2026-03-21 21:14:16'),
(787, 'RABU', '3', '09:00 - 09:45', 'XI 5', 'Biologi (Pilihan)', 'CATHARINA NOVENI DWIDJADJANTI, S.Pd.', '2026-03-21 21:14:16'),
(788, 'RABU', '3', '09:00 - 09:45', 'XI 6', 'Bahasa Inggris', 'RISTA JUNIANTI SAHAR, SS.', '2026-03-21 21:14:16'),
(789, 'RABU', '3', '09:00 - 09:45', 'XI 7', 'Matematika', 'HAMKA, S.Si., S.Pd., Gr.', '2026-03-21 21:14:16'),
(790, 'RABU', '3', '09:00 - 09:45', 'XI 8', 'Prakarya', 'SARWANA, S.Pd.', '2026-03-21 21:14:16'),
(791, 'RABU', '3', '09:00 - 09:45', 'XI 9', 'Sosiologi (Pilihan)', 'SRI UTAMI WULANSARI, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(792, 'RABU', '3', '09:00 - 09:45', 'XI 10', 'Bahasa Inggris', 'ARIFIN, S.Pd.', '2026-03-21 21:14:16'),
(793, 'RABU', '3', '09:00 - 09:45', 'XI 11', 'Geografi (Pilihan)', 'HALIFUDDIN, S.Pd.', '2026-03-21 21:14:16'),
(794, 'RABU', '3', '09:00 - 09:45', 'XI 12', 'Ekonomi (Pilihan)', 'Dr. Hj. ROSMAWATI, SE.,MA.', '2026-03-21 21:14:16'),
(795, 'RABU', '3', '09:00 - 09:45', 'XII 1', 'Sejarah', 'DAMAYANTI, S.Pd.', '2026-03-21 21:14:16'),
(796, 'RABU', '3', '09:00 - 09:45', 'XII 2', 'Seni Budaya', 'ST. NADIA KHAERATY, S.Pd.', '2026-03-21 21:14:16'),
(797, 'RABU', '3', '09:00 - 09:45', 'XII 3', 'PJOK', 'MUH. GILANG FAJARI, S.Pd.', '2026-03-21 21:14:16'),
(798, 'RABU', '3', '09:00 - 09:45', 'XII 4', 'Bahasa Inggris', 'MUHAMMAD SALEH, S.Pd., MM.', '2026-03-21 21:14:16'),
(799, 'RABU', '3', '09:00 - 09:45', 'XII 5', 'Prakarya', 'MUTMAINNAH, S.Pd., M.Biomed.', '2026-03-21 21:14:16'),
(800, 'RABU', '3', '09:00 - 09:45', 'XII 6', 'Pendidikan Agama', 'SITTI NAFI, S.Pd.I.', '2026-03-21 21:14:16'),
(801, 'RABU', '3', '09:00 - 09:45', 'XII 7', 'Bahasa Indonesia', 'FITRIYANI, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(802, 'RABU', '3', '09:00 - 09:45', 'XII 8', 'PJOK', 'ABD. KAMAL, S.Pd.', '2026-03-21 21:14:16'),
(803, 'RABU', '3', '09:00 - 09:45', 'XII 9', 'Matematika', 'LUDIHARISTIADI, S.Pd.', '2026-03-21 21:14:16'),
(804, 'RABU', '3', '09:00 - 09:45', 'XII 10', 'Matematika', 'MASNIATI, S.Pd.', '2026-03-21 21:14:16'),
(805, 'RABU', '3', '09:00 - 09:45', 'XII 11', 'Pendidikan Pancasila', 'EKA RUSNAENI, S.Pd., Gr.', '2026-03-21 21:14:16'),
(806, 'RABU', '3', '09:00 - 09:45', 'XII 12', 'Bahasa Indonesia', 'ST. ROSMAWATI M., S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(807, 'RABU', '4', '09:45 - 10:30', 'X 1', 'Ekonomi', 'A. VEBHY INDAH BELLIANTHARI, S.Pd.', '2026-03-21 21:14:16'),
(808, 'RABU', '4', '09:45 - 10:30', 'X 2', 'Pendidikan Agama', 'SURYADI SYARIF, S.Pd.', '2026-03-21 21:14:16'),
(809, 'RABU', '4', '09:45 - 10:30', 'X 3', 'Geografi', 'HALIFUDDIN, S.Pd.', '2026-03-21 21:14:16'),
(810, 'RABU', '4', '09:45 - 10:30', 'X 4', 'Kimia', 'LENNI BAKRI, S.Pd.', '2026-03-21 21:14:16'),
(811, 'RABU', '4', '09:45 - 10:30', 'X 5', 'PJOK', 'ASRIANTO, S.Or., Gr.', '2026-03-21 21:14:16'),
(812, 'RABU', '4', '09:45 - 10:30', 'X 6', 'Sejarah', 'ERNIATI, S.Pd.', '2026-03-21 21:14:16'),
(813, 'RABU', '4', '09:45 - 10:30', 'X 7', 'Sosiologi', 'ADE HANDAYANI, S.Sos., M.Pd., Gr.', '2026-03-21 21:14:16'),
(814, 'RABU', '4', '09:45 - 10:30', 'X 8', 'Muatan Lokal (BTQ)', 'MUH. ARSYAD HY., S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(815, 'RABU', '4', '09:45 - 10:30', 'X 9', 'Informatika', 'MUH. TRI PRASETIA NUA, S.Pd., Gr.', '2026-03-21 21:14:16'),
(816, 'RABU', '4', '09:45 - 10:30', 'X 10', 'Biologi', 'MINDJAH WATY M., S.Si.', '2026-03-21 21:14:16'),
(817, 'RABU', '4', '09:45 - 10:30', 'X 11', 'Biologi', 'HAMIDATUSSIFAH, S.Si.', '2026-03-21 21:14:16'),
(818, 'RABU', '4', '09:45 - 10:30', 'XI 1', 'Bahasa Indonesia', 'HASNA BAKRI, S.Pd.', '2026-03-21 21:14:16'),
(819, 'RABU', '4', '09:45 - 10:30', 'XI 2', 'Bahasa Inggris (Pilihan)', 'RANDIKA DHANI SYAPUTRA, S.Pd.', '2026-03-21 21:14:16'),
(820, 'RABU', '4', '09:45 - 10:30', 'XI 3', 'Informatika (Pilihan)', 'MUHAMMAD IDRIS RIFAI SARRO, S.Si.', '2026-03-21 21:14:16'),
(821, 'RABU', '4', '09:45 - 10:30', 'XI 4', 'Sejarah', 'ALI TAHIR, S.Pd.', '2026-03-21 21:14:16'),
(822, 'RABU', '4', '09:45 - 10:30', 'XI 5', 'PJOK', 'DWI SETIAWATI B., S.Pd.', '2026-03-21 21:14:16'),
(823, 'RABU', '4', '09:45 - 10:30', 'XI 6', 'Pendidikan Pancasila', 'YEKTIE NUR PRAYOGA LM, S.Pd., Gr.', '2026-03-21 21:14:16'),
(824, 'RABU', '4', '09:45 - 10:30', 'XI 7', 'Bahasa Indonesia (Pilihan)', 'NUR AKBAR, S.Pd.', '2026-03-21 21:14:16'),
(825, 'RABU', '4', '09:45 - 10:30', 'XI 8', 'Sosiologi (Pilihan)', 'SRI UTAMI WULANSARI, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(826, 'RABU', '4', '09:45 - 10:30', 'XI 9', 'Ekonomi (Pilihan)', 'MUHAMMAD YUSUF, S.Pd.', '2026-03-21 21:14:16'),
(827, 'RABU', '4', '09:45 - 10:30', 'XI 10', 'Bahasa Inggris (Pilihan)', 'AFIAH SAKIAH, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(828, 'RABU', '4', '09:45 - 10:30', 'XI 11', 'Ekonomi (Pilihan)', 'Dr. Hj. ROSMAWATI, SE.,MA.', '2026-03-21 21:14:16'),
(829, 'RABU', '4', '09:45 - 10:30', 'XI 12', 'Geografi (Pilihan)', 'Drs. BAHARUDDIN', '2026-03-21 21:14:16'),
(830, 'RABU', '4', '09:45 - 10:30', 'XII 1', 'Sejarah', 'DAMAYANTI, S.Pd.', '2026-03-21 21:14:16'),
(831, 'RABU', '4', '09:45 - 10:30', 'XII 2', 'Seni Budaya', 'ST. NADIA KHAERATY, S.Pd.', '2026-03-21 21:14:16'),
(832, 'RABU', '4', '09:45 - 10:30', 'XII 3', 'Bahasa Inggris', 'MUHAMMAD SALEH, S.Pd., MM.', '2026-03-21 21:14:16'),
(833, 'RABU', '4', '09:45 - 10:30', 'XII 4', 'PJOK', 'MUH. GILANG FAJARI, S.Pd.', '2026-03-21 21:14:16'),
(834, 'RABU', '4', '09:45 - 10:30', 'XII 5', 'Matematika', 'LILIES RATNA RIYANTINI, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(835, 'RABU', '4', '09:45 - 10:30', 'XII 6', 'PJOK', 'ABD. KAMAL, S.Pd.', '2026-03-21 21:14:16'),
(836, 'RABU', '4', '09:45 - 10:30', 'XII 7', 'Bahasa Indonesia', 'FITRIYANI, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(837, 'RABU', '4', '09:45 - 10:30', 'XII 8', 'Pendidikan Agama', 'YANTI NOVIANTI NAWAWI, S.Pd.I.', '2026-03-21 21:14:16'),
(838, 'RABU', '4', '09:45 - 10:30', 'XII 9', 'Matematika', 'LUDIHARISTIADI, S.Pd.', '2026-03-21 21:14:16'),
(839, 'RABU', '4', '09:45 - 10:30', 'XII 10', 'Matematika', 'MASNIATI, S.Pd.', '2026-03-21 21:14:16'),
(840, 'RABU', '4', '09:45 - 10:30', 'XII 11', 'Pendidikan Pancasila', 'EKA RUSNAENI, S.Pd., Gr.', '2026-03-21 21:14:16'),
(841, 'RABU', '4', '09:45 - 10:30', 'XII 12', 'Bahasa Indonesia', 'ST. ROSMAWATI M., S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(842, 'RABU', '5', '10:45 - 11:30', 'X 1', 'Ekonomi', 'A. VEBHY INDAH BELLIANTHARI, S.Pd.', '2026-03-21 21:14:16'),
(843, 'RABU', '5', '10:45 - 11:30', 'X 2', 'Pendidikan Agama', 'SURYADI SYARIF, S.Pd.', '2026-03-21 21:14:16'),
(844, 'RABU', '5', '10:45 - 11:30', 'X 3', 'Geografi', 'HALIFUDDIN, S.Pd.', '2026-03-21 21:14:16'),
(845, 'RABU', '5', '10:45 - 11:30', 'X 4', 'Kimia', 'LENNI BAKRI, S.Pd.', '2026-03-21 21:14:16'),
(846, 'RABU', '5', '10:45 - 11:30', 'X 5', 'PJOK', 'ASRIANTO, S.Or., Gr.', '2026-03-21 21:14:16'),
(847, 'RABU', '5', '10:45 - 11:30', 'X 6', 'Sejarah', 'ERNIATI, S.Pd.', '2026-03-21 21:14:16'),
(848, 'RABU', '5', '10:45 - 11:30', 'X 7', 'Sosiologi', 'ADE HANDAYANI, S.Sos., M.Pd., Gr.', '2026-03-21 21:14:16'),
(849, 'RABU', '5', '10:45 - 11:30', 'X 8', 'Bahasa Indonesia', 'A. FERAWATI SUT, S.Pd.', '2026-03-21 21:14:16'),
(850, 'RABU', '5', '10:45 - 11:30', 'X 9', 'Informatika', 'MUH. TRI PRASETIA NUA, S.Pd., Gr.', '2026-03-21 21:14:16'),
(851, 'RABU', '5', '10:45 - 11:30', 'X 10', 'Biologi', 'MINDJAH WATY M., S.Si.', '2026-03-21 21:14:16'),
(852, 'RABU', '5', '10:45 - 11:30', 'X 11', 'Biologi', 'HAMIDATUSSIFAH, S.Si.', '2026-03-21 21:14:16'),
(853, 'RABU', '5', '10:45 - 11:30', 'XI 1', 'Bahasa Indonesia', 'HASNA BAKRI, S.Pd.', '2026-03-21 21:14:16'),
(854, 'RABU', '5', '10:45 - 11:30', 'XI 2', 'Bahasa Inggris (Pilihan)', 'RANDIKA DHANI SYAPUTRA, S.Pd.', '2026-03-21 21:14:16'),
(855, 'RABU', '5', '10:45 - 11:30', 'XI 3', 'Informatika (Pilihan)', 'MUHAMMAD IDRIS RIFAI SARRO, S.Si.', '2026-03-21 21:14:16'),
(856, 'RABU', '5', '10:45 - 11:30', 'XI 4', 'Sejarah', 'ALI TAHIR, S.Pd.', '2026-03-21 21:14:16'),
(857, 'RABU', '5', '10:45 - 11:30', 'XI 5', 'PJOK', 'DWI SETIAWATI B., S.Pd.', '2026-03-21 21:14:16'),
(858, 'RABU', '5', '10:45 - 11:30', 'XI 6', 'Pendidikan Pancasila', 'YEKTIE NUR PRAYOGA LM, S.Pd., Gr.', '2026-03-21 21:14:16'),
(859, 'RABU', '5', '10:45 - 11:30', 'XI 7', 'Bahasa Indonesia (Pilihan)', 'NUR AKBAR, S.Pd.', '2026-03-21 21:14:16'),
(860, 'RABU', '5', '10:45 - 11:30', 'XI 8', 'Sosiologi (Pilihan)', 'SRI UTAMI WULANSARI, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(861, 'RABU', '5', '10:45 - 11:30', 'XI 9', 'Ekonomi (Pilihan)', 'MUHAMMAD YUSUF, S.Pd.', '2026-03-21 21:14:16'),
(862, 'RABU', '5', '10:45 - 11:30', 'XI 10', 'Bahasa Inggris (Pilihan)', 'AFIAH SAKIAH, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(863, 'RABU', '5', '10:45 - 11:30', 'XI 11', 'Ekonomi (Pilihan)', 'Dr. Hj. ROSMAWATI, SE.,MA.', '2026-03-21 21:14:16'),
(864, 'RABU', '5', '10:45 - 11:30', 'XI 12', 'Geografi (Pilihan)', 'Drs. BAHARUDDIN', '2026-03-21 21:14:16'),
(865, 'RABU', '5', '10:45 - 11:30', 'XII 1', 'Bahasa Indonesia', 'FITRIYANI, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(866, 'RABU', '5', '10:45 - 11:30', 'XII 2', 'Sejarah', 'DAMAYANTI, S.Pd.', '2026-03-21 21:14:16'),
(867, 'RABU', '5', '10:45 - 11:30', 'XII 3', 'Bahasa Inggris', 'MUHAMMAD SALEH, S.Pd., MM.', '2026-03-21 21:14:16'),
(868, 'RABU', '5', '10:45 - 11:30', 'XII 4', 'PJOK', 'MUH. GILANG FAJARI, S.Pd.', '2026-03-21 21:14:16'),
(869, 'RABU', '5', '10:45 - 11:30', 'XII 5', 'Matematika', 'LILIES RATNA RIYANTINI, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(870, 'RABU', '5', '10:45 - 11:30', 'XII 6', 'PJOK', 'ABD. KAMAL, S.Pd.', '2026-03-21 21:14:16'),
(871, 'RABU', '5', '10:45 - 11:30', 'XII 7', 'Pendidikan Pancasila', 'EKA RUSNAENI, S.Pd., Gr.', '2026-03-21 21:14:16'),
(872, 'RABU', '5', '10:45 - 11:30', 'XII 8', 'Pendidikan Agama', 'YANTI NOVIANTI NAWAWI, S.Pd.I.', '2026-03-21 21:14:16'),
(873, 'RABU', '5', '10:45 - 11:30', 'XII 9', 'Bahasa Indonesia', 'ST. ROSMAWATI M., S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(874, 'RABU', '5', '10:45 - 11:30', 'XII 10', 'Seni Budaya', 'ANUGRAYANTI, S.Pd.', '2026-03-21 21:14:16'),
(875, 'RABU', '5', '10:45 - 11:30', 'XII 11', 'Prakarya', 'MUTMAINNAH, S.Pd., M.Biomed.', '2026-03-21 21:14:16'),
(876, 'RABU', '5', '10:45 - 11:30', 'XII 12', 'Pendidikan Agama', 'SITTI NAFI, S.Pd.I.', '2026-03-21 21:14:16'),
(877, 'RABU', '6', '11:30 - 12:15', 'X 1', 'Ekonomi', 'A. VEBHY INDAH BELLIANTHARI, S.Pd.', '2026-03-21 21:14:16'),
(878, 'RABU', '6', '11:30 - 12:15', 'X 2', 'Pendidikan Agama', 'SURYADI SYARIF, S.Pd.', '2026-03-21 21:14:16'),
(879, 'RABU', '6', '11:30 - 12:15', 'X 3', 'Geografi', 'HALIFUDDIN, S.Pd.', '2026-03-21 21:14:16'),
(880, 'RABU', '6', '11:30 - 12:15', 'X 4', 'Kimia', 'LENNI BAKRI, S.Pd.', '2026-03-21 21:14:16'),
(881, 'RABU', '6', '11:30 - 12:15', 'X 5', 'PJOK', 'ASRIANTO, S.Or., Gr.', '2026-03-21 21:14:16'),
(882, 'RABU', '6', '11:30 - 12:15', 'X 6', 'Sejarah', 'ERNIATI, S.Pd.', '2026-03-21 21:14:16'),
(883, 'RABU', '6', '11:30 - 12:15', 'X 7', 'Sosiologi', 'ADE HANDAYANI, S.Sos., M.Pd., Gr.', '2026-03-21 21:14:16'),
(884, 'RABU', '6', '11:30 - 12:15', 'X 8', 'Bahasa Indonesia', 'A. FERAWATI SUT, S.Pd.', '2026-03-21 21:14:16'),
(885, 'RABU', '6', '11:30 - 12:15', 'X 9', 'Muatan Lokal (BTQ)', 'ACHMAD MUFTI AL GALZA S., S.Pd.I.', '2026-03-21 21:14:16'),
(886, 'RABU', '6', '11:30 - 12:15', 'X 10', 'Biologi', 'MINDJAH WATY M., S.Si.', '2026-03-21 21:14:16'),
(887, 'RABU', '6', '11:30 - 12:15', 'X 11', 'Biologi', 'HAMIDATUSSIFAH, S.Si.', '2026-03-21 21:14:16'),
(888, 'RABU', '6', '11:30 - 12:15', 'XI 1', 'Bahasa Indonesia', 'HASNA BAKRI, S.Pd.', '2026-03-21 21:14:16'),
(889, 'RABU', '6', '11:30 - 12:15', 'XI 2', 'Bahasa Inggris (Pilihan)', 'RANDIKA DHANI SYAPUTRA, S.Pd.', '2026-03-21 21:14:16'),
(890, 'RABU', '6', '11:30 - 12:15', 'XI 3', 'Informatika (Pilihan)', 'MUHAMMAD IDRIS RIFAI SARRO, S.Si.', '2026-03-21 21:14:16'),
(891, 'RABU', '6', '11:30 - 12:15', 'XI 4', 'Matematika', 'NUR QALBI RUSDIN, S.Pd.,M.Pd.', '2026-03-21 21:14:16');
INSERT INTO `schedules` (`id`, `hari`, `jam_ke`, `waktu`, `kelas`, `mata_pelajaran`, `nama_guru`, `created_at`) VALUES
(892, 'RABU', '6', '11:30 - 12:15', 'XI 5', 'PJOK', 'DWI SETIAWATI B., S.Pd.', '2026-03-21 21:14:16'),
(893, 'RABU', '6', '11:30 - 12:15', 'XI 6', 'Biologi (Pilihan)', 'CATHARINA NOVENI DWIDJADJANTI, S.Pd.', '2026-03-21 21:14:16'),
(894, 'RABU', '6', '11:30 - 12:15', 'XI 7', 'Bahasa Indonesia (Pilihan)', 'NUR AKBAR, S.Pd.', '2026-03-21 21:14:16'),
(895, 'RABU', '6', '11:30 - 12:15', 'XI 8', 'Sosiologi (Pilihan)', 'SRI UTAMI WULANSARI, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(896, 'RABU', '6', '11:30 - 12:15', 'XI 9', 'Ekonomi (Pilihan)', 'MUHAMMAD YUSUF, S.Pd.', '2026-03-21 21:14:16'),
(897, 'RABU', '6', '11:30 - 12:15', 'XI 10', 'Bahasa Inggris (Pilihan)', 'AFIAH SAKIAH, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(898, 'RABU', '6', '11:30 - 12:15', 'XI 11', 'Ekonomi (Pilihan)', 'Dr. Hj. ROSMAWATI, SE.,MA.', '2026-03-21 21:14:16'),
(899, 'RABU', '6', '11:30 - 12:15', 'XI 12', 'Geografi (Pilihan)', 'Drs. BAHARUDDIN', '2026-03-21 21:14:16'),
(900, 'RABU', '6', '11:30 - 12:15', 'XII 1', 'Bahasa Indonesia', 'FITRIYANI, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(901, 'RABU', '6', '11:30 - 12:15', 'XII 2', 'Sejarah', 'DAMAYANTI, S.Pd.', '2026-03-21 21:14:16'),
(902, 'RABU', '6', '11:30 - 12:15', 'XII 3', 'Bahasa Inggris', 'MUHAMMAD SALEH, S.Pd., MM.', '2026-03-21 21:14:16'),
(903, 'RABU', '6', '11:30 - 12:15', 'XII 4', 'PJOK', 'MUH. GILANG FAJARI, S.Pd.', '2026-03-21 21:14:16'),
(904, 'RABU', '6', '11:30 - 12:15', 'XII 5', 'Matematika', 'LILIES RATNA RIYANTINI, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(905, 'RABU', '6', '11:30 - 12:15', 'XII 6', 'PJOK', 'ABD. KAMAL, S.Pd.', '2026-03-21 21:14:16'),
(906, 'RABU', '6', '11:30 - 12:15', 'XII 7', 'Pendidikan Pancasila', 'EKA RUSNAENI, S.Pd., Gr.', '2026-03-21 21:14:16'),
(907, 'RABU', '6', '11:30 - 12:15', 'XII 8', 'Pendidikan Agama', 'YANTI NOVIANTI NAWAWI, S.Pd.I.', '2026-03-21 21:14:16'),
(908, 'RABU', '6', '11:30 - 12:15', 'XII 9', 'Bahasa Indonesia', 'ST. ROSMAWATI M., S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(909, 'RABU', '6', '11:30 - 12:15', 'XII 10', 'Seni Budaya', 'ANUGRAYANTI, S.Pd.', '2026-03-21 21:14:16'),
(910, 'RABU', '6', '11:30 - 12:15', 'XII 11', 'Prakarya', 'MUTMAINNAH, S.Pd., M.Biomed.', '2026-03-21 21:14:16'),
(911, 'RABU', '6', '11:30 - 12:15', 'XII 12', 'Pendidikan Agama', 'SITTI NAFI, S.Pd.I.', '2026-03-21 21:14:16'),
(912, 'RABU', '7', '12:50 - 13:30', 'X 1', 'Pendidikan Agama', 'SURYADI SYARIF, S.Pd.', '2026-03-21 21:14:16'),
(913, 'RABU', '7', '12:50 - 13:30', 'X 2', 'Pendidikan Pancasila', 'YEKTIE NUR PRAYOGA LM, S.Pd., Gr.', '2026-03-21 21:14:16'),
(914, 'RABU', '7', '12:50 - 13:30', 'X 3', 'Informatika', 'MUH. TRI PRASETIA NUA, S.Pd., Gr.', '2026-03-21 21:14:16'),
(915, 'RABU', '7', '12:50 - 13:30', 'X 4', 'Ekonomi', 'A. VEBHY INDAH BELLIANTHARI, S.Pd.', '2026-03-21 21:14:16'),
(916, 'RABU', '7', '12:50 - 13:30', 'X 5', 'Bahasa Indonesia', 'NURFATWA, S.Pd.', '2026-03-21 21:14:16'),
(917, 'RABU', '7', '12:50 - 13:30', 'X 6', 'Matematika', 'SYAMSIAR, S.Pd.', '2026-03-21 21:14:16'),
(918, 'RABU', '7', '12:50 - 13:30', 'X 7', 'Informatika', 'RIZA RISKY YULIANTI, S.Pd., Gr.', '2026-03-21 21:14:16'),
(919, 'RABU', '7', '12:50 - 13:30', 'X 8', 'Geografi', 'SANTI KUSUMA DEWI, S.Pd.', '2026-03-21 21:14:16'),
(920, 'RABU', '7', '12:50 - 13:30', 'X 9', 'Bahasa Indonesia', 'A. FERAWATI SUT, S.Pd.', '2026-03-21 21:14:16'),
(921, 'RABU', '7', '12:50 - 13:30', 'X 10', 'Seni Budaya', 'ASWAN ANWAR, S.Pd.', '2026-03-21 21:14:16'),
(922, 'RABU', '7', '12:50 - 13:30', 'X 11', 'Bahasa Indonesia', 'NUR AKBAR, S.Pd.', '2026-03-21 21:14:16'),
(923, 'RABU', '7', '12:50 - 13:30', 'XI 1', 'Fisika (Pilihan)', 'EVI YULIATI, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(924, 'RABU', '7', '12:50 - 13:30', 'XI 2', 'Sejarah', 'ALI TAHIR, S.Pd.', '2026-03-21 21:14:16'),
(925, 'RABU', '7', '12:50 - 13:30', 'XI 3', 'Matematika', 'NURAFNI PEBRIANTY, S.Pd., Gr.', '2026-03-21 21:14:16'),
(926, 'RABU', '7', '12:50 - 13:30', 'XI 4', 'Matematika', 'NUR QALBI RUSDIN, S.Pd.,M.Pd.', '2026-03-21 21:14:16'),
(927, 'RABU', '7', '12:50 - 13:30', 'XI 5', 'Pendidikan Pancasila', 'EKA RUSNAENI, S.Pd., Gr.', '2026-03-21 21:14:16'),
(928, 'RABU', '7', '12:50 - 13:30', 'XI 6', 'Biologi (Pilihan)', 'CATHARINA NOVENI DWIDJADJANTI, S.Pd.', '2026-03-21 21:14:16'),
(929, 'RABU', '7', '12:50 - 13:30', 'XI 7', 'Pendidikan Agama', 'MUH. ARSYAD HY., S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(930, 'RABU', '7', '12:50 - 13:30', 'XI 8', 'Bahasa Inggris', 'ARIFIN, S.Pd.', '2026-03-21 21:14:16'),
(931, 'RABU', '7', '12:50 - 13:30', 'XI 9', 'Prakarya', 'SARWANA, S.Pd.', '2026-03-21 21:14:16'),
(932, 'RABU', '7', '12:50 - 13:30', 'XI 10', 'Geografi (Pilihan)', 'HALIFUDDIN, S.Pd.', '2026-03-21 21:14:16'),
(933, 'RABU', '7', '12:50 - 13:30', 'XI 11', 'Pendidikan Agama', 'ACHMAD MUFTI AL GALZA S., S.Pd.I.', '2026-03-21 21:14:16'),
(934, 'RABU', '7', '12:50 - 13:30', 'XI 12', 'Sosiologi (Pilihan)', 'SRI UTAMI WULANSARI, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(935, 'RABU', '7', '12:50 - 13:30', 'XII 1', 'Matematika (Pilihan)', 'HARTIWI PRADIKTA, S.Pd., Gr.', '2026-03-21 21:14:16'),
(936, 'RABU', '7', '12:50 - 13:30', 'XII 2', 'Biologi (Pilihan)', 'Drs. MUHAMMAD HAMZAH, MM.', '2026-03-21 21:14:16'),
(937, 'RABU', '7', '12:50 - 13:30', 'XII 3', 'Informatika (Pilihan)', 'MUHAMMAD IDRIS RIFAI SARRO, S.Si.', '2026-03-21 21:14:16'),
(938, 'RABU', '7', '12:50 - 13:30', 'XII 4', 'Biologi (Pilihan)', 'HAMIDATUSSIFAH, S.Si.', '2026-03-21 21:14:16'),
(939, 'RABU', '7', '12:50 - 13:30', 'XII 5', 'Kimia (Pilihan)', 'SAPRUDDIN, S.Pd.', '2026-03-21 21:14:16'),
(940, 'RABU', '7', '12:50 - 13:30', 'XII 6', 'Bahasa Indonesia (Pilihan)', 'HASNA BAKRI, S.Pd.', '2026-03-21 21:14:16'),
(941, 'RABU', '7', '12:50 - 13:30', 'XII 7', 'Ekonomi (Pilihan)', 'MUHAMMAD YUSUF, S.Pd.', '2026-03-21 21:14:16'),
(942, 'RABU', '7', '12:50 - 13:30', 'XII 8', 'Bahasa Indonesia (Pilihan)', 'ST. ROSMAWATI M., S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(943, 'RABU', '7', '12:50 - 13:30', 'XII 9', 'Fisika (Pilihan)', 'SAPPEWATI, S.Pd.', '2026-03-21 21:14:16'),
(944, 'RABU', '7', '12:50 - 13:30', 'XII 10', 'Ekonomi (Pilihan)', 'Dr. Hj. ROSMAWATI, SE.,MA.', '2026-03-21 21:14:16'),
(945, 'RABU', '7', '12:50 - 13:30', 'XII 11', 'Bahasa Inggris (Pilihan)', 'AFIAH SAKIAH, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(946, 'RABU', '7', '12:50 - 13:30', 'XII 12', 'Matematika (Pilihan)', 'HAMKA, S.Si., S.Pd., Gr.', '2026-03-21 21:14:16'),
(947, 'RABU', '8', '13:30 - 14:10', 'X 1', 'Geografi', 'SANTI KUSUMA DEWI, S.Pd.', '2026-03-21 21:14:16'),
(948, 'RABU', '8', '13:30 - 14:10', 'X 2', 'Pendidikan Pancasila', 'YEKTIE NUR PRAYOGA LM, S.Pd., Gr.', '2026-03-21 21:14:16'),
(949, 'RABU', '8', '13:30 - 14:10', 'X 3', 'Informatika', 'MUH. TRI PRASETIA NUA, S.Pd., Gr.', '2026-03-21 21:14:16'),
(950, 'RABU', '8', '13:30 - 14:10', 'X 4', 'Ekonomi', 'A. VEBHY INDAH BELLIANTHARI, S.Pd.', '2026-03-21 21:14:16'),
(951, 'RABU', '8', '13:30 - 14:10', 'X 5', 'Bahasa Indonesia', 'NURFATWA, S.Pd.', '2026-03-21 21:14:16'),
(952, 'RABU', '8', '13:30 - 14:10', 'X 6', 'Matematika', 'SYAMSIAR, S.Pd.', '2026-03-21 21:14:16'),
(953, 'RABU', '8', '13:30 - 14:10', 'X 7', 'Bahasa Inggris', 'ARIFIN, S.Pd.', '2026-03-21 21:14:16'),
(954, 'RABU', '8', '13:30 - 14:10', 'X 8', 'Sosiologi', 'ADE HANDAYANI, S.Sos., M.Pd., Gr.', '2026-03-21 21:14:16'),
(955, 'RABU', '8', '13:30 - 14:10', 'X 9', 'Bahasa Indonesia', 'A. FERAWATI SUT, S.Pd.', '2026-03-21 21:14:16'),
(956, 'RABU', '8', '13:30 - 14:10', 'X 10', 'Seni Budaya', 'ASWAN ANWAR, S.Pd.', '2026-03-21 21:14:16'),
(957, 'RABU', '8', '13:30 - 14:10', 'X 11', 'Bahasa Indonesia', 'NUR AKBAR, S.Pd.', '2026-03-21 21:14:16'),
(958, 'RABU', '8', '13:30 - 14:10', 'XI 1', 'Fisika (Pilihan)', 'EVI YULIATI, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(959, 'RABU', '8', '13:30 - 14:10', 'XI 2', 'Sejarah', 'ALI TAHIR, S.Pd.', '2026-03-21 21:14:16'),
(960, 'RABU', '8', '13:30 - 14:10', 'XI 3', 'Matematika', 'NURAFNI PEBRIANTY, S.Pd., Gr.', '2026-03-21 21:14:16'),
(961, 'RABU', '8', '13:30 - 14:10', 'XI 4', 'Matematika', 'NUR QALBI RUSDIN, S.Pd.,M.Pd.', '2026-03-21 21:14:16'),
(962, 'RABU', '8', '13:30 - 14:10', 'XI 5', 'Pendidikan Pancasila', 'EKA RUSNAENI, S.Pd., Gr.', '2026-03-21 21:14:16'),
(963, 'RABU', '8', '13:30 - 14:10', 'XI 6', 'Biologi (Pilihan)', 'CATHARINA NOVENI DWIDJADJANTI, S.Pd.', '2026-03-21 21:14:16'),
(964, 'RABU', '8', '13:30 - 14:10', 'XI 7', 'Bahasa Inggris', 'RISTA JUNIANTI SAHAR, SS.', '2026-03-21 21:14:16'),
(965, 'RABU', '8', '13:30 - 14:10', 'XI 8', 'Bahasa Inggris (Pilihan)', 'RANDIKA DHANI SYAPUTRA, S.Pd.', '2026-03-21 21:14:16'),
(966, 'RABU', '8', '13:30 - 14:10', 'XI 9', 'Prakarya', 'SARWANA, S.Pd.', '2026-03-21 21:14:16'),
(967, 'RABU', '8', '13:30 - 14:10', 'XI 10', 'Geografi (Pilihan)', 'HALIFUDDIN, S.Pd.', '2026-03-21 21:14:16'),
(968, 'RABU', '8', '13:30 - 14:10', 'XI 11', 'Pendidikan Agama', 'ACHMAD MUFTI AL GALZA S., S.Pd.I.', '2026-03-21 21:14:16'),
(969, 'RABU', '8', '13:30 - 14:10', 'XI 12', 'Sosiologi (Pilihan)', 'SRI UTAMI WULANSARI, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(970, 'RABU', '8', '13:30 - 14:10', 'XII 1', 'Matematika (Pilihan)', 'HARTIWI PRADIKTA, S.Pd., Gr.', '2026-03-21 21:14:16'),
(971, 'RABU', '8', '13:30 - 14:10', 'XII 2', 'Biologi (Pilihan)', 'Drs. MUHAMMAD HAMZAH, MM.', '2026-03-21 21:14:16'),
(972, 'RABU', '8', '13:30 - 14:10', 'XII 3', 'Informatika (Pilihan)', 'MUHAMMAD IDRIS RIFAI SARRO, S.Si.', '2026-03-21 21:14:16'),
(973, 'RABU', '8', '13:30 - 14:10', 'XII 4', 'Biologi (Pilihan)', 'HAMIDATUSSIFAH, S.Si.', '2026-03-21 21:14:16'),
(974, 'RABU', '8', '13:30 - 14:10', 'XII 5', 'Kimia (Pilihan)', 'SAPRUDDIN, S.Pd.', '2026-03-21 21:14:16'),
(975, 'RABU', '8', '13:30 - 14:10', 'XII 6', 'Bahasa Indonesia (Pilihan)', 'HASNA BAKRI, S.Pd.', '2026-03-21 21:14:16'),
(976, 'RABU', '8', '13:30 - 14:10', 'XII 7', 'Ekonomi (Pilihan)', 'MUHAMMAD YUSUF, S.Pd.', '2026-03-21 21:14:16'),
(977, 'RABU', '8', '13:30 - 14:10', 'XII 8', 'Bahasa Indonesia (Pilihan)', 'ST. ROSMAWATI M., S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(978, 'RABU', '8', '13:30 - 14:10', 'XII 9', 'Fisika (Pilihan)', 'SAPPEWATI, S.Pd.', '2026-03-21 21:14:16'),
(979, 'RABU', '8', '13:30 - 14:10', 'XII 10', 'Ekonomi (Pilihan)', 'Dr. Hj. ROSMAWATI, SE.,MA.', '2026-03-21 21:14:16'),
(980, 'RABU', '8', '13:30 - 14:10', 'XII 11', 'Bahasa Inggris (Pilihan)', 'AFIAH SAKIAH, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(981, 'RABU', '8', '13:30 - 14:10', 'XII 12', 'Matematika (Pilihan)', 'HAMKA, S.Si., S.Pd., Gr.', '2026-03-21 21:14:16'),
(982, 'RABU', '9', '14:10 - 14:50', 'X 1', 'Geografi', 'SANTI KUSUMA DEWI, S.Pd.', '2026-03-21 21:14:16'),
(983, 'RABU', '9', '14:10 - 14:50', 'X 2', 'Bahasa Indonesia', 'A. FERAWATI SUT, S.Pd.', '2026-03-21 21:14:16'),
(984, 'RABU', '9', '14:10 - 14:50', 'X 3', 'Bahasa Indonesia', 'NURFATWA, S.Pd.', '2026-03-21 21:14:16'),
(985, 'RABU', '9', '14:10 - 14:50', 'X 4', 'Seni Budaya', 'ASWAN ANWAR, S.Pd.', '2026-03-21 21:14:16'),
(986, 'RABU', '9', '14:10 - 14:50', 'X 5', 'Pendidikan Pancasila', 'YEKTIE NUR PRAYOGA LM, S.Pd., Gr.', '2026-03-21 21:14:16'),
(987, 'RABU', '9', '14:10 - 14:50', 'X 6', 'Muatan Lokal (BTQ)', 'MUH. ARSYAD HY., S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(988, 'RABU', '9', '14:10 - 14:50', 'X 7', 'Bahasa Inggris', 'ARIFIN, S.Pd.', '2026-03-21 21:14:16'),
(989, 'RABU', '9', '14:10 - 14:50', 'X 8', 'Sosiologi', 'ADE HANDAYANI, S.Sos., M.Pd., Gr.', '2026-03-21 21:14:16'),
(990, 'RABU', '9', '14:10 - 14:50', 'X 9', 'Matematika', 'NURAFNI PEBRIANTY, S.Pd., Gr.', '2026-03-21 21:14:16'),
(991, 'RABU', '9', '14:10 - 14:50', 'X 10', 'Matematika', 'SYAMSIAR, S.Pd.', '2026-03-21 21:14:16'),
(992, 'RABU', '9', '14:10 - 14:50', 'X 11', 'Muatan Lokal (BTQ)', 'ACHMAD MUFTI AL GALZA S., S.Pd.I.', '2026-03-21 21:14:16'),
(993, 'RABU', '9', '14:10 - 14:50', 'XI 1', 'Informatika (Pilihan)', 'MUHAMMAD IDRIS RIFAI SARRO, S.Si.', '2026-03-21 21:14:16'),
(994, 'RABU', '9', '14:10 - 14:50', 'XI 2', 'Biologi (Pilihan)', 'CATHARINA NOVENI DWIDJADJANTI, S.Pd.', '2026-03-21 21:14:16'),
(995, 'RABU', '9', '14:10 - 14:50', 'XI 3', 'Seni Budaya', 'ST. NADIA KHAERATY, S.Pd.', '2026-03-21 21:14:16'),
(996, 'RABU', '9', '14:10 - 14:50', 'XI 4', 'Bahasa Inggris (Pilihan)', 'AFIAH SAKIAH, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(997, 'RABU', '9', '14:10 - 14:50', 'XI 5', 'Matematika', 'MASNIATI, S.Pd.', '2026-03-21 21:14:16'),
(998, 'RABU', '9', '14:10 - 14:50', 'XI 6', 'Bahasa Indonesia', 'HASNA BAKRI, S.Pd.', '2026-03-21 21:14:16'),
(999, 'RABU', '9', '14:10 - 14:50', 'XI 7', 'Bahasa Inggris', 'RISTA JUNIANTI SAHAR, SS.', '2026-03-21 21:14:16'),
(1000, 'RABU', '9', '14:10 - 14:50', 'XI 8', 'Bahasa Inggris (Pilihan)', 'RANDIKA DHANI SYAPUTRA, S.Pd.', '2026-03-21 21:14:16'),
(1001, 'RABU', '9', '14:10 - 14:50', 'XI 9', 'Sejarah', 'ALI TAHIR, S.Pd.', '2026-03-21 21:14:16'),
(1002, 'RABU', '9', '14:10 - 14:50', 'XI 10', 'Pendidikan Pancasila', 'EKA RUSNAENI, S.Pd., Gr.', '2026-03-21 21:14:16'),
(1003, 'RABU', '9', '14:10 - 14:50', 'XI 11', 'Matematika', 'NUR QALBI RUSDIN, S.Pd.,M.Pd.', '2026-03-21 21:14:16'),
(1004, 'RABU', '9', '14:10 - 14:50', 'XI 12', 'Seni Budaya', 'ANUGRAYANTI, S.Pd.', '2026-03-21 21:14:16'),
(1005, 'RABU', '9', '14:10 - 14:50', 'XII 1', 'Biologi (Pilihan)', 'Drs. MUHAMMAD HAMZAH, MM.', '2026-03-21 21:14:16'),
(1006, 'RABU', '9', '14:10 - 14:50', 'XII 2', 'Fisika (Pilihan)', 'SAPPEWATI, S.Pd.', '2026-03-21 21:14:16'),
(1007, 'RABU', '9', '14:10 - 14:50', 'XII 3', 'Ekonomi (Pilihan)', 'Dr. Hj. ROSMAWATI, SE.,MA.', '2026-03-21 21:14:16'),
(1008, 'RABU', '9', '14:10 - 14:50', 'XII 4', 'Geografi (Pilihan)', 'Drs. BAHARUDDIN', '2026-03-21 21:14:16'),
(1009, 'RABU', '9', '14:10 - 14:50', 'XII 5', 'Ekonomi (Pilihan)', 'MUHAMMAD YUSUF, S.Pd.', '2026-03-21 21:14:16'),
(1010, 'RABU', '9', '14:10 - 14:50', 'XII 6', 'Matematika (Pilihan)', 'HAMKA, S.Si., S.Pd., Gr.', '2026-03-21 21:14:16'),
(1011, 'RABU', '9', '14:10 - 14:50', 'XII 7', 'Biologi (Pilihan)', 'HAMIDATUSSIFAH, S.Si.', '2026-03-21 21:14:16'),
(1012, 'RABU', '9', '14:10 - 14:50', 'XII 8', 'Ekonomi (Pilihan)', 'A. VEBHY INDAH BELLIANTHARI, S.Pd.', '2026-03-21 21:14:16'),
(1013, 'RABU', '9', '14:10 - 14:50', 'XII 9', 'Bahasa Indonesia (Pilihan)', 'NUR AKBAR, S.Pd.', '2026-03-21 21:14:16'),
(1014, 'RABU', '9', '14:10 - 14:50', 'XII 10', 'Bahasa Inggris (Pilihan)', 'MUHAMMAD SALEH, S.Pd., MM.', '2026-03-21 21:14:16'),
(1015, 'RABU', '9', '14:10 - 14:50', 'XII 11', 'Kimia (Pilihan)', 'MARTHINA SATTU ARI, M.Pd.', '2026-03-21 21:14:16'),
(1016, 'RABU', '9', '14:10 - 14:50', 'XII 12', 'Fisika (Pilihan)', 'EVI YULIATI, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(1017, 'RABU', '10', '14:50 - 15:30', 'X 1', 'Geografi', 'SANTI KUSUMA DEWI, S.Pd.', '2026-03-21 21:14:16'),
(1018, 'RABU', '10', '14:50 - 15:30', 'X 2', 'Bahasa Indonesia', 'A. FERAWATI SUT, S.Pd.', '2026-03-21 21:14:16'),
(1019, 'RABU', '10', '14:50 - 15:30', 'X 3', 'Bahasa Indonesia', 'NURFATWA, S.Pd.', '2026-03-21 21:14:16'),
(1020, 'RABU', '10', '14:50 - 15:30', 'X 4', 'Seni Budaya', 'ASWAN ANWAR, S.Pd.', '2026-03-21 21:14:16'),
(1021, 'RABU', '10', '14:50 - 15:30', 'X 5', 'Pendidikan Pancasila', 'YEKTIE NUR PRAYOGA LM, S.Pd., Gr.', '2026-03-21 21:14:16'),
(1022, 'RABU', '10', '14:50 - 15:30', 'X 6', 'Muatan Lokal (BTQ)', 'MUH. ARSYAD HY., S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(1023, 'RABU', '10', '14:50 - 15:30', 'X 7', 'Bahasa Inggris', 'ARIFIN, S.Pd.', '2026-03-21 21:14:16'),
(1024, 'RABU', '10', '14:50 - 15:30', 'X 8', 'Sosiologi', 'ADE HANDAYANI, S.Sos., M.Pd., Gr.', '2026-03-21 21:14:16'),
(1025, 'RABU', '10', '14:50 - 15:30', 'X 9', 'Matematika', 'NURAFNI PEBRIANTY, S.Pd., Gr.', '2026-03-21 21:14:16'),
(1026, 'RABU', '10', '14:50 - 15:30', 'X 10', 'Matematika', 'SYAMSIAR, S.Pd.', '2026-03-21 21:14:16'),
(1027, 'RABU', '10', '14:50 - 15:30', 'X 11', 'Muatan Lokal (BTQ)', 'ACHMAD MUFTI AL GALZA S., S.Pd.I.', '2026-03-21 21:14:16'),
(1028, 'RABU', '10', '14:50 - 15:30', 'XI 1', 'Informatika (Pilihan)', 'MUHAMMAD IDRIS RIFAI SARRO, S.Si.', '2026-03-21 21:14:16'),
(1029, 'RABU', '10', '14:50 - 15:30', 'XI 2', 'Biologi (Pilihan)', 'CATHARINA NOVENI DWIDJADJANTI, S.Pd.', '2026-03-21 21:14:16'),
(1030, 'RABU', '10', '14:50 - 15:30', 'XI 3', 'Seni Budaya', 'ST. NADIA KHAERATY, S.Pd.', '2026-03-21 21:14:16'),
(1031, 'RABU', '10', '14:50 - 15:30', 'XI 4', 'Bahasa Inggris (Pilihan)', 'AFIAH SAKIAH, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(1032, 'RABU', '10', '14:50 - 15:30', 'XI 5', 'Matematika', 'MASNIATI, S.Pd.', '2026-03-21 21:14:16'),
(1033, 'RABU', '10', '14:50 - 15:30', 'XI 6', 'Bahasa Indonesia', 'HASNA BAKRI, S.Pd.', '2026-03-21 21:14:16'),
(1034, 'RABU', '10', '14:50 - 15:30', 'XI 7', 'Bahasa Inggris', 'RISTA JUNIANTI SAHAR, SS.', '2026-03-21 21:14:16'),
(1035, 'RABU', '10', '14:50 - 15:30', 'XI 8', 'Bahasa Inggris (Pilihan)', 'RANDIKA DHANI SYAPUTRA, S.Pd.', '2026-03-21 21:14:16'),
(1036, 'RABU', '10', '14:50 - 15:30', 'XI 9', 'Sejarah', 'ALI TAHIR, S.Pd.', '2026-03-21 21:14:16'),
(1037, 'RABU', '10', '14:50 - 15:30', 'XI 10', 'Pendidikan Pancasila', 'EKA RUSNAENI, S.Pd., Gr.', '2026-03-21 21:14:16'),
(1038, 'RABU', '10', '14:50 - 15:30', 'XI 11', 'Matematika', 'NUR QALBI RUSDIN, S.Pd.,M.Pd.', '2026-03-21 21:14:16'),
(1039, 'RABU', '10', '14:50 - 15:30', 'XI 12', 'Seni Budaya', 'ANUGRAYANTI, S.Pd.', '2026-03-21 21:14:16'),
(1040, 'RABU', '10', '14:50 - 15:30', 'XII 1', 'Biologi (Pilihan)', 'Drs. MUHAMMAD HAMZAH, MM.', '2026-03-21 21:14:16'),
(1041, 'RABU', '10', '14:50 - 15:30', 'XII 2', 'Fisika (Pilihan)', 'SAPPEWATI, S.Pd.', '2026-03-21 21:14:16'),
(1042, 'RABU', '10', '14:50 - 15:30', 'XII 3', 'Ekonomi (Pilihan)', 'Dr. Hj. ROSMAWATI, SE.,MA.', '2026-03-21 21:14:16'),
(1043, 'RABU', '10', '14:50 - 15:30', 'XII 4', 'Geografi (Pilihan)', 'Drs. BAHARUDDIN', '2026-03-21 21:14:16'),
(1044, 'RABU', '10', '14:50 - 15:30', 'XII 5', 'Ekonomi (Pilihan)', 'MUHAMMAD YUSUF, S.Pd.', '2026-03-21 21:14:16'),
(1045, 'RABU', '10', '14:50 - 15:30', 'XII 6', 'Matematika (Pilihan)', 'HAMKA, S.Si., S.Pd., Gr.', '2026-03-21 21:14:16'),
(1046, 'RABU', '10', '14:50 - 15:30', 'XII 7', 'Biologi (Pilihan)', 'HAMIDATUSSIFAH, S.Si.', '2026-03-21 21:14:16'),
(1047, 'RABU', '10', '14:50 - 15:30', 'XII 8', 'Ekonomi (Pilihan)', 'A. VEBHY INDAH BELLIANTHARI, S.Pd.', '2026-03-21 21:14:16'),
(1048, 'RABU', '10', '14:50 - 15:30', 'XII 9', 'Bahasa Indonesia (Pilihan)', 'NUR AKBAR, S.Pd.', '2026-03-21 21:14:16'),
(1049, 'RABU', '10', '14:50 - 15:30', 'XII 10', 'Bahasa Inggris (Pilihan)', 'MUHAMMAD SALEH, S.Pd., MM.', '2026-03-21 21:14:16'),
(1050, 'RABU', '10', '14:50 - 15:30', 'XII 11', 'Kimia (Pilihan)', 'MARTHINA SATTU ARI, M.Pd.', '2026-03-21 21:14:16'),
(1051, 'RABU', '10', '14:50 - 15:30', 'XII 12', 'Fisika (Pilihan)', 'EVI YULIATI, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(1052, 'KAMIS', '1', '07:30 - 08:15', 'X 1', 'Muatan Lokal (BTQ)', 'MUH. ARSYAD HY., S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(1053, 'KAMIS', '1', '07:30 - 08:15', 'X 2', 'Matematika', 'SYAMSIAR, S.Pd.', '2026-03-21 21:14:16'),
(1054, 'KAMIS', '1', '07:30 - 08:15', 'X 3', 'Kimia', 'ISMAIL LA HASSE, S.Si., S.Pd.', '2026-03-21 21:14:16'),
(1055, 'KAMIS', '1', '07:30 - 08:15', 'X 4', 'PJOK', 'MUH. GILANG FAJARI, S.Pd.', '2026-03-21 21:14:16'),
(1056, 'KAMIS', '1', '07:30 - 08:15', 'X 5', 'Geografi', 'SANTI KUSUMA DEWI, S.Pd.', '2026-03-21 21:14:16'),
(1057, 'KAMIS', '1', '07:30 - 08:15', 'X 6', 'Bahasa Indonesia', 'NURFATWA, S.Pd.', '2026-03-21 21:14:16'),
(1058, 'KAMIS', '1', '07:30 - 08:15', 'X 7', 'Biologi', 'NURSANTY, S.Pd.', '2026-03-21 21:14:16'),
(1059, 'KAMIS', '1', '07:30 - 08:15', 'X 8', 'Bahasa Indonesia', 'A. FERAWATI SUT, S.Pd.', '2026-03-21 21:14:16'),
(1060, 'KAMIS', '1', '07:30 - 08:15', 'X 9', 'Sejarah', 'ERNIATI, S.Pd.', '2026-03-21 21:14:16'),
(1061, 'KAMIS', '1', '07:30 - 08:15', 'X 10', 'Bahasa Inggris', 'RISTA JUNIANTI SAHAR, SS.', '2026-03-21 21:14:16'),
(1062, 'KAMIS', '1', '07:30 - 08:15', 'X 11', 'Sosiologi', 'ADE HANDAYANI, S.Sos., M.Pd., Gr.', '2026-03-21 21:14:16'),
(1063, 'KAMIS', '1', '07:30 - 08:15', 'XI 1', 'Pendidikan Pancasila', 'YEKTIE NUR PRAYOGA LM, S.Pd., Gr.', '2026-03-21 21:14:16'),
(1064, 'KAMIS', '1', '07:30 - 08:15', 'XI 2', 'Seni Budaya', 'ANUGRAYANTI, S.Pd.', '2026-03-21 21:14:16'),
(1065, 'KAMIS', '1', '07:30 - 08:15', 'XI 3', 'Prakarya', 'SARWANA, S.Pd.', '2026-03-21 21:14:16'),
(1066, 'KAMIS', '1', '07:30 - 08:15', 'XI 4', 'Matematika', 'NUR QALBI RUSDIN, S.Pd.,M.Pd.', '2026-03-21 21:14:16'),
(1067, 'KAMIS', '1', '07:30 - 08:15', 'XI 5', 'Bahasa Indonesia', 'NUR AKBAR, S.Pd.', '2026-03-21 21:14:16'),
(1068, 'KAMIS', '1', '07:30 - 08:15', 'XI 6', 'Biologi (Pilihan)', 'CATHARINA NOVENI DWIDJADJANTI, S.Pd.', '2026-03-21 21:14:16'),
(1069, 'KAMIS', '1', '07:30 - 08:15', 'XI 7', 'Matematika', 'HAMKA, S.Si., S.Pd., Gr.', '2026-03-21 21:14:16'),
(1070, 'KAMIS', '1', '07:30 - 08:15', 'XI 8', 'Pendidikan Agama', 'ACHMAD MUFTI AL GALZA S., S.Pd.I.', '2026-03-21 21:14:16'),
(1071, 'KAMIS', '1', '07:30 - 08:15', 'XI 9', 'Bahasa Indonesia', 'HASNA BAKRI, S.Pd.', '2026-03-21 21:14:16'),
(1072, 'KAMIS', '1', '07:30 - 08:15', 'XI 10', 'PJOK', 'ASRIANTO, S.Or., Gr.', '2026-03-21 21:14:16'),
(1073, 'KAMIS', '1', '07:30 - 08:15', 'XI 11', 'Sosiologi (Pilihan)', 'SRI UTAMI WULANSARI, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(1074, 'KAMIS', '1', '07:30 - 08:15', 'XI 12', 'PJOK', 'DWI SETIAWATI B., S.Pd.', '2026-03-21 21:14:16'),
(1075, 'KAMIS', '1', '07:30 - 08:15', 'XII 1', 'Bahasa Indonesia', 'FITRIYANI, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(1076, 'KAMIS', '1', '07:30 - 08:15', 'XII 2', 'Bahasa Inggris', 'MUHAMMAD SALEH, S.Pd., MM.', '2026-03-21 21:14:16'),
(1077, 'KAMIS', '1', '07:30 - 08:15', 'XII 3', 'Matematika', 'LILIES RATNA RIYANTINI, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(1078, 'KAMIS', '1', '07:30 - 08:15', 'XII 4', 'Prakarya', 'MUTMAINNAH, S.Pd., M.Biomed.', '2026-03-21 21:14:16'),
(1079, 'KAMIS', '1', '07:30 - 08:15', 'XII 5', 'Pendidikan Agama', 'SITTI NAFI, S.Pd.I.', '2026-03-21 21:14:16'),
(1080, 'KAMIS', '1', '07:30 - 08:15', 'XII 6', 'Sejarah', 'DAMAYANTI, S.Pd.', '2026-03-21 21:14:16'),
(1081, 'KAMIS', '1', '07:30 - 08:15', 'XII 7', 'PJOK', 'ABD. KAMAL, S.Pd.', '2026-03-21 21:14:16'),
(1082, 'KAMIS', '1', '07:30 - 08:15', 'XII 8', 'Seni Budaya', 'ST. NADIA KHAERATY, S.Pd.', '2026-03-21 21:14:16'),
(1083, 'KAMIS', '1', '07:30 - 08:15', 'XII 9', 'Pendidikan Pancasila', 'EKA RUSNAENI, S.Pd., Gr.', '2026-03-21 21:14:16'),
(1084, 'KAMIS', '1', '07:30 - 08:15', 'XII 10', 'Bahasa Indonesia', 'ST. ROSMAWATI M., S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(1085, 'KAMIS', '1', '07:30 - 08:15', 'XII 11', 'Bahasa Inggris', 'AFIAH SAKIAH, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(1086, 'KAMIS', '1', '07:30 - 08:15', 'XII 12', 'Matematika', 'MASNIATI, S.Pd.', '2026-03-21 21:14:16'),
(1087, 'KAMIS', '2', '08:15 - 09:00', 'X 1', 'Muatan Lokal (BTQ)', 'MUH. ARSYAD HY., S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(1088, 'KAMIS', '2', '08:15 - 09:00', 'X 2', 'Matematika', 'SYAMSIAR, S.Pd.', '2026-03-21 21:14:16'),
(1089, 'KAMIS', '2', '08:15 - 09:00', 'X 3', 'Kimia', 'ISMAIL LA HASSE, S.Si., S.Pd.', '2026-03-21 21:14:16'),
(1090, 'KAMIS', '2', '08:15 - 09:00', 'X 4', 'PJOK', 'MUH. GILANG FAJARI, S.Pd.', '2026-03-21 21:14:16'),
(1091, 'KAMIS', '2', '08:15 - 09:00', 'X 5', 'Geografi', 'SANTI KUSUMA DEWI, S.Pd.', '2026-03-21 21:14:16'),
(1092, 'KAMIS', '2', '08:15 - 09:00', 'X 6', 'Bahasa Indonesia', 'NURFATWA, S.Pd.', '2026-03-21 21:14:16'),
(1093, 'KAMIS', '2', '08:15 - 09:00', 'X 7', 'Biologi', 'NURSANTY, S.Pd.', '2026-03-21 21:14:16'),
(1094, 'KAMIS', '2', '08:15 - 09:00', 'X 8', 'Bahasa Indonesia', 'A. FERAWATI SUT, S.Pd.', '2026-03-21 21:14:16'),
(1095, 'KAMIS', '2', '08:15 - 09:00', 'X 9', 'Sejarah', 'ERNIATI, S.Pd.', '2026-03-21 21:14:16'),
(1096, 'KAMIS', '2', '08:15 - 09:00', 'X 10', 'Bahasa Inggris', 'RISTA JUNIANTI SAHAR, SS.', '2026-03-21 21:14:16'),
(1097, 'KAMIS', '2', '08:15 - 09:00', 'X 11', 'Sosiologi', 'ADE HANDAYANI, S.Sos., M.Pd., Gr.', '2026-03-21 21:14:16'),
(1098, 'KAMIS', '2', '08:15 - 09:00', 'XI 1', 'Pendidikan Pancasila', 'YEKTIE NUR PRAYOGA LM, S.Pd., Gr.', '2026-03-21 21:14:16'),
(1099, 'KAMIS', '2', '08:15 - 09:00', 'XI 2', 'Seni Budaya', 'ANUGRAYANTI, S.Pd.', '2026-03-21 21:14:16'),
(1100, 'KAMIS', '2', '08:15 - 09:00', 'XI 3', 'Prakarya', 'SARWANA, S.Pd.', '2026-03-21 21:14:16'),
(1101, 'KAMIS', '2', '08:15 - 09:00', 'XI 4', 'Informatika (Pilihan)', 'RIZA RISKY YULIANTI, S.Pd., Gr.', '2026-03-21 21:14:16'),
(1102, 'KAMIS', '2', '08:15 - 09:00', 'XI 5', 'Bahasa Indonesia', 'NUR AKBAR, S.Pd.', '2026-03-21 21:14:16'),
(1103, 'KAMIS', '2', '08:15 - 09:00', 'XI 6', 'Biologi (Pilihan)', 'CATHARINA NOVENI DWIDJADJANTI, S.Pd.', '2026-03-21 21:14:16'),
(1104, 'KAMIS', '2', '08:15 - 09:00', 'XI 7', 'Matematika', 'HAMKA, S.Si., S.Pd., Gr.', '2026-03-21 21:14:16'),
(1105, 'KAMIS', '2', '08:15 - 09:00', 'XI 8', 'Pendidikan Agama', 'ACHMAD MUFTI AL GALZA S., S.Pd.I.', '2026-03-21 21:14:16'),
(1106, 'KAMIS', '2', '08:15 - 09:00', 'XI 9', 'Bahasa Indonesia', 'HASNA BAKRI, S.Pd.', '2026-03-21 21:14:16'),
(1107, 'KAMIS', '2', '08:15 - 09:00', 'XI 10', 'PJOK', 'ASRIANTO, S.Or., Gr.', '2026-03-21 21:14:16'),
(1108, 'KAMIS', '2', '08:15 - 09:00', 'XI 11', 'Sosiologi (Pilihan)', 'SRI UTAMI WULANSARI, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(1109, 'KAMIS', '2', '08:15 - 09:00', 'XI 12', 'PJOK', 'DWI SETIAWATI B., S.Pd.', '2026-03-21 21:14:16'),
(1110, 'KAMIS', '2', '08:15 - 09:00', 'XII 1', 'Bahasa Indonesia', 'FITRIYANI, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(1111, 'KAMIS', '2', '08:15 - 09:00', 'XII 2', 'Bahasa Inggris', 'MUHAMMAD SALEH, S.Pd., MM.', '2026-03-21 21:14:16'),
(1112, 'KAMIS', '2', '08:15 - 09:00', 'XII 3', 'Matematika', 'LILIES RATNA RIYANTINI, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(1113, 'KAMIS', '2', '08:15 - 09:00', 'XII 4', 'Prakarya', 'MUTMAINNAH, S.Pd., M.Biomed.', '2026-03-21 21:14:16'),
(1114, 'KAMIS', '2', '08:15 - 09:00', 'XII 5', 'Pendidikan Agama', 'SITTI NAFI, S.Pd.I.', '2026-03-21 21:14:16'),
(1115, 'KAMIS', '2', '08:15 - 09:00', 'XII 6', 'Sejarah', 'DAMAYANTI, S.Pd.', '2026-03-21 21:14:16'),
(1116, 'KAMIS', '2', '08:15 - 09:00', 'XII 7', 'PJOK', 'ABD. KAMAL, S.Pd.', '2026-03-21 21:14:16'),
(1117, 'KAMIS', '2', '08:15 - 09:00', 'XII 8', 'Seni Budaya', 'ST. NADIA KHAERATY, S.Pd.', '2026-03-21 21:14:16'),
(1118, 'KAMIS', '2', '08:15 - 09:00', 'XII 9', 'Pendidikan Pancasila', 'EKA RUSNAENI, S.Pd., Gr.', '2026-03-21 21:14:16'),
(1119, 'KAMIS', '2', '08:15 - 09:00', 'XII 10', 'Bahasa Indonesia', 'ST. ROSMAWATI M., S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(1120, 'KAMIS', '2', '08:15 - 09:00', 'XII 11', 'Bahasa Inggris', 'AFIAH SAKIAH, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(1121, 'KAMIS', '2', '08:15 - 09:00', 'XII 12', 'Matematika', 'MASNIATI, S.Pd.', '2026-03-21 21:14:16'),
(1122, 'KAMIS', '3', '09:00 - 09:45', 'X 1', 'Pendidikan Agama', 'SURYADI SYARIF, S.Pd.', '2026-03-21 21:14:16'),
(1123, 'KAMIS', '3', '09:00 - 09:45', 'X 2', 'Biologi', 'MINDJAH WATY M., S.Si.', '2026-03-21 21:14:16'),
(1124, 'KAMIS', '3', '09:00 - 09:45', 'X 3', 'Kimia', 'ISMAIL LA HASSE, S.Si., S.Pd.', '2026-03-21 21:14:16'),
(1125, 'KAMIS', '3', '09:00 - 09:45', 'X 4', 'PJOK', 'MUH. GILANG FAJARI, S.Pd.', '2026-03-21 21:14:16'),
(1126, 'KAMIS', '3', '09:00 - 09:45', 'X 5', 'Geografi', 'SANTI KUSUMA DEWI, S.Pd.', '2026-03-21 21:14:16'),
(1127, 'KAMIS', '3', '09:00 - 09:45', 'X 6', 'Pendidikan Agama', 'MUH. ARSYAD HY., S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(1128, 'KAMIS', '3', '09:00 - 09:45', 'X 7', 'Biologi', 'NURSANTY, S.Pd.', '2026-03-21 21:14:16'),
(1129, 'KAMIS', '3', '09:00 - 09:45', 'X 8', 'Matematika', 'NURAFNI PEBRIANTY, S.Pd., Gr.', '2026-03-21 21:14:16'),
(1130, 'KAMIS', '3', '09:00 - 09:45', 'X 9', 'Sejarah', 'ERNIATI, S.Pd.', '2026-03-21 21:14:16'),
(1131, 'KAMIS', '3', '09:00 - 09:45', 'X 10', 'Bahasa Inggris', 'RISTA JUNIANTI SAHAR, SS.', '2026-03-21 21:14:16'),
(1132, 'KAMIS', '3', '09:00 - 09:45', 'X 11', 'Sosiologi', 'ADE HANDAYANI, S.Sos., M.Pd., Gr.', '2026-03-21 21:14:16'),
(1133, 'KAMIS', '3', '09:00 - 09:45', 'XI 1', 'Seni Budaya', 'ANUGRAYANTI, S.Pd.', '2026-03-21 21:14:16'),
(1134, 'KAMIS', '3', '09:00 - 09:45', 'XI 2', 'Bahasa Indonesia', 'A. FERAWATI SUT, S.Pd.', '2026-03-21 21:14:16'),
(1135, 'KAMIS', '3', '09:00 - 09:45', 'XI 3', 'Biologi (Pilihan)', 'CATHARINA NOVENI DWIDJADJANTI, S.Pd.', '2026-03-21 21:14:16'),
(1136, 'KAMIS', '3', '09:00 - 09:45', 'XI 4', 'Informatika (Pilihan)', 'RIZA RISKY YULIANTI, S.Pd., Gr.', '2026-03-21 21:14:16'),
(1137, 'KAMIS', '3', '09:00 - 09:45', 'XI 5', 'Sejarah', 'ALI TAHIR, S.Pd.', '2026-03-21 21:14:16'),
(1138, 'KAMIS', '3', '09:00 - 09:45', 'XI 6', 'Prakarya', 'SARWANA, S.Pd.', '2026-03-21 21:14:16'),
(1139, 'KAMIS', '3', '09:00 - 09:45', 'XI 7', 'Matematika', 'HAMKA, S.Si., S.Pd., Gr.', '2026-03-21 21:14:16'),
(1140, 'KAMIS', '3', '09:00 - 09:45', 'XI 8', 'Bahasa Indonesia', 'FITRIANI RAHMAT, S.Pd.', '2026-03-21 21:14:16'),
(1141, 'KAMIS', '3', '09:00 - 09:45', 'XI 9', 'Pendidikan Agama', 'ACHMAD MUFTI AL GALZA S., S.Pd.I.', '2026-03-21 21:14:16'),
(1142, 'KAMIS', '3', '09:00 - 09:45', 'XI 10', 'PJOK', 'ASRIANTO, S.Or., Gr.', '2026-03-21 21:14:16'),
(1143, 'KAMIS', '3', '09:00 - 09:45', 'XI 11', 'Seni Budaya', 'ASWAN ANWAR, S.Pd.', '2026-03-21 21:14:16'),
(1144, 'KAMIS', '3', '09:00 - 09:45', 'XI 12', 'PJOK', 'DWI SETIAWATI B., S.Pd.', '2026-03-21 21:14:16'),
(1145, 'KAMIS', '3', '09:00 - 09:45', 'XII 1', 'Pendidikan Pancasila', 'EKA RUSNAENI, S.Pd., Gr.', '2026-03-21 21:14:16'),
(1146, 'KAMIS', '3', '09:00 - 09:45', 'XII 2', 'Bahasa Inggris', 'MUHAMMAD SALEH, S.Pd., MM.', '2026-03-21 21:14:16'),
(1147, 'KAMIS', '3', '09:00 - 09:45', 'XII 3', 'Bahasa Indonesia', 'FITRIYANI, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(1148, 'KAMIS', '3', '09:00 - 09:45', 'XII 4', 'Matematika', 'LILIES RATNA RIYANTINI, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(1149, 'KAMIS', '3', '09:00 - 09:45', 'XII 5', 'Pendidikan Agama', 'SITTI NAFI, S.Pd.I.', '2026-03-21 21:14:16'),
(1150, 'KAMIS', '3', '09:00 - 09:45', 'XII 6', 'Prakarya', 'MUTMAINNAH, S.Pd., M.Biomed.', '2026-03-21 21:14:16'),
(1151, 'KAMIS', '3', '09:00 - 09:45', 'XII 7', 'PJOK', 'ABD. KAMAL, S.Pd.', '2026-03-21 21:14:16'),
(1152, 'KAMIS', '3', '09:00 - 09:45', 'XII 8', 'Sejarah', 'DAMAYANTI, S.Pd.', '2026-03-21 21:14:16'),
(1153, 'KAMIS', '3', '09:00 - 09:45', 'XII 9', 'Bahasa Indonesia', 'ST. ROSMAWATI M., S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(1154, 'KAMIS', '3', '09:00 - 09:45', 'XII 10', 'Matematika', 'MASNIATI, S.Pd.', '2026-03-21 21:14:16'),
(1155, 'KAMIS', '3', '09:00 - 09:45', 'XII 11', 'Bahasa Inggris', 'AFIAH SAKIAH, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(1156, 'KAMIS', '3', '09:00 - 09:45', 'XII 12', 'Seni Budaya', 'ST. NADIA KHAERATY, S.Pd.', '2026-03-21 21:14:16'),
(1157, 'KAMIS', '4', '09:45 - 10:30', 'X 1', 'Pendidikan Agama', 'SURYADI SYARIF, S.Pd.', '2026-03-21 21:14:16'),
(1158, 'KAMIS', '4', '09:45 - 10:30', 'X 2', 'Biologi', 'MINDJAH WATY M., S.Si.', '2026-03-21 21:14:16'),
(1159, 'KAMIS', '4', '09:45 - 10:30', 'X 3', 'Pendidikan Pancasila', 'YEKTIE NUR PRAYOGA LM, S.Pd., Gr.', '2026-03-21 21:14:16'),
(1160, 'KAMIS', '4', '09:45 - 10:30', 'X 4', 'Fisika', 'EVI YULIATI, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(1161, 'KAMIS', '4', '09:45 - 10:30', 'X 5', 'Kimia', 'LENNI BAKRI, S.Pd.', '2026-03-21 21:14:16'),
(1162, 'KAMIS', '4', '09:45 - 10:30', 'X 6', 'Pendidikan Agama', 'MUH. ARSYAD HY., S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(1163, 'KAMIS', '4', '09:45 - 10:30', 'X 7', 'PJOK', 'ASRIANTO, S.Or., Gr.', '2026-03-21 21:14:16'),
(1164, 'KAMIS', '4', '09:45 - 10:30', 'X 8', 'Matematika', 'NURAFNI PEBRIANTY, S.Pd., Gr.', '2026-03-21 21:14:16'),
(1165, 'KAMIS', '4', '09:45 - 10:30', 'X 9', 'Geografi', 'HALIFUDDIN, S.Pd.', '2026-03-21 21:14:16'),
(1166, 'KAMIS', '4', '09:45 - 10:30', 'X 10', 'Sosiologi', 'ADE HANDAYANI, S.Sos., M.Pd., Gr.', '2026-03-21 21:14:16'),
(1167, 'KAMIS', '4', '09:45 - 10:30', 'X 11', 'Matematika', 'HARTIWI PRADIKTA, S.Pd., Gr.', '2026-03-21 21:14:16'),
(1168, 'KAMIS', '4', '09:45 - 10:30', 'XI 1', 'Seni Budaya', 'ANUGRAYANTI, S.Pd.', '2026-03-21 21:14:16'),
(1169, 'KAMIS', '4', '09:45 - 10:30', 'XI 2', 'Matematika', 'NUR QALBI RUSDIN, S.Pd.,M.Pd.', '2026-03-21 21:14:16'),
(1170, 'KAMIS', '4', '09:45 - 10:30', 'XI 3', 'Biologi (Pilihan)', 'CATHARINA NOVENI DWIDJADJANTI, S.Pd.', '2026-03-21 21:14:16'),
(1171, 'KAMIS', '4', '09:45 - 10:30', 'XI 4', 'Informatika (Pilihan)', 'RIZA RISKY YULIANTI, S.Pd., Gr.', '2026-03-21 21:14:16'),
(1172, 'KAMIS', '4', '09:45 - 10:30', 'XI 5', 'Sejarah', 'ALI TAHIR, S.Pd.', '2026-03-21 21:14:16'),
(1173, 'KAMIS', '4', '09:45 - 10:30', 'XI 6', 'Prakarya', 'SARWANA, S.Pd.', '2026-03-21 21:14:16'),
(1174, 'KAMIS', '4', '09:45 - 10:30', 'XI 7', 'PJOK', 'DWI SETIAWATI B., S.Pd.', '2026-03-21 21:14:16'),
(1175, 'KAMIS', '4', '09:45 - 10:30', 'XI 8', 'Bahasa Indonesia', 'FITRIANI RAHMAT, S.Pd.', '2026-03-21 21:14:16'),
(1176, 'KAMIS', '4', '09:45 - 10:30', 'XI 9', 'Bahasa Inggris', 'ARIFIN, S.Pd.', '2026-03-21 21:14:16'),
(1177, 'KAMIS', '4', '09:45 - 10:30', 'XI 10', 'Ekonomi (Pilihan)', 'MUHAMMAD YUSUF, S.Pd.', '2026-03-21 21:14:16'),
(1178, 'KAMIS', '4', '09:45 - 10:30', 'XI 11', 'Seni Budaya', 'ASWAN ANWAR, S.Pd.', '2026-03-21 21:14:16'),
(1179, 'KAMIS', '4', '09:45 - 10:30', 'XI 12', 'Bahasa Inggris', 'RISTA JUNIANTI SAHAR, SS.', '2026-03-21 21:14:16'),
(1180, 'KAMIS', '4', '09:45 - 10:30', 'XII 1', 'Pendidikan Pancasila', 'EKA RUSNAENI, S.Pd., Gr.', '2026-03-21 21:14:16'),
(1181, 'KAMIS', '4', '09:45 - 10:30', 'XII 2', 'Matematika', 'LUDIHARISTIADI, S.Pd.', '2026-03-21 21:14:16'),
(1182, 'KAMIS', '4', '09:45 - 10:30', 'XII 3', 'Bahasa Indonesia', 'FITRIYANI, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(1183, 'KAMIS', '4', '09:45 - 10:30', 'XII 4', 'Matematika', 'LILIES RATNA RIYANTINI, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(1184, 'KAMIS', '4', '09:45 - 10:30', 'XII 5', 'PJOK', 'ABD. KAMAL, S.Pd.', '2026-03-21 21:14:16'),
(1185, 'KAMIS', '4', '09:45 - 10:30', 'XII 6', 'Prakarya', 'MUTMAINNAH, S.Pd., M.Biomed.', '2026-03-21 21:14:16'),
(1186, 'KAMIS', '4', '09:45 - 10:30', 'XII 7', 'Pendidikan Agama', 'SITTI NAFI, S.Pd.I.', '2026-03-21 21:14:16'),
(1187, 'KAMIS', '4', '09:45 - 10:30', 'XII 8', 'Sejarah', 'DAMAYANTI, S.Pd.', '2026-03-21 21:14:16'),
(1188, 'KAMIS', '4', '09:45 - 10:30', 'XII 9', 'Bahasa Indonesia', 'ST. ROSMAWATI M., S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(1189, 'KAMIS', '4', '09:45 - 10:30', 'XII 10', 'Matematika', 'MASNIATI, S.Pd.', '2026-03-21 21:14:16'),
(1190, 'KAMIS', '4', '09:45 - 10:30', 'XII 11', 'PJOK', 'MUH. GILANG FAJARI, S.Pd.', '2026-03-21 21:14:16'),
(1191, 'KAMIS', '4', '09:45 - 10:30', 'XII 12', 'Seni Budaya', 'ST. NADIA KHAERATY, S.Pd.', '2026-03-21 21:14:16'),
(1192, 'KAMIS', '5', '10:45 - 11:30', 'X 1', 'Matematika', 'SYAMSIAR, S.Pd.', '2026-03-21 21:14:16'),
(1193, 'KAMIS', '5', '10:45 - 11:30', 'X 2', 'Bahasa Indonesia', 'A. FERAWATI SUT, S.Pd.', '2026-03-21 21:14:16'),
(1194, 'KAMIS', '5', '10:45 - 11:30', 'X 3', 'Pendidikan Pancasila', 'YEKTIE NUR PRAYOGA LM, S.Pd., Gr.', '2026-03-21 21:14:16'),
(1195, 'KAMIS', '5', '10:45 - 11:30', 'X 4', 'Fisika', 'EVI YULIATI, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(1196, 'KAMIS', '5', '10:45 - 11:30', 'X 5', 'Kimia', 'LENNI BAKRI, S.Pd.', '2026-03-21 21:14:16'),
(1197, 'KAMIS', '5', '10:45 - 11:30', 'X 6', 'Bahasa Inggris', 'RANDIKA DHANI SYAPUTRA, S.Pd.', '2026-03-21 21:14:16'),
(1198, 'KAMIS', '5', '10:45 - 11:30', 'X 7', 'PJOK', 'ASRIANTO, S.Or., Gr.', '2026-03-21 21:14:16'),
(1199, 'KAMIS', '5', '10:45 - 11:30', 'X 8', 'Kimia', 'ISMAIL LA HASSE, S.Si., S.Pd.', '2026-03-21 21:14:16'),
(1200, 'KAMIS', '5', '10:45 - 11:30', 'X 9', 'Geografi', 'HALIFUDDIN, S.Pd.', '2026-03-21 21:14:16'),
(1201, 'KAMIS', '5', '10:45 - 11:30', 'X 10', 'Sosiologi', 'ADE HANDAYANI, S.Sos., M.Pd., Gr.', '2026-03-21 21:14:16'),
(1202, 'KAMIS', '5', '10:45 - 11:30', 'X 11', 'Matematika', 'HARTIWI PRADIKTA, S.Pd., Gr.', '2026-03-21 21:14:16'),
(1203, 'KAMIS', '5', '10:45 - 11:30', 'XI 1', 'Matematika', 'NURAFNI PEBRIANTY, S.Pd., Gr.', '2026-03-21 21:14:16'),
(1204, 'KAMIS', '5', '10:45 - 11:30', 'XI 2', 'Pendidikan Agama', 'MUH. ARSYAD HY., S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(1205, 'KAMIS', '5', '10:45 - 11:30', 'XI 3', 'Bahasa Indonesia', 'FITRIANI RAHMAT, S.Pd.', '2026-03-21 21:14:16'),
(1206, 'KAMIS', '5', '10:45 - 11:30', 'XI 4', 'Biologi (Pilihan)', 'CATHARINA NOVENI DWIDJADJANTI, S.Pd.', '2026-03-21 21:14:16'),
(1207, 'KAMIS', '5', '10:45 - 11:30', 'XI 5', 'Pendidikan Agama', 'SURYADI SYARIF, S.Pd.', '2026-03-21 21:14:16'),
(1208, 'KAMIS', '5', '10:45 - 11:30', 'XI 6', 'Seni Budaya', 'ANUGRAYANTI, S.Pd.', '2026-03-21 21:14:16'),
(1209, 'KAMIS', '5', '10:45 - 11:30', 'XI 7', 'PJOK', 'DWI SETIAWATI B., S.Pd.', '2026-03-21 21:14:16'),
(1210, 'KAMIS', '5', '10:45 - 11:30', 'XI 8', 'Prakarya', 'SARWANA, S.Pd.', '2026-03-21 21:14:16'),
(1211, 'KAMIS', '5', '10:45 - 11:30', 'XI 9', 'Bahasa Inggris', 'ARIFIN, S.Pd.', '2026-03-21 21:14:16'),
(1212, 'KAMIS', '5', '10:45 - 11:30', 'XI 10', 'Ekonomi (Pilihan)', 'MUHAMMAD YUSUF, S.Pd.', '2026-03-21 21:14:16'),
(1213, 'KAMIS', '5', '10:45 - 11:30', 'XI 11', 'Biologi (Pilihan)', 'NURSANTY, S.Pd.', '2026-03-21 21:14:16'),
(1214, 'KAMIS', '5', '10:45 - 11:30', 'XI 12', 'Bahasa Inggris', 'RISTA JUNIANTI SAHAR, SS.', '2026-03-21 21:14:16'),
(1215, 'KAMIS', '5', '10:45 - 11:30', 'XII 1', 'Bahasa Inggris', 'MUHAMMAD SALEH, S.Pd., MM.', '2026-03-21 21:14:16'),
(1216, 'KAMIS', '5', '10:45 - 11:30', 'XII 2', 'Prakarya', 'MUTMAINNAH, S.Pd., M.Biomed.', '2026-03-21 21:14:16'),
(1217, 'KAMIS', '5', '10:45 - 11:30', 'XII 3', 'Sejarah', 'DAMAYANTI, S.Pd.', '2026-03-21 21:14:16'),
(1218, 'KAMIS', '5', '10:45 - 11:30', 'XII 4', 'Seni Budaya', 'ST. NADIA KHAERATY, S.Pd.', '2026-03-21 21:14:16'),
(1219, 'KAMIS', '5', '10:45 - 11:30', 'XII 5', 'PJOK', 'ABD. KAMAL, S.Pd.', '2026-03-21 21:14:16'),
(1220, 'KAMIS', '5', '10:45 - 11:30', 'XII 6', 'Bahasa Indonesia', 'FITRIYANI, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(1221, 'KAMIS', '5', '10:45 - 11:30', 'XII 7', 'Pendidikan Agama', 'SITTI NAFI, S.Pd.I.', '2026-03-21 21:14:16'),
(1222, 'KAMIS', '5', '10:45 - 11:30', 'XII 8', 'Matematika', 'MASNIATI, S.Pd.', '2026-03-21 21:14:16'),
(1223, 'KAMIS', '5', '10:45 - 11:30', 'XII 9', 'Bahasa Inggris', 'AFIAH SAKIAH, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(1224, 'KAMIS', '5', '10:45 - 11:30', 'XII 10', 'Pendidikan Pancasila', 'EKA RUSNAENI, S.Pd., Gr.', '2026-03-21 21:14:16'),
(1225, 'KAMIS', '5', '10:45 - 11:30', 'XII 11', 'PJOK', 'MUH. GILANG FAJARI, S.Pd.', '2026-03-21 21:14:16'),
(1226, 'KAMIS', '5', '10:45 - 11:30', 'XII 12', 'Bahasa Indonesia', 'ST. ROSMAWATI M., S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(1227, 'KAMIS', '6', '11:30 - 12:15', 'X 1', 'Matematika', 'SYAMSIAR, S.Pd.', '2026-03-21 21:14:16'),
(1228, 'KAMIS', '6', '11:30 - 12:15', 'X 2', 'Bahasa Indonesia', 'A. FERAWATI SUT, S.Pd.', '2026-03-21 21:14:16'),
(1229, 'KAMIS', '6', '11:30 - 12:15', 'X 3', 'Biologi', 'MINDJAH WATY M., S.Si.', '2026-03-21 21:14:16'),
(1230, 'KAMIS', '6', '11:30 - 12:15', 'X 4', 'Fisika', 'EVI YULIATI, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(1231, 'KAMIS', '6', '11:30 - 12:15', 'X 5', 'Kimia', 'LENNI BAKRI, S.Pd.', '2026-03-21 21:14:16'),
(1232, 'KAMIS', '6', '11:30 - 12:15', 'X 6', 'Bahasa Inggris', 'RANDIKA DHANI SYAPUTRA, S.Pd.', '2026-03-21 21:14:16'),
(1233, 'KAMIS', '6', '11:30 - 12:15', 'X 7', 'PJOK', 'ASRIANTO, S.Or., Gr.', '2026-03-21 21:14:16'),
(1234, 'KAMIS', '6', '11:30 - 12:15', 'X 8', 'Kimia', 'ISMAIL LA HASSE, S.Si., S.Pd.', '2026-03-21 21:14:16'),
(1235, 'KAMIS', '6', '11:30 - 12:15', 'X 9', 'Geografi', 'HALIFUDDIN, S.Pd.', '2026-03-21 21:14:16'),
(1236, 'KAMIS', '6', '11:30 - 12:15', 'X 10', 'Sosiologi', 'ADE HANDAYANI, S.Sos., M.Pd., Gr.', '2026-03-21 21:14:16'),
(1237, 'KAMIS', '6', '11:30 - 12:15', 'X 11', 'Sejarah', 'ERNIATI, S.Pd.', '2026-03-21 21:14:16'),
(1238, 'KAMIS', '6', '11:30 - 12:15', 'XI 1', 'Matematika', 'NURAFNI PEBRIANTY, S.Pd., Gr.', '2026-03-21 21:14:16'),
(1239, 'KAMIS', '6', '11:30 - 12:15', 'XI 2', 'Pendidikan Agama', 'MUH. ARSYAD HY., S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(1240, 'KAMIS', '6', '11:30 - 12:15', 'XI 3', 'Bahasa Indonesia', 'FITRIANI RAHMAT, S.Pd.', '2026-03-21 21:14:16'),
(1241, 'KAMIS', '6', '11:30 - 12:15', 'XI 4', 'Biologi (Pilihan)', 'CATHARINA NOVENI DWIDJADJANTI, S.Pd.', '2026-03-21 21:14:16'),
(1242, 'KAMIS', '6', '11:30 - 12:15', 'XI 5', 'Pendidikan Agama', 'SURYADI SYARIF, S.Pd.', '2026-03-21 21:14:16'),
(1243, 'KAMIS', '6', '11:30 - 12:15', 'XI 6', 'Seni Budaya', 'ANUGRAYANTI, S.Pd.', '2026-03-21 21:14:16'),
(1244, 'KAMIS', '6', '11:30 - 12:15', 'XI 7', 'PJOK', 'DWI SETIAWATI B., S.Pd.', '2026-03-21 21:14:16'),
(1245, 'KAMIS', '6', '11:30 - 12:15', 'XI 8', 'Matematika', 'HAMKA, S.Si., S.Pd., Gr.', '2026-03-21 21:14:16'),
(1246, 'KAMIS', '6', '11:30 - 12:15', 'XI 9', 'Bahasa Inggris', 'ARIFIN, S.Pd.', '2026-03-21 21:14:16'),
(1247, 'KAMIS', '6', '11:30 - 12:15', 'XI 10', 'Ekonomi (Pilihan)', 'MUHAMMAD YUSUF, S.Pd.', '2026-03-21 21:14:16'),
(1248, 'KAMIS', '6', '11:30 - 12:15', 'XI 11', 'Biologi (Pilihan)', 'NURSANTY, S.Pd.', '2026-03-21 21:14:16'),
(1249, 'KAMIS', '6', '11:30 - 12:15', 'XI 12', 'Bahasa Inggris', 'RISTA JUNIANTI SAHAR, SS.', '2026-03-21 21:14:16'),
(1250, 'KAMIS', '6', '11:30 - 12:15', 'XII 1', 'Bahasa Inggris', 'MUHAMMAD SALEH, S.Pd., MM.', '2026-03-21 21:14:16'),
(1251, 'KAMIS', '6', '11:30 - 12:15', 'XII 2', 'Prakarya', 'MUTMAINNAH, S.Pd., M.Biomed.', '2026-03-21 21:14:16'),
(1252, 'KAMIS', '6', '11:30 - 12:15', 'XII 3', 'Sejarah', 'DAMAYANTI, S.Pd.', '2026-03-21 21:14:16'),
(1253, 'KAMIS', '6', '11:30 - 12:15', 'XII 4', 'Seni Budaya', 'ST. NADIA KHAERATY, S.Pd.', '2026-03-21 21:14:16'),
(1254, 'KAMIS', '6', '11:30 - 12:15', 'XII 5', 'PJOK', 'ABD. KAMAL, S.Pd.', '2026-03-21 21:14:16'),
(1255, 'KAMIS', '6', '11:30 - 12:15', 'XII 6', 'Bahasa Indonesia', 'FITRIYANI, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(1256, 'KAMIS', '6', '11:30 - 12:15', 'XII 7', 'Pendidikan Agama', 'SITTI NAFI, S.Pd.I.', '2026-03-21 21:14:16'),
(1257, 'KAMIS', '6', '11:30 - 12:15', 'XII 8', 'Matematika', 'MASNIATI, S.Pd.', '2026-03-21 21:14:16'),
(1258, 'KAMIS', '6', '11:30 - 12:15', 'XII 9', 'Bahasa Inggris', 'AFIAH SAKIAH, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(1259, 'KAMIS', '6', '11:30 - 12:15', 'XII 10', 'Pendidikan Pancasila', 'EKA RUSNAENI, S.Pd., Gr.', '2026-03-21 21:14:16'),
(1260, 'KAMIS', '6', '11:30 - 12:15', 'XII 11', 'PJOK', 'MUH. GILANG FAJARI, S.Pd.', '2026-03-21 21:14:16'),
(1261, 'KAMIS', '6', '11:30 - 12:15', 'XII 12', 'Bahasa Indonesia', 'ST. ROSMAWATI M., S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(1262, 'KAMIS', '7', '12:50 - 13:30', 'X 1', 'Seni Budaya', 'ASWAN ANWAR, S.Pd.', '2026-03-21 21:14:16'),
(1263, 'KAMIS', '7', '12:50 - 13:30', 'X 2', 'Bahasa Inggris', 'ARIFIN, S.Pd.', '2026-03-21 21:14:16'),
(1264, 'KAMIS', '7', '12:50 - 13:30', 'X 3', 'Biologi', 'MINDJAH WATY M., S.Si.', '2026-03-21 21:14:16'),
(1265, 'KAMIS', '7', '12:50 - 13:30', 'X 4', 'Pendidikan Pancasila', 'YEKTIE NUR PRAYOGA LM, S.Pd., Gr.', '2026-03-21 21:14:16'),
(1266, 'KAMIS', '7', '12:50 - 13:30', 'X 5', 'Muatan Lokal (BTQ)', 'SURYADI SYARIF, S.Pd.', '2026-03-21 21:14:16'),
(1267, 'KAMIS', '7', '12:50 - 13:30', 'X 6', 'Bahasa Inggris', 'RANDIKA DHANI SYAPUTRA, S.Pd.', '2026-03-21 21:14:16'),
(1268, 'KAMIS', '7', '12:50 - 13:30', 'X 7', 'Muatan Lokal (BTQ)', 'ACHMAD MUFTI AL GALZA S., S.Pd.I.', '2026-03-21 21:14:16'),
(1269, 'KAMIS', '7', '12:50 - 13:30', 'X 8', 'Kimia', 'ISMAIL LA HASSE, S.Si., S.Pd.', '2026-03-21 21:14:16'),
(1270, 'KAMIS', '7', '12:50 - 13:30', 'X 9', 'Fisika', 'EVI YULIATI, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(1271, 'KAMIS', '7', '12:50 - 13:30', 'X 10', 'Matematika', 'SYAMSIAR, S.Pd.', '2026-03-21 21:14:16'),
(1272, 'KAMIS', '7', '12:50 - 13:30', 'X 11', 'Sejarah', 'ERNIATI, S.Pd.', '2026-03-21 21:14:16'),
(1273, 'KAMIS', '7', '12:50 - 13:30', 'XI 1', 'Matematika (Pilihan)', 'NUR QALBI RUSDIN, S.Pd.,M.Pd.', '2026-03-21 21:14:16'),
(1274, 'KAMIS', '7', '12:50 - 13:30', 'XI 2', 'Pendidikan Agama', 'MUH. ARSYAD HY., S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(1275, 'KAMIS', '7', '12:50 - 13:30', 'XI 3', 'Kimia (Pilihan)', 'SAPRUDDIN, S.Pd.', '2026-03-21 21:14:16'),
(1276, 'KAMIS', '7', '12:50 - 13:30', 'XI 4', 'Bahasa Indonesia', 'FITRIANI RAHMAT, S.Pd.', '2026-03-21 21:14:16'),
(1277, 'KAMIS', '7', '12:50 - 13:30', 'XI 5', 'Bahasa Inggris', 'RISTA JUNIANTI SAHAR, SS.', '2026-03-21 21:14:16'),
(1278, 'KAMIS', '7', '12:50 - 13:30', 'XI 6', 'Bahasa Indonesia', 'HASNA BAKRI, S.Pd.', '2026-03-21 21:14:16'),
(1279, 'KAMIS', '7', '12:50 - 13:30', 'XI 7', 'Bahasa Indonesia', 'A. FERAWATI SUT, S.Pd.', '2026-03-21 21:14:16'),
(1280, 'KAMIS', '7', '12:50 - 13:30', 'XI 8', 'Matematika', 'HAMKA, S.Si., S.Pd., Gr.', '2026-03-21 21:14:16'),
(1281, 'KAMIS', '7', '12:50 - 13:30', 'XI 9', 'Seni Budaya', 'ANUGRAYANTI, S.Pd.', '2026-03-21 21:14:16'),
(1282, 'KAMIS', '7', '12:50 - 13:30', 'XI 10', 'Matematika', 'HARTIWI PRADIKTA, S.Pd., Gr.', '2026-03-21 21:14:16'),
(1283, 'KAMIS', '7', '12:50 - 13:30', 'XI 11', 'Bahasa Indonesia', 'NURFATWA, S.Pd.', '2026-03-21 21:14:16'),
(1284, 'KAMIS', '7', '12:50 - 13:30', 'XI 12', 'Sejarah', 'ALI TAHIR, S.Pd.', '2026-03-21 21:14:16'),
(1285, 'KAMIS', '7', '12:50 - 13:30', 'XII 1', 'Bahasa Inggris', 'MUHAMMAD SALEH, S.Pd., MM.', '2026-03-21 21:14:16'),
(1286, 'KAMIS', '7', '12:50 - 13:30', 'XII 2', 'Pendidikan Pancasila', 'EKA RUSNAENI, S.Pd., Gr.', '2026-03-21 21:14:16'),
(1287, 'KAMIS', '7', '12:50 - 13:30', 'XII 3', 'Prakarya', 'MUTMAINNAH, S.Pd., M.Biomed.', '2026-03-21 21:14:16'),
(1288, 'KAMIS', '7', '12:50 - 13:30', 'XII 4', 'Bahasa Indonesia', 'FITRIYANI, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(1289, 'KAMIS', '7', '12:50 - 13:30', 'XII 5', 'Bahasa Indonesia', 'ST. ROSMAWATI M., S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(1290, 'KAMIS', '7', '12:50 - 13:30', 'XII 6', 'Seni Budaya', 'ST. NADIA KHAERATY, S.Pd.', '2026-03-21 21:14:16'),
(1291, 'KAMIS', '7', '12:50 - 13:30', 'XII 7', 'Sejarah', 'DAMAYANTI, S.Pd.', '2026-03-21 21:14:16'),
(1292, 'KAMIS', '7', '12:50 - 13:30', 'XII 8', 'Prakarya', 'SARWANA, S.Pd.', '2026-03-21 21:14:16'),
(1293, 'KAMIS', '7', '12:50 - 13:30', 'XII 9', 'Bahasa Inggris', 'AFIAH SAKIAH, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(1294, 'KAMIS', '7', '12:50 - 13:30', 'XII 10', 'Pendidikan Agama', 'YANTI NOVIANTI NAWAWI, S.Pd.I.', '2026-03-21 21:14:16'),
(1295, 'KAMIS', '7', '12:50 - 13:30', 'XII 11', 'Matematika', 'LILIES RATNA RIYANTINI, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(1296, 'KAMIS', '7', '12:50 - 13:30', 'XII 12', 'Pendidikan Agama', 'SITTI NAFI, S.Pd.I.', '2026-03-21 21:14:16'),
(1297, 'KAMIS', '8', '13:30 - 14:10', 'X 1', 'Seni Budaya', 'ASWAN ANWAR, S.Pd.', '2026-03-21 21:14:16'),
(1298, 'KAMIS', '8', '13:30 - 14:10', 'X 2', 'Bahasa Inggris', 'ARIFIN, S.Pd.', '2026-03-21 21:14:16'),
(1299, 'KAMIS', '8', '13:30 - 14:10', 'X 3', 'Biologi', 'MINDJAH WATY M., S.Si.', '2026-03-21 21:14:16'),
(1300, 'KAMIS', '8', '13:30 - 14:10', 'X 4', 'Pendidikan Pancasila', 'YEKTIE NUR PRAYOGA LM, S.Pd., Gr.', '2026-03-21 21:14:16'),
(1301, 'KAMIS', '8', '13:30 - 14:10', 'X 5', 'Bahasa Inggris', 'RISTA JUNIANTI SAHAR, SS.', '2026-03-21 21:14:16'),
(1302, 'KAMIS', '8', '13:30 - 14:10', 'X 6', 'Biologi', 'NURSANTY, S.Pd.', '2026-03-21 21:14:16'),
(1303, 'KAMIS', '8', '13:30 - 14:10', 'X 7', 'Muatan Lokal (BTQ)', 'ACHMAD MUFTI AL GALZA S., S.Pd.I.', '2026-03-21 21:14:16'),
(1304, 'KAMIS', '8', '13:30 - 14:10', 'X 8', 'Muatan Lokal (BTQ)', 'MUH. ARSYAD HY., S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(1305, 'KAMIS', '8', '13:30 - 14:10', 'X 9', 'Fisika', 'EVI YULIATI, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(1306, 'KAMIS', '8', '13:30 - 14:10', 'X 10', 'Matematika', 'SYAMSIAR, S.Pd.', '2026-03-21 21:14:16'),
(1307, 'KAMIS', '8', '13:30 - 14:10', 'X 11', 'Sejarah', 'ERNIATI, S.Pd.', '2026-03-21 21:14:16'),
(1308, 'KAMIS', '8', '13:30 - 14:10', 'XI 1', 'Matematika (Pilihan)', 'NUR QALBI RUSDIN, S.Pd.,M.Pd.', '2026-03-21 21:14:16'),
(1309, 'KAMIS', '8', '13:30 - 14:10', 'XI 2', 'Biologi (Pilihan)', 'CATHARINA NOVENI DWIDJADJANTI, S.Pd.', '2026-03-21 21:14:16'),
(1310, 'KAMIS', '8', '13:30 - 14:10', 'XI 3', 'Kimia (Pilihan)', 'SAPRUDDIN, S.Pd.', '2026-03-21 21:14:16'),
(1311, 'KAMIS', '8', '13:30 - 14:10', 'XI 4', 'Bahasa Indonesia', 'FITRIANI RAHMAT, S.Pd.', '2026-03-21 21:14:16'),
(1312, 'KAMIS', '8', '13:30 - 14:10', 'XI 5', 'Pendidikan Agama', 'SURYADI SYARIF, S.Pd.', '2026-03-21 21:14:16'),
(1313, 'KAMIS', '8', '13:30 - 14:10', 'XI 6', 'Bahasa Indonesia', 'HASNA BAKRI, S.Pd.', '2026-03-21 21:14:16'),
(1314, 'KAMIS', '8', '13:30 - 14:10', 'XI 7', 'Bahasa Indonesia', 'A. FERAWATI SUT, S.Pd.', '2026-03-21 21:14:16'),
(1315, 'KAMIS', '8', '13:30 - 14:10', 'XI 8', 'Ekonomi (Pilihan)', 'MUHAMMAD YUSUF, S.Pd.', '2026-03-21 21:14:16'),
(1316, 'KAMIS', '8', '13:30 - 14:10', 'XI 9', 'Seni Budaya', 'ANUGRAYANTI, S.Pd.', '2026-03-21 21:14:16'),
(1317, 'KAMIS', '8', '13:30 - 14:10', 'XI 10', 'Matematika', 'HARTIWI PRADIKTA, S.Pd., Gr.', '2026-03-21 21:14:16'),
(1318, 'KAMIS', '8', '13:30 - 14:10', 'XI 11', 'Bahasa Indonesia', 'NURFATWA, S.Pd.', '2026-03-21 21:14:16'),
(1319, 'KAMIS', '8', '13:30 - 14:10', 'XI 12', 'Sejarah', 'ALI TAHIR, S.Pd.', '2026-03-21 21:14:16'),
(1320, 'KAMIS', '8', '13:30 - 14:10', 'XII 1', 'Seni Budaya', 'ST. NADIA KHAERATY, S.Pd.', '2026-03-21 21:14:16'),
(1321, 'KAMIS', '8', '13:30 - 14:10', 'XII 2', 'Pendidikan Pancasila', 'EKA RUSNAENI, S.Pd., Gr.', '2026-03-21 21:14:16'),
(1322, 'KAMIS', '8', '13:30 - 14:10', 'XII 3', 'Prakarya', 'MUTMAINNAH, S.Pd., M.Biomed.', '2026-03-21 21:14:16'),
(1323, 'KAMIS', '8', '13:30 - 14:10', 'XII 4', 'Pendidikan Agama', 'SITTI NAFI, S.Pd.I.', '2026-03-21 21:14:16'),
(1324, 'KAMIS', '8', '13:30 - 14:10', 'XII 5', 'Bahasa Indonesia', 'ST. ROSMAWATI M., S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(1325, 'KAMIS', '8', '13:30 - 14:10', 'XII 6', 'Matematika', 'MASNIATI, S.Pd.', '2026-03-21 21:14:16'),
(1326, 'KAMIS', '8', '13:30 - 14:10', 'XII 7', 'Sejarah', 'DAMAYANTI, S.Pd.', '2026-03-21 21:14:16'),
(1327, 'KAMIS', '8', '13:30 - 14:10', 'XII 8', 'Prakarya', 'SARWANA, S.Pd.', '2026-03-21 21:14:16'),
(1328, 'KAMIS', '8', '13:30 - 14:10', 'XII 9', 'Pendidikan Agama', 'YANTI NOVIANTI NAWAWI, S.Pd.I.', '2026-03-21 21:14:16'),
(1329, 'KAMIS', '8', '13:30 - 14:10', 'XII 10', 'Bahasa Inggris', 'AFIAH SAKIAH, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(1330, 'KAMIS', '8', '13:30 - 14:10', 'XII 11', 'Matematika', 'LILIES RATNA RIYANTINI, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(1331, 'KAMIS', '8', '13:30 - 14:10', 'XII 12', 'Bahasa Inggris', 'MUHAMMAD SALEH, S.Pd., MM.', '2026-03-21 21:14:16'),
(1332, 'KAMIS', '9', '14:10 - 14:50', 'X 1', 'Pendidikan Pancasila', 'YEKTIE NUR PRAYOGA LM, S.Pd., Gr.', '2026-03-21 21:14:16'),
(1333, 'KAMIS', '9', '14:10 - 14:50', 'X 2', 'Bahasa Inggris', 'ARIFIN, S.Pd.', '2026-03-21 21:14:16'),
(1334, 'KAMIS', '9', '14:10 - 14:50', 'X 3', 'Bahasa Indonesia', 'NURFATWA, S.Pd.', '2026-03-21 21:14:16');
INSERT INTO `schedules` (`id`, `hari`, `jam_ke`, `waktu`, `kelas`, `mata_pelajaran`, `nama_guru`, `created_at`) VALUES
(1335, 'KAMIS', '9', '14:10 - 14:50', 'X 4', 'Muatan Lokal (BTQ)', 'SURYADI SYARIF, S.Pd.', '2026-03-21 21:14:16'),
(1336, 'KAMIS', '9', '14:10 - 14:50', 'X 5', 'Bahasa Inggris', 'RISTA JUNIANTI SAHAR, SS.', '2026-03-21 21:14:16'),
(1337, 'KAMIS', '9', '14:10 - 14:50', 'X 6', 'Biologi', 'NURSANTY, S.Pd.', '2026-03-21 21:14:16'),
(1338, 'KAMIS', '9', '14:10 - 14:50', 'X 7', 'Matematika', 'SYAMSIAR, S.Pd.', '2026-03-21 21:14:16'),
(1339, 'KAMIS', '9', '14:10 - 14:50', 'X 8', 'Seni Budaya', 'ASWAN ANWAR, S.Pd.', '2026-03-21 21:14:16'),
(1340, 'KAMIS', '9', '14:10 - 14:50', 'X 9', 'Fisika', 'EVI YULIATI, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(1341, 'KAMIS', '9', '14:10 - 14:50', 'X 10', 'Bahasa Indonesia', 'A. FERAWATI SUT, S.Pd.', '2026-03-21 21:14:16'),
(1342, 'KAMIS', '9', '14:10 - 14:50', 'X 11', 'Bahasa Indonesia', 'NUR AKBAR, S.Pd.', '2026-03-21 21:14:16'),
(1343, 'KAMIS', '9', '14:10 - 14:50', 'XI 1', 'Bahasa Inggris (Pilihan)', 'RANDIKA DHANI SYAPUTRA, S.Pd.', '2026-03-21 21:14:16'),
(1344, 'KAMIS', '9', '14:10 - 14:50', 'XI 2', 'Biologi (Pilihan)', 'CATHARINA NOVENI DWIDJADJANTI, S.Pd.', '2026-03-21 21:14:16'),
(1345, 'KAMIS', '9', '14:10 - 14:50', 'XI 3', 'Sejarah', 'ALI TAHIR, S.Pd.', '2026-03-21 21:14:16'),
(1346, 'KAMIS', '9', '14:10 - 14:50', 'XI 4', 'Pendidikan Agama', 'YANTI NOVIANTI NAWAWI, S.Pd.I.', '2026-03-21 21:14:16'),
(1347, 'KAMIS', '9', '14:10 - 14:50', 'XI 5', 'Informatika (Pilihan)', 'RIZA RISKY YULIANTI, S.Pd., Gr.', '2026-03-21 21:14:16'),
(1348, 'KAMIS', '9', '14:10 - 14:50', 'XI 6', 'Matematika', 'HARTIWI PRADIKTA, S.Pd., Gr.', '2026-03-21 21:14:16'),
(1349, 'KAMIS', '9', '14:10 - 14:50', 'XI 7', 'Pendidikan Agama', 'MUH. ARSYAD HY., S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(1350, 'KAMIS', '9', '14:10 - 14:50', 'XI 8', 'Ekonomi (Pilihan)', 'MUHAMMAD YUSUF, S.Pd.', '2026-03-21 21:14:16'),
(1351, 'KAMIS', '9', '14:10 - 14:50', 'XI 9', 'Geografi (Pilihan)', 'HALIFUDDIN, S.Pd.', '2026-03-21 21:14:16'),
(1352, 'KAMIS', '9', '14:10 - 14:50', 'XI 10', 'Bahasa Indonesia', 'FITRIANI RAHMAT, S.Pd.', '2026-03-21 21:14:16'),
(1353, 'KAMIS', '9', '14:10 - 14:50', 'XI 11', 'Matematika', 'NUR QALBI RUSDIN, S.Pd.,M.Pd.', '2026-03-21 21:14:16'),
(1354, 'KAMIS', '9', '14:10 - 14:50', 'XI 12', 'Matematika', 'NURAFNI PEBRIANTY, S.Pd., Gr.', '2026-03-21 21:14:16'),
(1355, 'KAMIS', '9', '14:10 - 14:50', 'XII 1', 'Prakarya', 'MUTMAINNAH, S.Pd., M.Biomed.', '2026-03-21 21:14:16'),
(1356, 'KAMIS', '9', '14:10 - 14:50', 'XII 2', 'Bahasa Indonesia', 'FITRIYANI, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(1357, 'KAMIS', '9', '14:10 - 14:50', 'XII 3', 'Seni Budaya', 'ST. NADIA KHAERATY, S.Pd.', '2026-03-21 21:14:16'),
(1358, 'KAMIS', '9', '14:10 - 14:50', 'XII 4', 'Pendidikan Agama', 'SITTI NAFI, S.Pd.I.', '2026-03-21 21:14:16'),
(1359, 'KAMIS', '9', '14:10 - 14:50', 'XII 5', 'Pendidikan Pancasila', 'EKA RUSNAENI, S.Pd., Gr.', '2026-03-21 21:14:16'),
(1360, 'KAMIS', '9', '14:10 - 14:50', 'XII 6', 'Matematika', 'MASNIATI, S.Pd.', '2026-03-21 21:14:16'),
(1361, 'KAMIS', '9', '14:10 - 14:50', 'XII 7', 'Prakarya', 'SARWANA, S.Pd.', '2026-03-21 21:14:16'),
(1362, 'KAMIS', '9', '14:10 - 14:50', 'XII 8', 'Bahasa Indonesia', 'ST. ROSMAWATI M., S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(1363, 'KAMIS', '9', '14:10 - 14:50', 'XII 9', 'Sejarah', 'DAMAYANTI, S.Pd.', '2026-03-21 21:14:16'),
(1364, 'KAMIS', '9', '14:10 - 14:50', 'XII 10', 'Bahasa Inggris', 'AFIAH SAKIAH, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(1365, 'KAMIS', '9', '14:10 - 14:50', 'XII 11', 'Seni Budaya', 'ANUGRAYANTI, S.Pd.', '2026-03-21 21:14:16'),
(1366, 'KAMIS', '9', '14:10 - 14:50', 'XII 12', 'Bahasa Inggris', 'MUHAMMAD SALEH, S.Pd., MM.', '2026-03-21 21:14:16'),
(1367, 'KAMIS', '10', '14:50 - 15:30', 'X 1', 'Pendidikan Pancasila', 'YEKTIE NUR PRAYOGA LM, S.Pd., Gr.', '2026-03-21 21:14:16'),
(1368, 'KAMIS', '10', '14:50 - 15:30', 'X 2', 'Biologi', 'MINDJAH WATY M., S.Si.', '2026-03-21 21:14:16'),
(1369, 'KAMIS', '10', '14:50 - 15:30', 'X 3', 'Bahasa Indonesia', 'NURFATWA, S.Pd.', '2026-03-21 21:14:16'),
(1370, 'KAMIS', '10', '14:50 - 15:30', 'X 4', 'Muatan Lokal (BTQ)', 'SURYADI SYARIF, S.Pd.', '2026-03-21 21:14:16'),
(1371, 'KAMIS', '10', '14:50 - 15:30', 'X 5', 'Bahasa Inggris', 'RISTA JUNIANTI SAHAR, SS.', '2026-03-21 21:14:16'),
(1372, 'KAMIS', '10', '14:50 - 15:30', 'X 6', 'Biologi', 'NURSANTY, S.Pd.', '2026-03-21 21:14:16'),
(1373, 'KAMIS', '10', '14:50 - 15:30', 'X 7', 'Matematika', 'SYAMSIAR, S.Pd.', '2026-03-21 21:14:16'),
(1374, 'KAMIS', '10', '14:50 - 15:30', 'X 8', 'Seni Budaya', 'ASWAN ANWAR, S.Pd.', '2026-03-21 21:14:16'),
(1375, 'KAMIS', '10', '14:50 - 15:30', 'X 9', 'Muatan Lokal (BTQ)', 'ACHMAD MUFTI AL GALZA S., S.Pd.I.', '2026-03-21 21:14:16'),
(1376, 'KAMIS', '10', '14:50 - 15:30', 'X 10', 'Bahasa Indonesia', 'A. FERAWATI SUT, S.Pd.', '2026-03-21 21:14:16'),
(1377, 'KAMIS', '10', '14:50 - 15:30', 'X 11', 'Bahasa Indonesia', 'NUR AKBAR, S.Pd.', '2026-03-21 21:14:16'),
(1378, 'KAMIS', '10', '14:50 - 15:30', 'XI 1', 'Bahasa Inggris (Pilihan)', 'RANDIKA DHANI SYAPUTRA, S.Pd.', '2026-03-21 21:14:16'),
(1379, 'KAMIS', '10', '14:50 - 15:30', 'XI 2', 'Biologi (Pilihan)', 'CATHARINA NOVENI DWIDJADJANTI, S.Pd.', '2026-03-21 21:14:16'),
(1380, 'KAMIS', '10', '14:50 - 15:30', 'XI 3', 'Sejarah', 'ALI TAHIR, S.Pd.', '2026-03-21 21:14:16'),
(1381, 'KAMIS', '10', '14:50 - 15:30', 'XI 4', 'Pendidikan Agama', 'YANTI NOVIANTI NAWAWI, S.Pd.I.', '2026-03-21 21:14:16'),
(1382, 'KAMIS', '10', '14:50 - 15:30', 'XI 5', 'Informatika (Pilihan)', 'RIZA RISKY YULIANTI, S.Pd., Gr.', '2026-03-21 21:14:16'),
(1383, 'KAMIS', '10', '14:50 - 15:30', 'XI 6', 'Matematika', 'HARTIWI PRADIKTA, S.Pd., Gr.', '2026-03-21 21:14:16'),
(1384, 'KAMIS', '10', '14:50 - 15:30', 'XI 7', 'Pendidikan Agama', 'MUH. ARSYAD HY., S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(1385, 'KAMIS', '10', '14:50 - 15:30', 'XI 8', 'Ekonomi (Pilihan)', 'MUHAMMAD YUSUF, S.Pd.', '2026-03-21 21:14:16'),
(1386, 'KAMIS', '10', '14:50 - 15:30', 'XI 9', 'Geografi (Pilihan)', 'HALIFUDDIN, S.Pd.', '2026-03-21 21:14:16'),
(1387, 'KAMIS', '10', '14:50 - 15:30', 'XI 10', 'Bahasa Indonesia', 'FITRIANI RAHMAT, S.Pd.', '2026-03-21 21:14:16'),
(1388, 'KAMIS', '10', '14:50 - 15:30', 'XI 11', 'Matematika', 'NUR QALBI RUSDIN, S.Pd.,M.Pd.', '2026-03-21 21:14:16'),
(1389, 'KAMIS', '10', '14:50 - 15:30', 'XI 12', 'Matematika', 'NURAFNI PEBRIANTY, S.Pd., Gr.', '2026-03-21 21:14:16'),
(1390, 'KAMIS', '10', '14:50 - 15:30', 'XII 1', 'Prakarya', 'MUTMAINNAH, S.Pd., M.Biomed.', '2026-03-21 21:14:16'),
(1391, 'KAMIS', '10', '14:50 - 15:30', 'XII 2', 'Bahasa Indonesia', 'FITRIYANI, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(1392, 'KAMIS', '10', '14:50 - 15:30', 'XII 3', 'Seni Budaya', 'ST. NADIA KHAERATY, S.Pd.', '2026-03-21 21:14:16'),
(1393, 'KAMIS', '10', '14:50 - 15:30', 'XII 4', 'Pendidikan Agama', 'SITTI NAFI, S.Pd.I.', '2026-03-21 21:14:16'),
(1394, 'KAMIS', '10', '14:50 - 15:30', 'XII 5', 'Pendidikan Pancasila', 'EKA RUSNAENI, S.Pd., Gr.', '2026-03-21 21:14:16'),
(1395, 'KAMIS', '10', '14:50 - 15:30', 'XII 6', 'Matematika', 'MASNIATI, S.Pd.', '2026-03-21 21:14:16'),
(1396, 'KAMIS', '10', '14:50 - 15:30', 'XII 7', 'Prakarya', 'SARWANA, S.Pd.', '2026-03-21 21:14:16'),
(1397, 'KAMIS', '10', '14:50 - 15:30', 'XII 8', 'Bahasa Indonesia', 'ST. ROSMAWATI M., S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(1398, 'KAMIS', '10', '14:50 - 15:30', 'XII 9', 'Sejarah', 'DAMAYANTI, S.Pd.', '2026-03-21 21:14:16'),
(1399, 'KAMIS', '10', '14:50 - 15:30', 'XII 10', 'Bahasa Inggris', 'AFIAH SAKIAH, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(1400, 'KAMIS', '10', '14:50 - 15:30', 'XII 11', 'Seni Budaya', 'ANUGRAYANTI, S.Pd.', '2026-03-21 21:14:16'),
(1401, 'KAMIS', '10', '14:50 - 15:30', 'XII 12', 'Bahasa Inggris', 'MUHAMMAD SALEH, S.Pd., MM.', '2026-03-21 21:14:16'),
(1402, 'JUMAT', '1', '07:15 - 08:00', 'X 1', 'FUTSAL GURU DAN TENAGA KEPENDIDIKAN', 'SENAM ANAK INDONESIA HEBAT', '2026-03-21 21:14:16'),
(1403, 'JUMAT', '2', '08:00 - 08:45', 'X 1', 'Matematika', 'SYAMSIAR, S.Pd.', '2026-03-21 21:14:16'),
(1404, 'JUMAT', '2', '08:00 - 08:45', 'X 2', 'Sosiologi', 'ADE HANDAYANI, S.Sos., M.Pd., Gr.', '2026-03-21 21:14:16'),
(1405, 'JUMAT', '2', '08:00 - 08:45', 'X 3', 'Sejarah', 'ERNIATI, S.Pd.', '2026-03-21 21:14:16'),
(1406, 'JUMAT', '2', '08:00 - 08:45', 'X 4', 'Bahasa Indonesia', 'NURFATWA, S.Pd.', '2026-03-21 21:14:16'),
(1407, 'JUMAT', '2', '08:00 - 08:45', 'X 5', 'Fisika', 'MUH. TRI PRASETIA NUA, S.Pd., Gr.', '2026-03-21 21:14:16'),
(1408, 'JUMAT', '2', '08:00 - 08:45', 'X 6', 'Informatika', 'NURAFNI PEBRIANTY, S.Pd., Gr.', '2026-03-21 21:14:16'),
(1409, 'JUMAT', '2', '08:00 - 08:45', 'X 7', 'Pendidikan Pancasila', 'YEKTIE NUR PRAYOGA LM, S.Pd., Gr.', '2026-03-21 21:14:16'),
(1410, 'JUMAT', '2', '08:00 - 08:45', 'X 8', 'Pendidikan Agama', 'SURYADI SYARIF, S.Pd.', '2026-03-21 21:14:16'),
(1411, 'JUMAT', '2', '08:00 - 08:45', 'X 9', 'PJOK', 'ASRIANTO, S.Or., Gr.', '2026-03-21 21:14:16'),
(1412, 'JUMAT', '2', '08:00 - 08:45', 'X 10', 'PJOK', 'MUH. GILANG FAJARI, S.Pd.', '2026-03-21 21:14:16'),
(1413, 'JUMAT', '2', '08:00 - 08:45', 'X 11', 'Ekonomi', 'A. VEBHY INDAH BELLIANTHARI, S.Pd.', '2026-03-21 21:14:16'),
(1414, 'JUMAT', '2', '08:00 - 08:45', 'XI 1', 'PJOK', 'DWI SETIAWATI B., S.Pd.', '2026-03-21 21:14:16'),
(1415, 'JUMAT', '2', '08:00 - 08:45', 'XI 2', 'Bahasa Indonesia', 'A. FERAWATI SUT, S.Pd.', '2026-03-21 21:14:16'),
(1416, 'JUMAT', '2', '08:00 - 08:45', 'XI 3', 'Bahasa Indonesia', 'FITRIANI RAHMAT, S.Pd.', '2026-03-21 21:14:16'),
(1417, 'JUMAT', '2', '08:00 - 08:45', 'XI 4', 'Informatika (Pilihan)', 'RIZA RISKY YULIANTI, S.Pd., Gr.', '2026-03-21 21:14:16'),
(1418, 'JUMAT', '2', '08:00 - 08:45', 'XI 5', 'Matematika', 'MASNIATI, S.Pd.', '2026-03-21 21:14:16'),
(1419, 'JUMAT', '2', '08:00 - 08:45', 'XI 6', 'PJOK', 'ABD. KAMAL, S.Pd.', '2026-03-21 21:14:16'),
(1420, 'JUMAT', '2', '08:00 - 08:45', 'XI 7', 'Seni Budaya', 'ANUGRAYANTI, S.Pd.', '2026-03-21 21:14:16'),
(1421, 'JUMAT', '2', '08:00 - 08:45', 'XI 8', 'Bahasa Inggris', 'ARIFIN, S.Pd.', '2026-03-21 21:14:16'),
(1422, 'JUMAT', '2', '08:00 - 08:45', 'XI 9', 'Bahasa Inggris (Pilihan)', 'RANDIKA DHANI SYAPUTRA, S.Pd.', '2026-03-21 21:14:16'),
(1423, 'JUMAT', '2', '08:00 - 08:45', 'XI 10', 'Matematika', 'HARTIWI PRADIKTA, S.Pd., Gr.', '2026-03-21 21:14:16'),
(1424, 'JUMAT', '2', '08:00 - 08:45', 'XI 11', 'Bahasa Inggris', 'RISTA JUNIANTI SAHAR, SS.', '2026-03-21 21:14:16'),
(1425, 'JUMAT', '2', '08:00 - 08:45', 'XI 12', 'Sosiologi (Pilihan)', 'SRI UTAMI WULANSARI, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(1426, 'JUMAT', '2', '08:00 - 08:45', 'XII 1', 'Bahasa Inggris (Pilihan)', 'AFIAH SAKIAH, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(1427, 'JUMAT', '2', '08:00 - 08:45', 'XII 2', 'Geografi (Pilihan)', 'SANTI KUSUMA DEWI, S.Pd.', '2026-03-21 21:14:16'),
(1428, 'JUMAT', '2', '08:00 - 08:45', 'XII 3', 'Matematika (Pilihan)', 'NUR QALBI RUSDIN, S.Pd.,M.Pd.', '2026-03-21 21:14:16'),
(1429, 'JUMAT', '2', '08:00 - 08:45', 'XII 4', 'Ekonomi (Pilihan)', 'MUHAMMAD YUSUF, S.Pd.', '2026-03-21 21:14:16'),
(1430, 'JUMAT', '2', '08:00 - 08:45', 'XII 5', 'Geografi (Pilihan)', 'Drs. BAHARUDDIN', '2026-03-21 21:14:16'),
(1431, 'JUMAT', '2', '08:00 - 08:45', 'XII 6', 'Geografi (Pilihan)', 'HALIFUDDIN, S.Pd.', '2026-03-21 21:14:16'),
(1432, 'JUMAT', '2', '08:00 - 08:45', 'XII 7', 'Bahasa Indonesia (Pilihan)', 'HASNA BAKRI, S.Pd.', '2026-03-21 21:14:16'),
(1433, 'JUMAT', '2', '08:00 - 08:45', 'XII 8', 'Biologi (Pilihan)', 'Drs. MUHAMMAD HAMZAH, MM.', '2026-03-21 21:14:16'),
(1434, 'JUMAT', '2', '08:00 - 08:45', 'XII 9', 'Ekonomi (Pilihan)', 'Dr. Hj. ROSMAWATI, SE.,MA.', '2026-03-21 21:14:16'),
(1435, 'JUMAT', '2', '08:00 - 08:45', 'XII 10', 'Matematika (Pilihan)', 'HAMKA, S.Si., S.Pd., Gr.', '2026-03-21 21:14:16'),
(1436, 'JUMAT', '2', '08:00 - 08:45', 'XII 11', 'Biologi (Pilihan)', 'HAMIDATUSSIFAH, S.Si.', '2026-03-21 21:14:16'),
(1437, 'JUMAT', '2', '08:00 - 08:45', 'XII 12', 'Kimia (Pilihan)', 'SAPRUDDIN, S.Pd.', '2026-03-21 21:14:16'),
(1438, 'JUMAT', '3', '08:45 - 09:30', 'X 1', 'Matematika', 'SYAMSIAR, S.Pd.', '2026-03-21 21:14:16'),
(1439, 'JUMAT', '3', '08:45 - 09:30', 'X 2', 'Sosiologi', 'ADE HANDAYANI, S.Sos., M.Pd., Gr.', '2026-03-21 21:14:16'),
(1440, 'JUMAT', '3', '08:45 - 09:30', 'X 3', 'Sejarah', 'ERNIATI, S.Pd.', '2026-03-21 21:14:16'),
(1441, 'JUMAT', '3', '08:45 - 09:30', 'X 4', 'Bahasa Indonesia', 'NURFATWA, S.Pd.', '2026-03-21 21:14:16'),
(1442, 'JUMAT', '3', '08:45 - 09:30', 'X 5', 'Fisika', 'MUH. TRI PRASETIA NUA, S.Pd., Gr.', '2026-03-21 21:14:16'),
(1443, 'JUMAT', '3', '08:45 - 09:30', 'X 6', 'Informatika', 'NURAFNI PEBRIANTY, S.Pd., Gr.', '2026-03-21 21:14:16'),
(1444, 'JUMAT', '3', '08:45 - 09:30', 'X 7', 'Pendidikan Pancasila', 'YEKTIE NUR PRAYOGA LM, S.Pd., Gr.', '2026-03-21 21:14:16'),
(1445, 'JUMAT', '3', '08:45 - 09:30', 'X 8', 'Pendidikan Agama', 'SURYADI SYARIF, S.Pd.', '2026-03-21 21:14:16'),
(1446, 'JUMAT', '3', '08:45 - 09:30', 'X 9', 'PJOK', 'ASRIANTO, S.Or., Gr.', '2026-03-21 21:14:16'),
(1447, 'JUMAT', '3', '08:45 - 09:30', 'X 10', 'PJOK', 'MUH. GILANG FAJARI, S.Pd.', '2026-03-21 21:14:16'),
(1448, 'JUMAT', '3', '08:45 - 09:30', 'X 11', 'Ekonomi', 'A. VEBHY INDAH BELLIANTHARI, S.Pd.', '2026-03-21 21:14:16'),
(1449, 'JUMAT', '3', '08:45 - 09:30', 'XI 1', 'PJOK', 'DWI SETIAWATI B., S.Pd.', '2026-03-21 21:14:16'),
(1450, 'JUMAT', '3', '08:45 - 09:30', 'XI 2', 'Bahasa Indonesia', 'A. FERAWATI SUT, S.Pd.', '2026-03-21 21:14:16'),
(1451, 'JUMAT', '3', '08:45 - 09:30', 'XI 3', 'Bahasa Indonesia', 'FITRIANI RAHMAT, S.Pd.', '2026-03-21 21:14:16'),
(1452, 'JUMAT', '3', '08:45 - 09:30', 'XI 4', 'Informatika (Pilihan)', 'RIZA RISKY YULIANTI, S.Pd., Gr.', '2026-03-21 21:14:16'),
(1453, 'JUMAT', '3', '08:45 - 09:30', 'XI 5', 'Matematika', 'MASNIATI, S.Pd.', '2026-03-21 21:14:16'),
(1454, 'JUMAT', '3', '08:45 - 09:30', 'XI 6', 'PJOK', 'ABD. KAMAL, S.Pd.', '2026-03-21 21:14:16'),
(1455, 'JUMAT', '3', '08:45 - 09:30', 'XI 7', 'Seni Budaya', 'ANUGRAYANTI, S.Pd.', '2026-03-21 21:14:16'),
(1456, 'JUMAT', '3', '08:45 - 09:30', 'XI 8', 'Bahasa Inggris', 'ARIFIN, S.Pd.', '2026-03-21 21:14:16'),
(1457, 'JUMAT', '3', '08:45 - 09:30', 'XI 9', 'Bahasa Inggris (Pilihan)', 'RANDIKA DHANI SYAPUTRA, S.Pd.', '2026-03-21 21:14:16'),
(1458, 'JUMAT', '3', '08:45 - 09:30', 'XI 10', 'Matematika', 'HARTIWI PRADIKTA, S.Pd., Gr.', '2026-03-21 21:14:16'),
(1459, 'JUMAT', '3', '08:45 - 09:30', 'XI 11', 'Bahasa Inggris', 'RISTA JUNIANTI SAHAR, SS.', '2026-03-21 21:14:16'),
(1460, 'JUMAT', '3', '08:45 - 09:30', 'XI 12', 'Sosiologi (Pilihan)', 'SRI UTAMI WULANSARI, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(1461, 'JUMAT', '3', '08:45 - 09:30', 'XII 1', 'Bahasa Inggris (Pilihan)', 'AFIAH SAKIAH, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(1462, 'JUMAT', '3', '08:45 - 09:30', 'XII 2', 'Geografi (Pilihan)', 'SANTI KUSUMA DEWI, S.Pd.', '2026-03-21 21:14:16'),
(1463, 'JUMAT', '3', '08:45 - 09:30', 'XII 3', 'Matematika (Pilihan)', 'NUR QALBI RUSDIN, S.Pd.,M.Pd.', '2026-03-21 21:14:16'),
(1464, 'JUMAT', '3', '08:45 - 09:30', 'XII 4', 'Ekonomi (Pilihan)', 'MUHAMMAD YUSUF, S.Pd.', '2026-03-21 21:14:16'),
(1465, 'JUMAT', '3', '08:45 - 09:30', 'XII 5', 'Geografi (Pilihan)', 'Drs. BAHARUDDIN', '2026-03-21 21:14:16'),
(1466, 'JUMAT', '3', '08:45 - 09:30', 'XII 6', 'Geografi (Pilihan)', 'HALIFUDDIN, S.Pd.', '2026-03-21 21:14:16'),
(1467, 'JUMAT', '3', '08:45 - 09:30', 'XII 7', 'Bahasa Indonesia (Pilihan)', 'HASNA BAKRI, S.Pd.', '2026-03-21 21:14:16'),
(1468, 'JUMAT', '3', '08:45 - 09:30', 'XII 8', 'Biologi (Pilihan)', 'Drs. MUHAMMAD HAMZAH, MM.', '2026-03-21 21:14:16'),
(1469, 'JUMAT', '3', '08:45 - 09:30', 'XII 9', 'Ekonomi (Pilihan)', 'Dr. Hj. ROSMAWATI, SE.,MA.', '2026-03-21 21:14:16'),
(1470, 'JUMAT', '3', '08:45 - 09:30', 'XII 10', 'Matematika (Pilihan)', 'HAMKA, S.Si., S.Pd., Gr.', '2026-03-21 21:14:16'),
(1471, 'JUMAT', '3', '08:45 - 09:30', 'XII 11', 'Biologi (Pilihan)', 'HAMIDATUSSIFAH, S.Si.', '2026-03-21 21:14:16'),
(1472, 'JUMAT', '3', '08:45 - 09:30', 'XII 12', 'Kimia (Pilihan)', 'SAPRUDDIN, S.Pd.', '2026-03-21 21:14:16'),
(1473, 'JUMAT', '4', '09:30 - 10:15', 'X 1', 'Bahasa Inggris', 'ARIFIN, S.Pd.', '2026-03-21 21:14:16'),
(1474, 'JUMAT', '4', '09:30 - 10:15', 'X 2', 'Sosiologi', 'ADE HANDAYANI, S.Sos., M.Pd., Gr.', '2026-03-21 21:14:16'),
(1475, 'JUMAT', '4', '09:30 - 10:15', 'X 3', 'Sejarah', 'ERNIATI, S.Pd.', '2026-03-21 21:14:16'),
(1476, 'JUMAT', '4', '09:30 - 10:15', 'X 4', 'Bahasa Indonesia', 'NURFATWA, S.Pd.', '2026-03-21 21:14:16'),
(1477, 'JUMAT', '4', '09:30 - 10:15', 'X 5', 'Fisika', 'MUH. TRI PRASETIA NUA, S.Pd., Gr.', '2026-03-21 21:14:16'),
(1478, 'JUMAT', '4', '09:30 - 10:15', 'X 6', 'Pendidikan Pancasila', 'YEKTIE NUR PRAYOGA LM, S.Pd., Gr.', '2026-03-21 21:14:16'),
(1479, 'JUMAT', '4', '09:30 - 10:15', 'X 7', 'Informatika', 'RIZA RISKY YULIANTI, S.Pd., Gr.', '2026-03-21 21:14:16'),
(1480, 'JUMAT', '4', '09:30 - 10:15', 'X 8', 'Pendidikan Agama', 'SURYADI SYARIF, S.Pd.', '2026-03-21 21:14:16'),
(1481, 'JUMAT', '4', '09:30 - 10:15', 'X 9', 'PJOK', 'ASRIANTO, S.Or., Gr.', '2026-03-21 21:14:16'),
(1482, 'JUMAT', '4', '09:30 - 10:15', 'X 10', 'PJOK', 'MUH. GILANG FAJARI, S.Pd.', '2026-03-21 21:14:16'),
(1483, 'JUMAT', '4', '09:30 - 10:15', 'X 11', 'Ekonomi', 'A. VEBHY INDAH BELLIANTHARI, S.Pd.', '2026-03-21 21:14:16'),
(1484, 'JUMAT', '4', '09:30 - 10:15', 'XI 1', 'PJOK', 'DWI SETIAWATI B., S.Pd.', '2026-03-21 21:14:16'),
(1485, 'JUMAT', '4', '09:30 - 10:15', 'XI 2', 'Bahasa Indonesia', 'A. FERAWATI SUT, S.Pd.', '2026-03-21 21:14:16'),
(1486, 'JUMAT', '4', '09:30 - 10:15', 'XI 3', 'Matematika', 'NURAFNI PEBRIANTY, S.Pd., Gr.', '2026-03-21 21:14:16'),
(1487, 'JUMAT', '4', '09:30 - 10:15', 'XI 4', 'Seni Budaya', 'ST. NADIA KHAERATY, S.Pd.', '2026-03-21 21:14:16'),
(1488, 'JUMAT', '4', '09:30 - 10:15', 'XI 5', 'Prakarya', 'SARWANA, S.Pd.', '2026-03-21 21:14:16'),
(1489, 'JUMAT', '4', '09:30 - 10:15', 'XI 6', 'PJOK', 'ABD. KAMAL, S.Pd.', '2026-03-21 21:14:16'),
(1490, 'JUMAT', '4', '09:30 - 10:15', 'XI 7', 'Sejarah', 'ALI TAHIR, S.Pd.', '2026-03-21 21:14:16'),
(1491, 'JUMAT', '4', '09:30 - 10:15', 'XI 8', 'Pendidikan Pancasila', 'EKA RUSNAENI, S.Pd., Gr.', '2026-03-21 21:14:16'),
(1492, 'JUMAT', '4', '09:30 - 10:15', 'XI 9', 'Bahasa Inggris (Pilihan)', 'RANDIKA DHANI SYAPUTRA, S.Pd.', '2026-03-21 21:14:16'),
(1493, 'JUMAT', '4', '09:30 - 10:15', 'XI 10', 'Prakarya', 'MUTMAINNAH, S.Pd., M.Biomed.', '2026-03-21 21:14:16'),
(1494, 'JUMAT', '4', '09:30 - 10:15', 'XI 11', 'Bahasa Inggris', 'RISTA JUNIANTI SAHAR, SS.', '2026-03-21 21:14:16'),
(1495, 'JUMAT', '4', '09:30 - 10:15', 'XI 12', 'Sosiologi (Pilihan)', 'SRI UTAMI WULANSARI, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(1496, 'JUMAT', '4', '09:30 - 10:15', 'XII 1', 'Bahasa Inggris (Pilihan)', 'AFIAH SAKIAH, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(1497, 'JUMAT', '4', '09:30 - 10:15', 'XII 2', 'Geografi (Pilihan)', 'SANTI KUSUMA DEWI, S.Pd.', '2026-03-21 21:14:16'),
(1498, 'JUMAT', '4', '09:30 - 10:15', 'XII 3', 'Matematika (Pilihan)', 'NUR QALBI RUSDIN, S.Pd.,M.Pd.', '2026-03-21 21:14:16'),
(1499, 'JUMAT', '4', '09:30 - 10:15', 'XII 4', 'Ekonomi (Pilihan)', 'MUHAMMAD YUSUF, S.Pd.', '2026-03-21 21:14:16'),
(1500, 'JUMAT', '4', '09:30 - 10:15', 'XII 5', 'Geografi (Pilihan)', 'Drs. BAHARUDDIN', '2026-03-21 21:14:16'),
(1501, 'JUMAT', '4', '09:30 - 10:15', 'XII 6', 'Geografi (Pilihan)', 'HALIFUDDIN, S.Pd.', '2026-03-21 21:14:16'),
(1502, 'JUMAT', '4', '09:30 - 10:15', 'XII 7', 'Bahasa Indonesia (Pilihan)', 'HASNA BAKRI, S.Pd.', '2026-03-21 21:14:16'),
(1503, 'JUMAT', '4', '09:30 - 10:15', 'XII 8', 'Biologi (Pilihan)', 'Drs. MUHAMMAD HAMZAH, MM.', '2026-03-21 21:14:16'),
(1504, 'JUMAT', '4', '09:30 - 10:15', 'XII 9', 'Ekonomi (Pilihan)', 'Dr. Hj. ROSMAWATI, SE.,MA.', '2026-03-21 21:14:16'),
(1505, 'JUMAT', '4', '09:30 - 10:15', 'XII 10', 'Matematika (Pilihan)', 'HAMKA, S.Si., S.Pd., Gr.', '2026-03-21 21:14:16'),
(1506, 'JUMAT', '4', '09:30 - 10:15', 'XII 11', 'Biologi (Pilihan)', 'HAMIDATUSSIFAH, S.Si.', '2026-03-21 21:14:16'),
(1507, 'JUMAT', '4', '09:30 - 10:15', 'XII 12', 'Kimia (Pilihan)', 'SAPRUDDIN, S.Pd.', '2026-03-21 21:14:16'),
(1508, 'JUMAT', '5', '10:25 - 11:10', 'X 1', 'Bahasa Inggris', 'ARIFIN, S.Pd.', '2026-03-21 21:14:16'),
(1509, 'JUMAT', '5', '10:25 - 11:10', 'X 2', 'Matematika', 'SYAMSIAR, S.Pd.', '2026-03-21 21:14:16'),
(1510, 'JUMAT', '5', '10:25 - 11:10', 'X 3', 'Seni Budaya', 'ASWAN ANWAR, S.Pd.', '2026-03-21 21:14:16'),
(1511, 'JUMAT', '5', '10:25 - 11:10', 'X 4', 'Informatika', 'MUH. TRI PRASETIA NUA, S.Pd., Gr.', '2026-03-21 21:14:16'),
(1512, 'JUMAT', '5', '10:25 - 11:10', 'X 5', 'Matematika', 'HARTIWI PRADIKTA, S.Pd., Gr.', '2026-03-21 21:14:16'),
(1513, 'JUMAT', '5', '10:25 - 11:10', 'X 6', 'Pendidikan Pancasila', 'YEKTIE NUR PRAYOGA LM, S.Pd., Gr.', '2026-03-21 21:14:16'),
(1514, 'JUMAT', '5', '10:25 - 11:10', 'X 7', 'Bahasa Indonesia', 'NURFATWA, S.Pd.', '2026-03-21 21:14:16'),
(1515, 'JUMAT', '5', '10:25 - 11:10', 'X 8', 'Informatika', 'RIZA RISKY YULIANTI, S.Pd., Gr.', '2026-03-21 21:14:16'),
(1516, 'JUMAT', '5', '10:25 - 11:10', 'X 9', 'Bahasa Indonesia', 'A. FERAWATI SUT, S.Pd.', '2026-03-21 21:14:16'),
(1517, 'JUMAT', '5', '10:25 - 11:10', 'X 10', 'Sejarah', 'ERNIATI, S.Pd.', '2026-03-21 21:14:16'),
(1518, 'JUMAT', '5', '10:25 - 11:10', 'X 11', 'Seni Budaya', 'ANUGRAYANTI, S.Pd.', '2026-03-21 21:14:16'),
(1519, 'JUMAT', '5', '10:25 - 11:10', 'XI 1', 'Pendidikan Agama', 'YANTI NOVIANTI NAWAWI, S.Pd.I.', '2026-03-21 21:14:16'),
(1520, 'JUMAT', '5', '10:25 - 11:10', 'XI 2', 'Bahasa Inggris', 'RISTA JUNIANTI SAHAR, SS.', '2026-03-21 21:14:16'),
(1521, 'JUMAT', '5', '10:25 - 11:10', 'XI 3', 'Matematika', 'NURAFNI PEBRIANTY, S.Pd., Gr.', '2026-03-21 21:14:16'),
(1522, 'JUMAT', '5', '10:25 - 11:10', 'XI 4', 'Seni Budaya', 'ST. NADIA KHAERATY, S.Pd.', '2026-03-21 21:14:16'),
(1523, 'JUMAT', '5', '10:25 - 11:10', 'XI 5', 'Prakarya', 'SARWANA, S.Pd.', '2026-03-21 21:14:16'),
(1524, 'JUMAT', '5', '10:25 - 11:10', 'XI 6', 'Pendidikan Agama', 'SURYADI SYARIF, S.Pd.', '2026-03-21 21:14:16'),
(1525, 'JUMAT', '5', '10:25 - 11:10', 'XI 7', 'Sejarah', 'ALI TAHIR, S.Pd.', '2026-03-21 21:14:16'),
(1526, 'JUMAT', '5', '10:25 - 11:10', 'XI 8', 'Pendidikan Pancasila', 'EKA RUSNAENI, S.Pd., Gr.', '2026-03-21 21:14:16'),
(1527, 'JUMAT', '5', '10:25 - 11:10', 'XI 9', 'Geografi (Pilihan)', 'HALIFUDDIN, S.Pd.', '2026-03-21 21:14:16'),
(1528, 'JUMAT', '5', '10:25 - 11:10', 'XI 10', 'Prakarya', 'MUTMAINNAH, S.Pd., M.Biomed.', '2026-03-21 21:14:16'),
(1529, 'JUMAT', '5', '10:25 - 11:10', 'XI 11', 'Sosiologi (Pilihan)', 'SRI UTAMI WULANSARI, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(1530, 'JUMAT', '5', '10:25 - 11:10', 'XI 12', 'Bahasa Indonesia', 'FITRIANI RAHMAT, S.Pd.', '2026-03-21 21:14:16'),
(1531, 'JUMAT', '5', '10:25 - 11:10', 'XII 1', 'Bahasa Indonesia (Pilihan)', 'NUR AKBAR, S.Pd.', '2026-03-21 21:14:16'),
(1532, 'JUMAT', '5', '10:25 - 11:10', 'XII 2', 'Ekonomi (Pilihan)', 'Dr. Hj. ROSMAWATI, SE.,MA.', '2026-03-21 21:14:16'),
(1533, 'JUMAT', '5', '10:25 - 11:10', 'XII 3', 'Kimia (Pilihan)', 'ISMAIL LA HASSE, S.Si., S.Pd.', '2026-03-21 21:14:16'),
(1534, 'JUMAT', '5', '10:25 - 11:10', 'XII 4', 'Kimia (Pilihan)', 'SAPRUDDIN, S.Pd.', '2026-03-21 21:14:16'),
(1535, 'JUMAT', '5', '10:25 - 11:10', 'XII 5', 'Bahasa Indonesia (Pilihan)', 'HASNA BAKRI, S.Pd.', '2026-03-21 21:14:16'),
(1536, 'JUMAT', '5', '10:25 - 11:10', 'XII 6', 'Biologi (Pilihan)', 'HAMIDATUSSIFAH, S.Si.', '2026-03-21 21:14:16'),
(1537, 'JUMAT', '5', '10:25 - 11:10', 'XII 7', 'Informatika (Pilihan)', 'MUHAMMAD IDRIS RIFAI SARRO, S.Si.', '2026-03-21 21:14:16'),
(1538, 'JUMAT', '5', '10:25 - 11:10', 'XII 8', 'Kimia (Pilihan)', 'MARTHINA SATTU ARI, M.Pd.', '2026-03-21 21:14:16'),
(1539, 'JUMAT', '5', '10:25 - 11:10', 'XII 9', 'Matematika (Pilihan)', 'NUR QALBI RUSDIN, S.Pd.,M.Pd.', '2026-03-21 21:14:16'),
(1540, 'JUMAT', '5', '10:25 - 11:10', 'XII 10', 'Biologi (Pilihan)', 'MINDJAH WATY M., S.Si.', '2026-03-21 21:14:16'),
(1541, 'JUMAT', '5', '10:25 - 11:10', 'XII 11', 'Matematika (Pilihan)', 'HAMKA, S.Si., S.Pd., Gr.', '2026-03-21 21:14:16'),
(1542, 'JUMAT', '5', '10:25 - 11:10', 'XII 12', 'Geografi (Pilihan)', 'Drs. BAHARUDDIN', '2026-03-21 21:14:16'),
(1543, 'JUMAT', '6', '11:10 - 11:55', 'X 1', 'Bahasa Inggris', 'ARIFIN, S.Pd.', '2026-03-21 21:14:16'),
(1544, 'JUMAT', '6', '11:10 - 11:55', 'X 2', 'Matematika', 'SYAMSIAR, S.Pd.', '2026-03-21 21:14:16'),
(1545, 'JUMAT', '6', '11:10 - 11:55', 'X 3', 'Ekonomi', 'A. VEBHY INDAH BELLIANTHARI, S.Pd.', '2026-03-21 21:14:16'),
(1546, 'JUMAT', '6', '11:10 - 11:55', 'X 4', 'Informatika', 'MUH. TRI PRASETIA NUA, S.Pd., Gr.', '2026-03-21 21:14:16'),
(1547, 'JUMAT', '6', '11:10 - 11:55', 'X 5', 'Matematika', 'HARTIWI PRADIKTA, S.Pd., Gr.', '2026-03-21 21:14:16'),
(1548, 'JUMAT', '6', '11:10 - 11:55', 'X 6', 'Geografi', 'SANTI KUSUMA DEWI, S.Pd.', '2026-03-21 21:14:16'),
(1549, 'JUMAT', '6', '11:10 - 11:55', 'X 7', 'Bahasa Indonesia', 'NURFATWA, S.Pd.', '2026-03-21 21:14:16'),
(1550, 'JUMAT', '6', '11:10 - 11:55', 'X 8', 'Informatika', 'RIZA RISKY YULIANTI, S.Pd., Gr.', '2026-03-21 21:14:16'),
(1551, 'JUMAT', '6', '11:10 - 11:55', 'X 9', 'Bahasa Indonesia', 'A. FERAWATI SUT, S.Pd.', '2026-03-21 21:14:16'),
(1552, 'JUMAT', '6', '11:10 - 11:55', 'X 10', 'Sejarah', 'ERNIATI, S.Pd.', '2026-03-21 21:14:16'),
(1553, 'JUMAT', '6', '11:10 - 11:55', 'X 11', 'Seni Budaya', 'ANUGRAYANTI, S.Pd.', '2026-03-21 21:14:16'),
(1554, 'JUMAT', '6', '11:10 - 11:55', 'XI 1', 'Pendidikan Agama', 'YANTI NOVIANTI NAWAWI, S.Pd.I.', '2026-03-21 21:14:16'),
(1555, 'JUMAT', '6', '11:10 - 11:55', 'XI 2', 'Bahasa Inggris', 'RISTA JUNIANTI SAHAR, SS.', '2026-03-21 21:14:16'),
(1556, 'JUMAT', '6', '11:10 - 11:55', 'XI 3', 'Pendidikan Pancasila', 'EKA RUSNAENI, S.Pd., Gr.', '2026-03-21 21:14:16'),
(1557, 'JUMAT', '6', '11:10 - 11:55', 'XI 4', 'Prakarya', 'MUTMAINNAH, S.Pd., M.Biomed.', '2026-03-21 21:14:16'),
(1558, 'JUMAT', '6', '11:10 - 11:55', 'XI 5', 'Seni Budaya', 'ST. NADIA KHAERATY, S.Pd.', '2026-03-21 21:14:16'),
(1559, 'JUMAT', '6', '11:10 - 11:55', 'XI 6', 'Pendidikan Agama', 'SURYADI SYARIF, S.Pd.', '2026-03-21 21:14:16'),
(1560, 'JUMAT', '6', '11:10 - 11:55', 'XI 7', 'Prakarya', 'SARWANA, S.Pd.', '2026-03-21 21:14:16'),
(1561, 'JUMAT', '6', '11:10 - 11:55', 'XI 8', 'Bahasa Inggris (Pilihan)', 'RANDIKA DHANI SYAPUTRA, S.Pd.', '2026-03-21 21:14:16'),
(1562, 'JUMAT', '6', '11:10 - 11:55', 'XI 9', 'Geografi (Pilihan)', 'HALIFUDDIN, S.Pd.', '2026-03-21 21:14:16'),
(1563, 'JUMAT', '6', '11:10 - 11:55', 'XI 10', 'Sejarah', 'ALI TAHIR, S.Pd.', '2026-03-21 21:14:16'),
(1564, 'JUMAT', '6', '11:10 - 11:55', 'XI 11', 'Sosiologi (Pilihan)', 'SRI UTAMI WULANSARI, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(1565, 'JUMAT', '6', '11:10 - 11:55', 'XI 12', 'Bahasa Indonesia', 'FITRIANI RAHMAT, S.Pd.', '2026-03-21 21:14:16'),
(1566, 'JUMAT', '6', '11:10 - 11:55', 'XII 1', 'Bahasa Indonesia (Pilihan)', 'NUR AKBAR, S.Pd.', '2026-03-21 21:14:16'),
(1567, 'JUMAT', '6', '11:10 - 11:55', 'XII 2', 'Ekonomi (Pilihan)', 'Dr. Hj. ROSMAWATI, SE.,MA.', '2026-03-21 21:14:16'),
(1568, 'JUMAT', '6', '11:10 - 11:55', 'XII 3', 'Kimia (Pilihan)', 'ISMAIL LA HASSE, S.Si., S.Pd.', '2026-03-21 21:14:16'),
(1569, 'JUMAT', '6', '11:10 - 11:55', 'XII 4', 'Kimia (Pilihan)', 'SAPRUDDIN, S.Pd.', '2026-03-21 21:14:16'),
(1570, 'JUMAT', '6', '11:10 - 11:55', 'XII 5', 'Bahasa Indonesia (Pilihan)', 'HASNA BAKRI, S.Pd.', '2026-03-21 21:14:16'),
(1571, 'JUMAT', '6', '11:10 - 11:55', 'XII 6', 'Biologi (Pilihan)', 'HAMIDATUSSIFAH, S.Si.', '2026-03-21 21:14:16'),
(1572, 'JUMAT', '6', '11:10 - 11:55', 'XII 7', 'Informatika (Pilihan)', 'MUHAMMAD IDRIS RIFAI SARRO, S.Si.', '2026-03-21 21:14:16'),
(1573, 'JUMAT', '6', '11:10 - 11:55', 'XII 8', 'Kimia (Pilihan)', 'MARTHINA SATTU ARI, M.Pd.', '2026-03-21 21:14:16'),
(1574, 'JUMAT', '6', '11:10 - 11:55', 'XII 9', 'Matematika (Pilihan)', 'NUR QALBI RUSDIN, S.Pd.,M.Pd.', '2026-03-21 21:14:16'),
(1575, 'JUMAT', '6', '11:10 - 11:55', 'XII 10', 'Biologi (Pilihan)', 'MINDJAH WATY M., S.Si.', '2026-03-21 21:14:16'),
(1576, 'JUMAT', '6', '11:10 - 11:55', 'XII 11', 'Matematika (Pilihan)', 'HAMKA, S.Si., S.Pd., Gr.', '2026-03-21 21:14:16'),
(1577, 'JUMAT', '6', '11:10 - 11:55', 'XII 12', 'Geografi (Pilihan)', 'Drs. BAHARUDDIN', '2026-03-21 21:14:16'),
(1578, 'JUMAT', '7', '13:15 - 14:00', 'X 1', 'Bahasa Indonesia', 'NURFATWA, S.Pd.', '2026-03-21 21:14:16'),
(1579, 'JUMAT', '7', '13:15 - 14:00', 'X 2', 'Informatika', 'MUH. TRI PRASETIA NUA, S.Pd., Gr.', '2026-03-21 21:14:16'),
(1580, 'JUMAT', '7', '13:15 - 14:00', 'X 3', 'Ekonomi', 'A. VEBHY INDAH BELLIANTHARI, S.Pd.', '2026-03-21 21:14:16'),
(1581, 'JUMAT', '7', '13:15 - 14:00', 'X 4', 'Matematika', 'SYAMSIAR, S.Pd.', '2026-03-21 21:14:16'),
(1582, 'JUMAT', '7', '13:15 - 14:00', 'X 5', 'Informatika', 'RIZA RISKY YULIANTI, S.Pd., Gr.', '2026-03-21 21:14:16'),
(1583, 'JUMAT', '7', '13:15 - 14:00', 'X 6', 'Geografi', 'SANTI KUSUMA DEWI, S.Pd.', '2026-03-21 21:14:16'),
(1584, 'JUMAT', '7', '13:15 - 14:00', 'X 7', 'Seni Budaya', 'ASWAN ANWAR, S.Pd.', '2026-03-21 21:14:16'),
(1585, 'JUMAT', '7', '13:15 - 14:00', 'X 8', 'Pendidikan Pancasila', 'YEKTIE NUR PRAYOGA LM, S.Pd., Gr.', '2026-03-21 21:14:16'),
(1586, 'JUMAT', '7', '13:15 - 14:00', 'X 9', 'Matematika', 'NURAFNI PEBRIANTY, S.Pd., Gr.', '2026-03-21 21:14:16'),
(1587, 'JUMAT', '7', '13:15 - 14:00', 'X 10', 'Bahasa Indonesia', 'A. FERAWATI SUT, S.Pd.', '2026-03-21 21:14:16'),
(1588, 'JUMAT', '7', '13:15 - 14:00', 'X 11', 'Bahasa Inggris', 'ARIFIN, S.Pd.', '2026-03-21 21:14:16'),
(1589, 'JUMAT', '7', '13:15 - 14:00', 'XI 1', 'Pendidikan Agama', 'YANTI NOVIANTI NAWAWI, S.Pd.I.', '2026-03-21 21:14:16'),
(1590, 'JUMAT', '7', '13:15 - 14:00', 'XI 2', 'Bahasa Inggris', 'RISTA JUNIANTI SAHAR, SS.', '2026-03-21 21:14:16'),
(1591, 'JUMAT', '7', '13:15 - 14:00', 'XI 3', 'Pendidikan Pancasila', 'EKA RUSNAENI, S.Pd., Gr.', '2026-03-21 21:14:16'),
(1592, 'JUMAT', '7', '13:15 - 14:00', 'XI 4', 'Prakarya', 'MUTMAINNAH, S.Pd., M.Biomed.', '2026-03-21 21:14:16'),
(1593, 'JUMAT', '7', '13:15 - 14:00', 'XI 5', 'Seni Budaya', 'ST. NADIA KHAERATY, S.Pd.', '2026-03-21 21:14:16'),
(1594, 'JUMAT', '7', '13:15 - 14:00', 'XI 6', 'Pendidikan Agama', 'SURYADI SYARIF, S.Pd.', '2026-03-21 21:14:16'),
(1595, 'JUMAT', '7', '13:15 - 14:00', 'XI 7', 'Prakarya', 'SARWANA, S.Pd.', '2026-03-21 21:14:16'),
(1596, 'JUMAT', '7', '13:15 - 14:00', 'XI 8', 'Bahasa Inggris (Pilihan)', 'RANDIKA DHANI SYAPUTRA, S.Pd.', '2026-03-21 21:14:16'),
(1597, 'JUMAT', '7', '13:15 - 14:00', 'XI 9', 'Geografi (Pilihan)', 'HALIFUDDIN, S.Pd.', '2026-03-21 21:14:16'),
(1598, 'JUMAT', '7', '13:15 - 14:00', 'XI 10', 'Sejarah', 'ALI TAHIR, S.Pd.', '2026-03-21 21:14:16'),
(1599, 'JUMAT', '7', '13:15 - 14:00', 'XI 11', 'Sosiologi (Pilihan)', 'SRI UTAMI WULANSARI, S.Pd., M.Pd.', '2026-03-21 21:14:16'),
(1600, 'JUMAT', '7', '13:15 - 14:00', 'XI 12', 'Bahasa Indonesia', 'FITRIANI RAHMAT, S.Pd.', '2026-03-21 21:14:16'),
(1601, 'JUMAT', '7', '13:15 - 14:00', 'XII 1', 'Bahasa Indonesia (Pilihan)', 'NUR AKBAR, S.Pd.', '2026-03-21 21:14:16'),
(1602, 'JUMAT', '7', '13:15 - 14:00', 'XII 2', 'Ekonomi (Pilihan)', 'Dr. Hj. ROSMAWATI, SE.,MA.', '2026-03-21 21:14:16'),
(1603, 'JUMAT', '7', '13:15 - 14:00', 'XII 3', 'Kimia (Pilihan)', 'ISMAIL LA HASSE, S.Si., S.Pd.', '2026-03-21 21:14:16'),
(1604, 'JUMAT', '7', '13:15 - 14:00', 'XII 4', 'Kimia (Pilihan)', 'SAPRUDDIN, S.Pd.', '2026-03-21 21:14:16'),
(1605, 'JUMAT', '7', '13:15 - 14:00', 'XII 5', 'Bahasa Indonesia (Pilihan)', 'HASNA BAKRI, S.Pd.', '2026-03-21 21:14:16'),
(1606, 'JUMAT', '7', '13:15 - 14:00', 'XII 6', 'Biologi (Pilihan)', 'HAMIDATUSSIFAH, S.Si.', '2026-03-21 21:14:16'),
(1607, 'JUMAT', '7', '13:15 - 14:00', 'XII 7', 'Informatika (Pilihan)', 'MUHAMMAD IDRIS RIFAI SARRO, S.Si.', '2026-03-21 21:14:16'),
(1608, 'JUMAT', '7', '13:15 - 14:00', 'XII 8', 'Kimia (Pilihan)', 'MARTHINA SATTU ARI, M.Pd.', '2026-03-21 21:14:16'),
(1609, 'JUMAT', '7', '13:15 - 14:00', 'XII 9', 'Matematika (Pilihan)', 'NUR QALBI RUSDIN, S.Pd.,M.Pd.', '2026-03-21 21:14:16'),
(1610, 'JUMAT', '7', '13:15 - 14:00', 'XII 10', 'Biologi (Pilihan)', 'MINDJAH WATY M., S.Si.', '2026-03-21 21:14:16'),
(1611, 'JUMAT', '7', '13:15 - 14:00', 'XII 11', 'Matematika (Pilihan)', 'HAMKA, S.Si., S.Pd., Gr.', '2026-03-21 21:14:16'),
(1612, 'JUMAT', '7', '13:15 - 14:00', 'XII 12', 'Geografi (Pilihan)', 'Drs. BAHARUDDIN', '2026-03-21 21:14:16'),
(1613, 'JUMAT', '8', '14:00 - 14:45', 'X 1', 'Bahasa Indonesia', 'NURFATWA, S.Pd.', '2026-03-21 21:14:16'),
(1614, 'JUMAT', '8', '14:00 - 14:45', 'X 2', 'Informatika', 'MUH. TRI PRASETIA NUA, S.Pd., Gr.', '2026-03-21 21:14:16'),
(1615, 'JUMAT', '8', '14:00 - 14:45', 'X 3', 'Ekonomi', 'A. VEBHY INDAH BELLIANTHARI, S.Pd.', '2026-03-21 21:14:16'),
(1616, 'JUMAT', '8', '14:00 - 14:45', 'X 4', 'Matematika', 'SYAMSIAR, S.Pd.', '2026-03-21 21:14:16'),
(1617, 'JUMAT', '8', '14:00 - 14:45', 'X 5', 'Informatika', 'RIZA RISKY YULIANTI, S.Pd., Gr.', '2026-03-21 21:14:16'),
(1618, 'JUMAT', '8', '14:00 - 14:45', 'X 6', 'Geografi', 'SANTI KUSUMA DEWI, S.Pd.', '2026-03-21 21:14:16'),
(1619, 'JUMAT', '8', '14:00 - 14:45', 'X 7', 'Seni Budaya', 'ASWAN ANWAR, S.Pd.', '2026-03-21 21:14:16'),
(1620, 'JUMAT', '8', '14:00 - 14:45', 'X 8', 'Pendidikan Pancasila', 'YEKTIE NUR PRAYOGA LM, S.Pd., Gr.', '2026-03-21 21:14:16'),
(1621, 'JUMAT', '8', '14:00 - 14:45', 'X 9', 'Matematika', 'NURAFNI PEBRIANTY, S.Pd., Gr.', '2026-03-21 21:14:16'),
(1622, 'JUMAT', '8', '14:00 - 14:45', 'X 10', 'Bahasa Indonesia', 'A. FERAWATI SUT, S.Pd.', '2026-03-21 21:14:16'),
(1623, 'JUMAT', '8', '14:00 - 14:45', 'X 11', 'Bahasa Inggris', 'ARIFIN, S.Pd.', '2026-03-21 21:14:16'),
(1624, 'SENIN', '1', '07:30 - 08:15', 'XI 12', 'UPACARA BENDERA', '', '2026-03-21 21:14:16'),
(1625, 'SENIN', '2', '08:15 - 09:00', 'XI 12', 'XI 12', 'XI 12', '2026-03-21 21:14:16'),
(1626, 'SENIN', '3', '09:00 - 09:45', 'XI 12', '', 'XI 12', '2026-03-21 21:14:16'),
(1627, 'SENIN', '4', '09:45 - 10:30', 'XI 12', 'I S T I R A H A T', '', '2026-03-21 21:14:16'),
(1628, 'SENIN', '6', '11:30 - 12:15', 'XI 12', 'I S T I R A H A T', '', '2026-03-21 21:14:16'),
(1629, 'SELASA', '1', '07:30 - 08:15', 'XI 12', 'XI 1', 'XI 1', '2026-03-21 21:14:16'),
(1630, 'SELASA', '2', '08:15 - 09:00', 'XI 12', '', 'XI 1', '2026-03-21 21:14:16'),
(1631, 'SELASA', '3', '09:00 - 09:45', 'XI 12', 'X 3', '', '2026-03-21 21:14:16'),
(1632, 'SELASA', '4', '09:45 - 10:30', 'XI 12', 'I S T I R A H A T', 'X 3', '2026-03-21 21:14:16'),
(1633, 'SELASA', '5', '10:45 - 11:30', 'XI 12', '', 'X 3', '2026-03-21 21:14:16'),
(1634, 'SELASA', '6', '11:30 - 12:15', 'XI 12', 'I S T I R A H A T', '', '2026-03-21 21:14:16'),
(1635, 'SELASA', '8', '13:30 - 14:10', 'XI 12', 'X 8', '', '2026-03-21 21:14:16'),
(1636, 'SELASA', '9', '14:10 - 14:50', 'XI 12', 'X 8', 'X 8', '2026-03-21 21:14:16'),
(1637, 'SELASA', '10', '14:50 - 15:30', 'XI 12', '', 'X 8', '2026-03-21 21:14:16'),
(1638, 'RABU', '1', '07:30 - 08:15', 'XI 12', 'X 3', 'X 3', '2026-03-21 21:14:16'),
(1639, 'RABU', '2', '08:15 - 09:00', 'XI 12', '', 'X 3', '2026-03-21 21:14:16'),
(1640, 'RABU', '4', '09:45 - 10:30', 'XI 12', 'I S T I R A H A T', '', '2026-03-21 21:14:16'),
(1641, 'RABU', '6', '11:30 - 12:15', 'XI 12', 'I S T I R A H A T', '', '2026-03-21 21:14:16'),
(1642, 'RABU', '7', '12:50 - 13:30', 'XI 12', 'XI 3', 'XI 3', '2026-03-21 21:14:16'),
(1643, 'RABU', '8', '13:30 - 14:10', 'XI 12', 'X 9', 'XI 3', '2026-03-21 21:14:16'),
(1644, 'RABU', '9', '14:10 - 14:50', 'XI 12', 'X 9', 'X 9', '2026-03-21 21:14:16'),
(1645, 'RABU', '10', '14:50 - 15:30', 'XI 12', '', 'X 9', '2026-03-21 21:14:16'),
(1646, 'SENIN', '1', '07:30 - 08:15', 'XII 12', 'UPACARA BENDERA', '', '2026-03-21 21:14:16'),
(1647, 'SENIN', '2', '08:15 - 09:00', 'XII 12', '09:00 - 09:45', '08:15 - 09:00', '2026-03-21 21:14:16'),
(1648, 'SENIN', '3', '09:00 - 09:45', 'XII 12', '09:45 - 10:30', '09:00 - 09:45', '2026-03-21 21:14:16'),
(1649, 'SENIN', '4', '09:45 - 10:30', 'XII 12', '10:30 - 10:45', '09:45 - 10:30', '2026-03-21 21:14:16'),
(1650, 'SENIN', '5', '10:45 - 11:30', 'XII 12', '11:30 - 12:15', '10:45 - 11:30', '2026-03-21 21:14:16'),
(1651, 'SENIN', '6', '11:30 - 12:15', 'XII 12', '12:15 - 12:50', '11:30 - 12:15', '2026-03-21 21:14:16'),
(1652, 'SENIN', '7', '12:50 - 13:30', 'XII 12', '13:30 - 14:10', '12:50 - 13:30', '2026-03-21 21:14:16'),
(1653, 'SENIN', '8', '13:30 - 14:10', 'XII 12', '14:10 - 14:50', '13:30 - 14:10', '2026-03-21 21:14:16'),
(1654, 'SENIN', '9', '14:10 - 14:50', 'XII 12', '14:50 - 15:30', '14:10 - 14:50', '2026-03-21 21:14:16'),
(1655, 'SENIN', '10', '14:50 - 15:30', 'XII 12', '', '14:50 - 15:30', '2026-03-21 21:14:16'),
(1656, 'SELASA', '1', '07:30 - 08:15', 'XII 12', '08:15 - 09:00', '07:30 - 08:15', '2026-03-21 21:14:16'),
(1657, 'SELASA', '2', '08:15 - 09:00', 'XII 12', '09:00 - 09:45', '08:15 - 09:00', '2026-03-21 21:14:16'),
(1658, 'SELASA', '3', '09:00 - 09:45', 'XII 12', '09:45 - 10:30', '09:00 - 09:45', '2026-03-21 21:14:16'),
(1659, 'SELASA', '4', '09:45 - 10:30', 'XII 12', '10:30 - 10:45', '09:45 - 10:30', '2026-03-21 21:14:16'),
(1660, 'SELASA', '5', '10:45 - 11:30', 'XII 12', '11:30 - 12:15', '10:45 - 11:30', '2026-03-21 21:14:16'),
(1661, 'SELASA', '6', '11:30 - 12:15', 'XII 12', '12:15 - 12:50', '11:30 - 12:15', '2026-03-21 21:14:16'),
(1662, 'SELASA', '7', '12:50 - 13:30', 'XII 12', '13:30 - 14:10', '12:50 - 13:30', '2026-03-21 21:14:16'),
(1663, 'SELASA', '8', '13:30 - 14:10', 'XII 12', '14:10 - 14:50', '13:30 - 14:10', '2026-03-21 21:14:16'),
(1664, 'SELASA', '9', '14:10 - 14:50', 'XII 12', '14:50 - 15:30', '14:10 - 14:50', '2026-03-21 21:14:16'),
(1665, 'SELASA', '10', '14:50 - 15:30', 'XII 12', '', '14:50 - 15:30', '2026-03-21 21:14:16'),
(1666, 'RABU', '1', '07:30 - 08:15', 'XII 12', '08:15 - 09:00', '07:30 - 08:15', '2026-03-21 21:14:16'),
(1667, 'RABU', '2', '08:15 - 09:00', 'XII 12', '09:00 - 09:45', '08:15 - 09:00', '2026-03-21 21:14:16'),
(1668, 'RABU', '3', '09:00 - 09:45', 'XII 12', '09:45 - 10:30', '09:00 - 09:45', '2026-03-21 21:14:16'),
(1669, 'RABU', '4', '09:45 - 10:30', 'XII 12', '10:30 - 10:45', '09:45 - 10:30', '2026-03-21 21:14:16'),
(1670, 'RABU', '5', '10:45 - 11:30', 'XII 12', '11:30 - 12:15', '10:45 - 11:30', '2026-03-21 21:14:16'),
(1671, 'RABU', '6', '11:30 - 12:15', 'XII 12', '12:15 - 12:50', '11:30 - 12:15', '2026-03-21 21:14:16'),
(1672, 'RABU', '7', '12:50 - 13:30', 'XII 12', '13:30 - 14:10', '12:50 - 13:30', '2026-03-21 21:14:16'),
(1673, 'RABU', '8', '13:30 - 14:10', 'XII 12', '14:10 - 14:50', '13:30 - 14:10', '2026-03-21 21:14:16'),
(1674, 'RABU', '9', '14:10 - 14:50', 'XII 12', '14:50 - 15:30', '14:10 - 14:50', '2026-03-21 21:14:16'),
(1675, 'RABU', '10', '14:50 - 15:30', 'XII 12', '', '14:50 - 15:30', '2026-03-21 21:14:16');

-- --------------------------------------------------------

--
-- Table structure for table `student_grades`
--

CREATE TABLE `student_grades` (
  `id` int NOT NULL,
  `student_id` int NOT NULL,
  `teacher_id` int NOT NULL,
  `subject_id` int NOT NULL,
  `class_id` int NOT NULL,
  `academic_year` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `semester` enum('Ganjil','Genap') COLLATE utf8mb4_unicode_ci NOT NULL,
  `grade` decimal(5,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `student_grades`
--

INSERT INTO `student_grades` (`id`, `student_id`, `teacher_id`, `subject_id`, `class_id`, `academic_year`, `semester`, `grade`, `created_at`, `updated_at`) VALUES
(1, 5804, 5803, 4, 1, '2025-2026', 'Ganjil', '19.00', '2026-03-22 04:47:24', '2026-03-22 04:47:24'),
(2, 5804, 5803, 13, 1, '2025-2026', 'Ganjil', '60.00', '2026-03-22 04:47:31', '2026-03-22 04:47:31'),
(3, 6723, 6722, 3, 5, '2025-2026', 'Ganjil', '100.00', '2026-03-22 21:32:17', '2026-03-22 21:32:17');

-- --------------------------------------------------------

--
-- Table structure for table `subjects`
--

CREATE TABLE `subjects` (
  `id` int NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `subjects`
--

INSERT INTO `subjects` (`id`, `name`) VALUES
(1, 'Matematika Wajib'),
(2, 'Matematika Peminatan'),
(3, 'Bahasa Indonesia'),
(4, 'Bahasa Inggris'),
(5, 'Fisika'),
(6, 'Kimia'),
(7, 'Biologi'),
(8, 'Ekonomi'),
(9, 'Sosiologi'),
(10, 'Geografi'),
(11, 'Sejarah'),
(12, 'PKN'),
(13, 'Pendidikan Agama'),
(14, 'Matematika');

-- --------------------------------------------------------

--
-- Table structure for table `submissions`
--

CREATE TABLE `submissions` (
  `id` int NOT NULL,
  `assignment_id` int NOT NULL,
  `student_id` int NOT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `submitted_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `grade` int DEFAULT NULL,
  `feedback` text,
  `status` enum('hadir','sakit','izin','alpha','terlambat','menunggu_nilai','dinilai') DEFAULT 'menunggu_nilai',
  `is_archived` tinyint(1) DEFAULT '0'
) ;

--
-- Dumping data for table `submissions`
--

INSERT INTO `submissions` (`id`, `assignment_id`, `student_id`, `file_path`, `submitted_at`, `grade`, `feedback`, `status`, `is_archived`) VALUES
(160, 69, 7146, NULL, '2026-03-24 15:23:25', 80, '', 'terlambat', 0);

-- --------------------------------------------------------

--
-- Table structure for table `submission_attachments`
--

CREATE TABLE `submission_attachments` (
  `id` int NOT NULL,
  `submission_id` int NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `submission_attachments`
--

INSERT INTO `submission_attachments` (`id`, `submission_id`, `file_path`, `created_at`) VALUES
(12, 160, 'public/uploads/submissions/1774365805_7146_KHS - siswa.pdf', '2026-03-24 15:23:25');

-- --------------------------------------------------------

--
-- Table structure for table `teacher_classes`
--

CREATE TABLE `teacher_classes` (
  `id` int NOT NULL,
  `teacher_id` int NOT NULL,
  `class_id` int DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `subject` varchar(100) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `subject_id` int DEFAULT NULL,
  `folder_name` varchar(255) DEFAULT NULL,
  `is_special_class` tinyint(1) DEFAULT '0',
  `special_grade_level` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `teacher_classes`
--

INSERT INTO `teacher_classes` (`id`, `teacher_id`, `class_id`, `name`, `subject`, `created_at`, `subject_id`, `folder_name`, `is_special_class`, `special_grade_level`) VALUES
(45, 6722, 1, 'Bahasa Indonesia - X-1', 'Bahasa Indonesia', '2026-03-24 14:31:41', 3, '2026-03-24 Bahasa_Indonesia_-_X-1', 0, NULL),
(46, 6722, 1, 'Bahasa Inggris - X-1', 'Bahasa Inggris', '2026-03-24 15:30:27', 4, '2026-03-24 Bahasa_Inggris_-_X-1', 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tracer_study`
--

CREATE TABLE `tracer_study` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `kegiatan` enum('Kuliah','Kerja','Wirausaha','Belum/Tidak Bekerja') COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama_instansi` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `jurusan_posisi` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tahun_lulus` year NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `foto` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `role` enum('admin','guru','siswa','osis','kepsek','wakasek','bk') NOT NULL DEFAULT 'siswa',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `class_id` int DEFAULT NULL,
  `subject_id` int DEFAULT NULL,
  `photo_path` varchar(255) DEFAULT NULL,
  `gender` enum('L','P') DEFAULT NULL,
  `address` text,
  `status` enum('active','graduated','suspended') DEFAULT 'active',
  `nip` varchar(20) DEFAULT NULL,
  `nis` varchar(20) DEFAULT NULL,
  `password_changed` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `full_name`, `role`, `created_at`, `class_id`, `subject_id`, `photo_path`, `gender`, `address`, `status`, `nip`, `nis`, `password_changed`) VALUES
(1, 'admin', 'admin123', 'Administrator', 'admin', '2026-02-05 00:21:34', NULL, NULL, NULL, NULL, NULL, 'active', 'adminsma42026', NULL, 0),
(61, '001', '123', 'pimpinan', 'kepsek', '2026-03-01 02:50:01', NULL, NULL, NULL, 'L', '', 'active', '001', NULL, 0),
(91, 'bksma4', 'bksma4', 'bksma4', 'bk', '2026-03-11 13:32:47', NULL, NULL, NULL, 'P', NULL, 'active', NULL, NULL, 0),
(6722, '1', '1', '1', 'guru', '2026-03-22 21:15:32', NULL, 13, NULL, 'L', '', 'active', '1', NULL, 0),
(7146, '2', '$2y$10$CnfQiRGml6D81im1.GzgUeFTZAmaQLWKcDLAbql37WTx7FnmKD1bW', 'siswa', 'siswa', '2026-03-24 14:27:13', 1, NULL, NULL, 'L', '', 'active', NULL, '2', 0),
(7147, '01', '01', 'pimpinan', 'kepsek', '2026-03-24 14:33:18', NULL, NULL, NULL, 'L', '', 'active', '01', NULL, 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `assignments`
--
ALTER TABLE `assignments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `teacher_id` (`teacher_id`),
  ADD KEY `fk_assignment_teacher_class` (`teacher_class_id`);

--
-- Indexes for table `assignment_attachments`
--
ALTER TABLE `assignment_attachments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `assignment_id` (`assignment_id`);

--
-- Indexes for table `assignment_classes`
--
ALTER TABLE `assignment_classes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `assignment_id` (`assignment_id`),
  ADD KEY `class_id` (`class_id`);

--
-- Indexes for table `classes`
--
ALTER TABLE `classes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `class_members`
--
ALTER TABLE `class_members`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_member` (`teacher_class_id`,`student_id`);

--
-- Indexes for table `e_counseling`
--
ALTER TABLE `e_counseling`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `grades`
--
ALTER TABLE `grades`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_grade` (`student_id`,`subject_id`,`semester`,`tahun_ajaran`),
  ADD KEY `subject_id` (`subject_id`),
  ADD KEY `teacher_id` (`teacher_id`),
  ADD KEY `class_id` (`class_id`);

--
-- Indexes for table `materials`
--
ALTER TABLE `materials`
  ADD PRIMARY KEY (`id`),
  ADD KEY `teacher_id` (`teacher_id`),
  ADD KEY `fk_materials_teacher_class` (`teacher_class_id`);

--
-- Indexes for table `meet_links`
--
ALTER TABLE `meet_links`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `news`
--
ALTER TABLE `news`
  ADD PRIMARY KEY (`id`),
  ADD KEY `author_id` (`author_id`);

--
-- Indexes for table `news_images`
--
ALTER TABLE `news_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `news_id` (`news_id`);

--
-- Indexes for table `pengaduan`
--
ALTER TABLE `pengaduan`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `schedules`
--
ALTER TABLE `schedules`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `student_grades`
--
ALTER TABLE `student_grades`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_grade` (`student_id`,`subject_id`,`academic_year`,`semester`);

--
-- Indexes for table `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `submissions`
--
ALTER TABLE `submissions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `assignment_id` (`assignment_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `submission_attachments`
--
ALTER TABLE `submission_attachments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `submission_id` (`submission_id`);

--
-- Indexes for table `teacher_classes`
--
ALTER TABLE `teacher_classes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `teacher_id` (`teacher_id`),
  ADD KEY `class_id` (`class_id`);

--
-- Indexes for table `tracer_study`
--
ALTER TABLE `tracer_study`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_tracer` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `assignments`
--
ALTER TABLE `assignments`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=75;

--
-- AUTO_INCREMENT for table `assignment_attachments`
--
ALTER TABLE `assignment_attachments`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `assignment_classes`
--
ALTER TABLE `assignment_classes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=72;

--
-- AUTO_INCREMENT for table `classes`
--
ALTER TABLE `classes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT for table `class_members`
--
ALTER TABLE `class_members`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `e_counseling`
--
ALTER TABLE `e_counseling`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `grades`
--
ALTER TABLE `grades`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `materials`
--
ALTER TABLE `materials`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `meet_links`
--
ALTER TABLE `meet_links`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `news`
--
ALTER TABLE `news`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `news_images`
--
ALTER TABLE `news_images`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `pengaduan`
--
ALTER TABLE `pengaduan`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `schedules`
--
ALTER TABLE `schedules`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1676;

--
-- AUTO_INCREMENT for table `student_grades`
--
ALTER TABLE `student_grades`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `submissions`
--
ALTER TABLE `submissions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `submission_attachments`
--
ALTER TABLE `submission_attachments`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `teacher_classes`
--
ALTER TABLE `teacher_classes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT for table `tracer_study`
--
ALTER TABLE `tracer_study`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7148;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `assignments`
--
ALTER TABLE `assignments`
  ADD CONSTRAINT `assignments_ibfk_1` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_assignment_teacher_class` FOREIGN KEY (`teacher_class_id`) REFERENCES `teacher_classes` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `assignment_attachments`
--
ALTER TABLE `assignment_attachments`
  ADD CONSTRAINT `assignment_attachments_ibfk_1` FOREIGN KEY (`assignment_id`) REFERENCES `assignments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `assignment_classes`
--
ALTER TABLE `assignment_classes`
  ADD CONSTRAINT `assignment_classes_ibfk_1` FOREIGN KEY (`assignment_id`) REFERENCES `assignments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `assignment_classes_ibfk_2` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `grades`
--
ALTER TABLE `grades`
  ADD CONSTRAINT `grades_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `grades_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `grades_ibfk_3` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `grades_ibfk_4` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `materials`
--
ALTER TABLE `materials`
  ADD CONSTRAINT `fk_materials_teacher_class` FOREIGN KEY (`teacher_class_id`) REFERENCES `teacher_classes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `materials_ibfk_1` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `news`
--
ALTER TABLE `news`
  ADD CONSTRAINT `news_ibfk_1` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `news_images`
--
ALTER TABLE `news_images`
  ADD CONSTRAINT `news_images_ibfk_1` FOREIGN KEY (`news_id`) REFERENCES `news` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `submissions`
--
ALTER TABLE `submissions`
  ADD CONSTRAINT `submissions_ibfk_1` FOREIGN KEY (`assignment_id`) REFERENCES `assignments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `submissions_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `submission_attachments`
--
ALTER TABLE `submission_attachments`
  ADD CONSTRAINT `submission_attachments_ibfk_1` FOREIGN KEY (`submission_id`) REFERENCES `submissions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `teacher_classes`
--
ALTER TABLE `teacher_classes`
  ADD CONSTRAINT `teacher_classes_ibfk_1` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `teacher_classes_ibfk_2` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tracer_study`
--
ALTER TABLE `tracer_study`
  ADD CONSTRAINT `fk_tracer_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
