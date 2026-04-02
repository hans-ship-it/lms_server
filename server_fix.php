<?php
// server_fix.php
// Script untuk memperbaiki Bug Karakter Aneh (Mojibake) langsung di Server Hosting.

echo "<h2>Memulai Perbaikan...</h2>";
echo "<ul>";

$base_dir = __DIR__;

// 1. Perbaiki Database Config (Menambahkan UTF-8 Support)
$db_file = $base_dir . '/config/database.php';
if (file_exists($db_file)) {
    $db_content = file_get_contents($db_file);
    if (strpos($db_content, 'SET NAMES utf8mb4') === false) {
        // Cari posisi time_zone
        $target = "\$pdo->exec(\"SET time_zone = '+08:00'\");";
        if (strpos($db_content, $target) !== false) {
            $replacement = "\$pdo->exec(\"SET time_zone = '+08:00'\");\n    \$pdo->exec(\"SET NAMES utf8mb4\");";
            $db_content = str_replace($target, $replacement, $db_content);
            file_put_contents($db_file, $db_content);
            echo "<li>[UPDATE] <b>config/database.php</b> berhasil diperbaiki (Support utf8mb4 ditambahkan).</li>";
        } else {
            echo "<li>[INFO] time_zone tidak ditemukan di <b>config/database.php</b>.</li>";
        }
    } else {
        echo "<li>[SKIP] <b>config/database.php</b> sudah mensupport utf8mb4 secara benar.</li>";
    }
} else {
    echo "<li>[ERROR] <b>config/database.php</b> tidak ditemukan!</li>";
}


// 2. Daftar file yang mengandung karakter mojibake
$files_to_fix = [
    'index.php',
    'pantauan_nilai.php',
    'keaktifan_siswa.php',
    'src/admin/dashboard.php',
    'src/pimpinan/dashboard.php',
    'src/osis/dashboard.php',
    'src/guru/dashboard.php',
    'src/bk/dashboard.php',
    'src/admin/manage_users.php',
    'src/guru/grades_input.php',
    'src/siswa/kelas_detail_siswa.php'
];

$replacements = [
    'â€”' => '&mdash;',
    'â€º' => '&rsaquo;',
    'â€¢' => '&bull;',
    'â€“' => '&ndash;',
    'â‰¥' => '&ge;',
    'â ³' => '&#x23F3;',
    'âœ“' => '&check;',
    'âœ•' => '&times;'
];

// Loop perbaiki tiap file
foreach ($files_to_fix as $file) {
    $path = $base_dir . '/' . $file;
    if (file_exists($path)) {
        $content = file_get_contents($path);
        
        $newContent = str_replace(array_keys($replacements), array_values($replacements), $content);
        if ($newContent !== $content) {
            @chmod($path, 0666); // Coba ubah permission agar bisa ditulisi
            $is_saved = @file_put_contents($path, $newContent);
            @chmod($path, 0644); // Kembalikan permission
            
            if ($is_saved !== false) {
                echo "<li><span style='color:green;'>[SUCCESS]</span> Teks mojibake di <b>{$file}</b> berhasil diperbaiki!</li>";
            } else {
                echo "<li><span style='color:red;'>[FAILED]</span> Gagal menyimpan <b>{$file}</b>! Server menolak izin *write*. Anda HARUS mengupload file ini secara manual dari komputer Anda.</li>";
            }
        } else {
            echo "<li><span style='color:blue;'>[SKIP]</span> <b>{$file}</b> sudah bersih, tidak ada tulisan aneh.</li>";
        }
    } else {
        echo "<li><span style='color:red;'>[ERROR] File <b>{$file}</b> tidak ditemukan di server.</span></li>";
    }
}

echo "</ul>";
echo "<h3>Selesai!</h3>";
echo "<p style='color: red; font-weight:bold;'>PENTING: Setelah berhasil, segera HAPUS file <b>server_fix.php</b> ini dari server hosting Anda demi keamanan!</p>";
?>
