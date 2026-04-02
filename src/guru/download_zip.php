<?php
// src/guru/download_zip.php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'guru') {
    die("Akses ditolak.");
}

// ─── Auto-Migration: Ensure is_archived column exists in submissions ───
try {
    $check = $pdo->query("SHOW COLUMNS FROM submissions LIKE 'is_archived'");
    if ($check->rowCount() == 0) {
        $pdo->exec("ALTER TABLE submissions ADD COLUMN is_archived TINYINT(1) DEFAULT 0");
    }
}
catch (Exception $e) {
// Silent fail or log
}
// ─── End Auto-Migration ───

$assignment_id = $_GET['assignment_id'] ?? 0;

// Verify Ownership
$stmt = $pdo->prepare("SELECT title FROM assignments WHERE id = ? AND teacher_id = ?");
$stmt->execute([$assignment_id, $_SESSION['user_id']]);
$assignment = $stmt->fetch();

if (!$assignment) {
    die("Tugas tidak ditemukan.");
}

// Fetch submissions with files
$stmt = $pdo->prepare("
    SELECT s.id, u.full_name, u.username,
    (SELECT GROUP_CONCAT(file_path SEPARATOR ',') FROM submission_attachments WHERE submission_id = s.id) as files,
    s.file_path as single_file
    FROM submissions s
    JOIN users u ON s.student_id = u.id
    WHERE s.assignment_id = ? AND s.is_archived = 0
");
$stmt->execute([$assignment_id]);
$submissions = $stmt->fetchAll();

if (empty($submissions)) {
    die("Belum ada pengumpulan tugas untuk didownload.");
}

// Create ZIP
$zip = new ZipArchive();
$zipName = "Rekap_Tugas_" . preg_replace('/[^a-zA-Z0-9]/', '_', $assignment['title']) . "_" . date('Ymd_His') . ".zip";
$tempFile = sys_get_temp_dir() . '/' . $zipName;

if ($zip->open($tempFile, ZipArchive::CREATE) !== TRUE) {
    die("Gagal membuat file ZIP.");
}

$addedFiles = 0;

foreach ($submissions as $sub) {
    $studentName = preg_replace('/[^a-zA-Z0-9 ]/', '', $sub['full_name']);
    $studentNIS = $sub['username']; // Using username as NIS based on context

    $files = [];
    if (!empty($sub['files'])) {
        $files = explode(',', $sub['files']);
    }
    elseif (!empty($sub['single_file'])) {
        $files = [$sub['single_file']];
    }

    foreach ($files as $idx => $filePath) {
        $fullPath = "../../" . $filePath;
        if (file_exists($fullPath)) {
            $ext = pathinfo($fullPath, PATHINFO_EXTENSION);
            $newFileName = $studentName . "_" . $studentNIS . "_File" . ($idx + 1) . "." . $ext;
            $zip->addFile($fullPath, $newFileName);
            $addedFiles++;
        }
    }
}

$zip->close();

if ($addedFiles > 0) {
    header('Content-Type: application/zip');
    header('Content-disposition: attachment; filename=' . $zipName);
    header('Content-Length: ' . filesize($tempFile));
    readfile($tempFile);
    unlink($tempFile); // Clean up
}
else {
    echo "Tidak ada file fisik yang ditemukan untuk di-zip.";
}
?>
