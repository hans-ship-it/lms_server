<?php
$files = [
    'keaktifan_siswa.php',
    'pantauan_nilai.php',
    'src/admin/dashboard.php',
    'src/pimpinan/dashboard.php',
    'src/osis/dashboard.php',
    'src/guru/dashboard.php',
    'src/bk/dashboard.php',
    'src/admin/manage_users.php',
    'src/guru/grades_input.php'
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

foreach ($files as $file) {
    // Pakai absolute path c:/laragon/www/lms/...
    $path = 'c:/laragon/www/lms/' . $file;
    if (file_exists($path)) {
        $content = file_get_contents($path);
        
        $newContent = str_replace(array_keys($replacements), array_values($replacements), $content);
        if ($newContent !== $content) {
            file_put_contents($path, $newContent);
            echo "Fixed $file\n";
        }
    }
}
echo "Done.\n";
