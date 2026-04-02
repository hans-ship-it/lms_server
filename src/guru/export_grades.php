<?php
// src/guru/export_grades.php
// Exports submission grades for a given assignment as an Excel-compatible CSV file.

session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'guru') {
    header("Location: ../../login.php");
    exit;
}

$assignment_id = intval($_GET['assignment_id'] ?? 0);
$teacher_id    = $_SESSION['user_id'];

// Verify ownership
$stmt = $pdo->prepare("SELECT * FROM assignments WHERE id = ? AND teacher_id = ?");
$stmt->execute([$assignment_id, $teacher_id]);
$assignment = $stmt->fetch();

if (!$assignment) {
    die("Tugas tidak ditemukan atau Anda tidak memiliki akses.");
}

// Fetch class names for this assignment
$stmtC = $pdo->prepare("
    SELECT GROUP_CONCAT(classes.name ORDER BY classes.name SEPARATOR ', ') as class_names
    FROM assignment_classes
    JOIN classes ON assignment_classes.class_id = classes.id
    WHERE assignment_classes.assignment_id = ?
");
$stmtC->execute([$assignment_id]);
$class_names = $stmtC->fetchColumn() ?: '-';

// Fetch all submissions with student info
$stmt = $pdo->prepare("
    SELECT s.*, u.full_name, u.username,
    (SELECT COUNT(*) FROM submission_attachments WHERE submission_id = s.id) as file_count
    FROM submissions s
    JOIN users u ON s.student_id = u.id
    WHERE s.assignment_id = ?
    ORDER BY u.full_name ASC
");
$stmt->execute([$assignment_id]);
$submissions = $stmt->fetchAll();

// ── Generate CSV ──────────────────────────────────────────────────────────────
$safe_title = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $assignment['title']);
$filename   = "nilai_{$safe_title}_" . date('Ymd_His') . ".csv";

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');
header('Expires: 0');

// BOM for Excel UTF-8 compatibility
echo "\xEF\xBB\xBF";

$out = fopen('php://output', 'w');

// ── Nama Tugas ────────────────────────────────────────────────────────────────
fputcsv($out, [$assignment['title']]);
fputcsv($out, []); // blank row

// ── Column Headers ────────────────────────────────────────────────────────────
fputcsv($out, ['No.', 'Tanggal', 'Nama', 'Nilai']);

// ── Data Rows ─────────────────────────────────────────────────────────────────
$no = 1;
foreach ($submissions as $sub) {
    $tanggal = !empty($sub['submitted_at']) ? date('d M Y, H:i', strtotime($sub['submitted_at'])) : '-';
    $nilai   = $sub['grade'] !== null ? $sub['grade'] : '-';

    fputcsv($out, [
        $no++,
        $tanggal,
        $sub['full_name'],
        $nilai,
    ]);
}

fclose($out);
exit;
