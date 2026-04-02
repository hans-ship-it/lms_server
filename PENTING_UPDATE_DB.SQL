-- ==========================================================
-- LMS DATABASE MIGRATION SCRIPT
-- Dari: sman4_lms (Hosted/Lama) 
-- Ke: sman4_lms (4) (Local/Baru)
-- ==========================================================

-- 1. UPDATE TABLE: users
-- Menambahkan role 'bk' dan kolom 'password_changed'
ALTER TABLE `users` 
MODIFY COLUMN `role` ENUM('admin','guru','siswa','osis','kepsek','wakasek','bk') NOT NULL DEFAULT 'siswa',
ADD COLUMN `password_changed` TINYINT(1) DEFAULT 0 AFTER `nis`;

-- 2. CREATE TABLE: e_counseling
-- Fitur Pesan/Konsultasi BK
CREATE TABLE IF NOT EXISTS `e_counseling` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) DEFAULT NULL,
  `kelas` varchar(50) DEFAULT NULL,
  `kategori` varchar(100) NOT NULL,
  `pesan` text NOT NULL,
  `status` enum('pending','read','replied') DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- 3. CREATE TABLE: grades
-- Sistem Penilaian (dengan Generated Columns)
CREATE TABLE IF NOT EXISTS `grades` (
  `id` int NOT NULL AUTO_INCREMENT,
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
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_grade` (`student_id`,`subject_id`,`semester`,`tahun_ajaran`),
  KEY `subject_id` (`subject_id`),
  KEY `teacher_id` (`teacher_id`),
  KEY `class_id` (`class_id`),
  CONSTRAINT `grades_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `grades_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `grades_ibfk_3` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `grades_ibfk_4` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- 4. CREATE TABLE: pengaduan
-- Fitur Pengaduan Siswa
CREATE TABLE IF NOT EXISTS `pengaduan` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nama_siswa` varchar(100) DEFAULT NULL,
  `kelas` varchar(50) DEFAULT NULL,
  `kategori` enum('Kendala Belajar','Bullying','Fasilitas','Konsultasi Karir','Lainnya') NOT NULL DEFAULT 'Lainnya',
  `pesan` text NOT NULL,
  `status` enum('Pending','Diproses','Selesai') NOT NULL DEFAULT 'Pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- 5. CREATE TABLE: student_grades
-- Tabel Penilaian Tambahan
CREATE TABLE IF NOT EXISTS `student_grades` (
  `id` int NOT NULL AUTO_INCREMENT,
  `student_id` int NOT NULL,
  `teacher_id` int NOT NULL,
  `subject_id` int NOT NULL,
  `class_id` int NOT NULL,
  `academic_year` varchar(20) NOT NULL,
  `semester` enum('Ganjil','Genap') NOT NULL,
  `grade` decimal(5,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_grade` (`student_id`,`subject_id`,`academic_year`,`semester`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. CREATE TABLE: tracer_study
-- Fitur Penelusuran Alumni
CREATE TABLE IF NOT EXISTS `tracer_study` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `kegiatan` enum('Kuliah','Kerja','Wirausaha','Belum/Tidak Bekerja') NOT NULL,
  `nama_instansi` varchar(255) NOT NULL,
  `jurusan_posisi` varchar(255) NOT NULL,
  `tahun_lulus` year NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `foto` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_tracer` (`user_id`),
  CONSTRAINT `fk_tracer_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

