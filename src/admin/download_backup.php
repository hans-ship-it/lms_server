<?php
// src/admin/download_backup.php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    exit("Akses ditolak.");
}

$pathToDownload = $_GET['path'] ?? '';
if (empty($pathToDownload)) {
    exit("Path tidak valid.");
}

// Keamanan dasar untuk mencegah directory traversal traversal
if (strpos($pathToDownload, '..') !== false) {
    exit("Tindakan tidak diizinkan.");
}

// Pastikan path memang berawalan 'backups/'
if (strpos($pathToDownload, 'backups') !== 0) {
    exit("Hanya diizinkan mengunduh dari direktori backups.");
}

// Transform path ke real server absolute path
$realPath = realpath(__DIR__ . '/../../' . $pathToDownload);

if (!$realPath || !file_exists($realPath)) {
    exit("File atau folder arsip tidak ditemukan di server.");
}

if (is_dir($realPath)) {
    // Jika path adalah FOLDER (Misal folder "Maret" atau "Minggu-1"), buat ZIP on the fly bundling
    $folderName = basename($realPath);
    $parentDir = basename(dirname($realPath));
    $bundlingName = "Bundle_Backup_{$parentDir}_{$folderName}.zip";
    
    $zipTemp = sys_get_temp_dir() . '/' . uniqid('bundling_') . '.zip';
    
    $zip = new ZipArchive();
    if ($zip->open($zipTemp, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($realPath, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::LEAVES_ONLY
        );
        
        $hasFiles = false;
        foreach ($files as $name => $file) {
            if (!$file->isDir()) {
                $filePath = $file->getRealPath();
                // Hirarki di dalam arsip hasil download
                $relativePath = substr($filePath, strlen($realPath) + 1);
                $zip->addFile($filePath, $relativePath);
                $hasFiles = true;
            }
        }
        $zip->close();
        
        if ($hasFiles) {
            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename="' . $bundlingName . '"');
            header('Content-Length: ' . filesize($zipTemp));
            header('Pragma: public');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Expires: 0');
            readfile($zipTemp);
            unlink($zipTemp);
            exit;
        } else {
            unlink($zipTemp);
            exit("Folder tersebut belum memiliki file backup di dalamnya.");
        }
    } else {
        exit("Sistem gagal membuat arsip ZIP gabungan.");
    }
    
} else {
    // Jika path adalah FILE `.zip` atau `.sql` tunggal (harian), force download langsung
    $ext = pathinfo($realPath, PATHINFO_EXTENSION);
    $mime = ($ext === 'zip') ? 'application/zip' : 'application/sql';
    
    header('Content-Type: ' . $mime);
    header('Content-Disposition: attachment; filename="' . basename($realPath) . '"');
    header('Content-Length: ' . filesize($realPath));
    header('Pragma: public');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Expires: 0');
    
    // Output file batch
    $file = @fopen($realPath, "rb");
    if ($file) {
        while (!feof($file)) {
            print(@fread($file, 1024 * 8));
            ob_flush();
            flush();
        }
    }
    exit;
}
?>
