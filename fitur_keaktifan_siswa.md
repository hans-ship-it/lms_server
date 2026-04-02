# Dokumentasi Fitur: Keaktifan Siswa (Portal Pantauan Nilai)

Fitur **Keaktifan Siswa** adalah modul tambahan pada Portal Pantauan Nilai yang dirancang untuk memberikan transparansi penuh kepada siswa mengenai riwayat tugas, nilai, dan umpan balik (feedback) dari guru secara real-time.

## 🌟 Ringkasan Fitur
Berbeda dengan Kartu Hasil Studi (KHS) yang hanya menampilkan nilai akhir semester, fitur Keaktifan Siswa merinci setiap aktivitas tugas yang diberikan oleh guru di kelas. Siswa dapat memantau tugas mana yang sudah dinilai, mana yang masih menunggu, serta tugas yang belum dikumpulkan.

---

## 🚀 Komponen Utama Halaman

### 1. Dashboard Ringkasan (Stats Cards)
Di bagian atas halaman, terdapat empat kartu info cepat:
*   **Total Tugas:** Menampilkan jumlah keseluruhan tugas yang ditujukan untuk kelas siswa.
*   **Sudah Dinilai:** Jumlah tugas yang telah diberikan nilai oleh guru.
*   **Mata Pelajaran:** Jumlah mata pelajaran aktif yang memiliki tugas.
*   **Rata-rata Nilai:** Nilai rata-rata dari seluruh tugas yang sudah dinilai (dengan indikator warna predikat).

### 2. Filter Mata Pelajaran (Dropdown)
Fitur filter intuitif yang memungkinkan siswa untuk:
*   Melihat semua tugas dari seluruh mata pelajaran sekaligus.
*   Menyaring tugas secara spesifik per mata pelajaran (misal: "Bahasa Indonesia" saja) untuk fokus pada perkembangan materi tertentu.

### 3. Tabel Rekapitulasi Tugas
Tabel utama yang dikelompokkan berdasarkan Mata Pelajaran, berisi informasi:
*   **Judul Tugas:** Nama aktivitas/tugas yang diberikan guru.
*   **Status Keaktifan:** Label warna dinamis:
    *   🟢 **Dinilai:** Tugas sudah selesai diperiksa guru.
    *   🔵 **Menunggu Penilaian:** Tugas sudah dikumpulkan namun belum diberi nilai.
    *   🟡 **Belum Dikumpulkan:** Tugas masih dalam periode aktif namun belum ada submisi.
    *   🔴 **Tidak Dikumpulkan:** Tugas melewati batas waktu (deadline) tanpa ada submisi.
*   **Nilai & Predikat:** Skor angka (0-100) disertai predikat huruf (A/B/C/D) yang konsisten dengan standar sekolah.
*   **Feedback Guru:** Catatan atau saran perbaikan langsung dari guru mata pelajaran.
*   **Deadline:** Batas waktu pengumpulan tugas untuk membantu manajemen waktu siswa.

---

## 🛠️ Logika Teknis & Relasi Data

### Akurasi Multi-Mapel (Anti-Duplicate)
Sistem menggunakan logika `teacher_class_id` untuk memastikan tidak ada duplikasi tugas. Jika seorang guru mengajar lebih dari satu mata pelajaran di kelas yang sama, tugas akan muncul tepat di bawah kategori mata pelajaran yang sesuai.

### Kriteria Tampilan
*   Hanya menampilkan tugas dari mata pelajaran yang **sudah dibuatkan Kelas Mapel-nya** oleh guru terkait.
*   Sistem secara otomatis mendeteksi status pengumpulan berdasarkan tabel `submissions`.
*   Tugas dengan tipe 'absensi' disaring agar tabel tetap fokus pada aktivitas akademik 'tugas'.

---

## 📱 Aksesibilitas
*   **Navigasi Cepat:** Tersedia tombol pintas di header halaman Laporan KHS untuk berpindah ke halaman Keaktifan Siswa, dan sebaliknya.
*   **Responsif Mobile:** Tampilan tabel dan kartu statistik secara otomatis menyesuaikan ukuran layar ponsel (smartphone) tanpa memotong informasi penting.

---
*Dokumentasi ini dibuat untuk mempermudah pemahaman penggunaan portal oleh Siswa dan Orang Tua.*
