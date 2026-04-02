<?php
// src/guru/export_attendance.php
// Export rekap absensi siswa per kelas sebagai CSV (Excel-compatible)

session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'guru') {
    header("Location: ../../login.php");
    exit;
}

$teacher_id = $_SESSION['user_id'];
$class_id   = intval($_GET['class_id'] ?? 0);

// Verify ownership
$stmt = $pdo->prepare("
    SELECT tc.*, c.name as school_class_name 
    FROM teacher_classes tc
    JOIN classes c ON tc.class_id = c.id
    WHERE tc.id = ? AND tc.teacher_id = ?
");
$stmt->execute([$class_id, $teacher_id]);
$class = $stmt->fetch();

if (!$class) {
    die("Kelas tidak ditemukan atau Anda tidak memiliki akses.");
}

// Fetch Students
$stmt = $pdo->prepare("SELECT id, full_name, nis FROM users WHERE class_id = ? AND role = 'siswa' ORDER BY nis ASC, full_name ASC");
$stmt->execute([$class['class_id']]);
$students = $stmt->fetchAll();

// Fetch attendance sessions (pertemuan) 
$stmt = $pdo->prepare("
    SELECT * FROM assignments 
    WHERE teacher_class_id = ? AND assignment_type = 'absensi' AND teacher_id = ? 
    ORDER BY meeting_number ASC
");
$stmt->execute([$class_id, $teacher_id]);
$attendances_raw = $stmt->fetchAll();

// Deduplicate by meeting_number (keep latest)
$attendances = [];
foreach ($attendances_raw as $att) {
    $m = intval($att['meeting_number']);
    if (!isset($attendances[$m]) || $att['id'] > $attendances[$m]['id']) {
        $attendances[$m] = $att;
    }
}
ksort($attendances);

// Build status map: [student_id][meeting_num] = status
$status_map = [];
$counts_per_student = []; // [student_id] = [hadir, sakit, izin, alpha]
foreach ($students as $s) {
    $status_map[$s['id']] = [];
    $counts_per_student[$s['id']] = ['hadir' => 0, 'sakit' => 0, 'izin' => 0, 'alpha' => 0, 'terlambat' => 0];
}

foreach ($attendances as $m => $att) {
    $sub_stmt = $pdo->prepare("SELECT student_id, status FROM submissions WHERE assignment_id = ?");
    $sub_stmt->execute([$att['id']]);
    foreach ($sub_stmt->fetchAll() as $row) {
        $sid    = $row['student_id'];
        $status = $row['status'];
        $status_map[$sid][$m] = $status;
        if (isset($counts_per_student[$sid][$status])) {
            $counts_per_student[$sid][$status]++;
        }
    }
}

// === Generate CSV ===
$safe_class = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $class['name']);
$filename = "absensi_{$safe_class}_" . date('Ymd_His') . ".csv";

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');
header('Expires: 0');

// BOM for Excel UTF-8
echo "\xEF\xBB\xBF";

$out = fopen('php://output', 'w');

// Title rows
fputcsv($out, ["REKAP ABSENSI SISWA"]);
fputcsv($out, ["Mata Pelajaran / Kelas", $class['name'] . " — " . $class['school_class_name']]);
fputcsv($out, ["Dicetak", date('d F Y, H:i')]);
fputcsv($out, []); // blank

// Header row: No | Nama | NIS | P1 | P2 | ... | H | S | I | A | T
$header = ['No.', 'Nama Siswa', 'NIS'];
foreach (array_keys($attendances) as $m) {
    $header[] = "P-$m";
}
$header = array_merge($header, ['Hadir', 'Sakit', 'Izin', 'Alpha', 'Terlambat']);
fputcsv($out, $header);

// Data rows
$no = 1;
foreach ($students as $s) {
    $row = [$no++, $s['full_name'], $s['nis'] ?? '-'];
    foreach (array_keys($attendances) as $m) {
        $st = $status_map[$s['id']][$m] ?? '-';
        // Abbreviate to single letter for readability
        $label = match ($st) {
            'hadir'     => 'H',
            'sakit'     => 'S',
            'izin'      => 'I',
            'alpha'     => 'A',
            'terlambat' => 'T',
            default     => '-',
        };
        $row[] = $label;
    }
    $c = $counts_per_student[$s['id']];
    $row[] = $c['hadir'];
    $row[] = $c['sakit'];
    $row[] = $c['izin'];
    $row[] = $c['alpha'];
    $row[] = $c['terlambat'];
    fputcsv($out, $row);
}

// Blank then legend
fputcsv($out, []);
fputcsv($out, ['Keterangan:', 'H = Hadir', 'S = Sakit', 'I = Izin', 'A = Alpha', 'T = Terlambat']);

fclose($out);
exit;
