<?php
// src/guru/grades_input.php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'guru') {
    header("Location: ../../login.php");
    exit;
}

$teacher_id = $_SESSION['user_id'];
$class_id = $_GET['class_id'] ?? null;

if (!$class_id) {
    header("Location: grades.php");
    exit;
}

// Fetch class info
$stmt = $pdo->prepare("SELECT tc.*, c.name as school_class_name FROM teacher_classes tc LEFT JOIN classes c ON tc.class_id = c.id WHERE tc.id = ? AND tc.teacher_id = ?");
$stmt->execute([$class_id, $teacher_id]);
$class_info = $stmt->fetch();

if (!$class_info) {
    die("Akses ditolak atau kelas tidak ditemukan.");
}

$success = "";
$error = "";

$academic_year = $_GET['academic_year'] ?? '2025-2026';
$semester = $_GET['semester'] ?? 'Ganjil';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_grades'])) {
    $academic_year = $_POST['academic_year'];
    $semester = $_POST['semester'];
    $grades = $_POST['grades'] ?? [];
    
    $stmt = $pdo->prepare("
        INSERT INTO student_grades (student_id, teacher_id, subject_id, class_id, academic_year, semester, grade) 
        VALUES (?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE grade = VALUES(grade), updated_at = NOW()
    ");

    $pdo->beginTransaction();
    try {
        $real_class_id = $class_info['class_id'] ? $class_info['class_id'] : 0;
        foreach ($grades as $sid => $val) {
            if ($val === '') continue; 
            $stmt->execute([
                $sid, 
                $teacher_id, 
                $class_info['subject_id'], 
                $real_class_id, 
                $academic_year, 
                $semester, 
                $val
            ]);
        }
        $pdo->commit();
        $success = "Data nilai berhasil disimpan!";
    } catch(Exception $e) {
        $pdo->rollBack();
        $error = "Terjadi kesalahan sistem: " . $e->getMessage();
    }
}

// Fetch Students
if ($class_info['is_special_class']) {
    $stmt = $pdo->prepare("SELECT u.id, u.full_name, u.nis FROM class_members cm JOIN users u ON cm.student_id = u.id WHERE cm.teacher_class_id = ? ORDER BY u.full_name ASC");
    $stmt->execute([$class_id]);
} else {
    $stmt = $pdo->prepare("SELECT id, full_name, nis FROM users WHERE class_id = ? AND role = 'siswa' ORDER BY full_name ASC");
    $stmt->execute([$class_info['class_id']]);
}
$students = $stmt->fetchAll();

// Fetch existing grades
$stmt = $pdo->prepare("SELECT student_id, grade FROM student_grades WHERE teacher_id = ? AND subject_id = ? AND academic_year = ? AND semester = ?");
$stmt->execute([$teacher_id, $class_info['subject_id'], $academic_year, $semester]);
$existing_grades = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Input Nilai - <?php echo htmlspecialchars($class_info['name']); ?></title>
    <link rel="stylesheet" href="/public/assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .main-content { background: #f5f7fb !important; padding: 0 !important; }
        .page-hero {
            background: linear-gradient(135deg, #1e1b4b 0%, #312e81 50%, #4338ca 100%);
            padding: 2.5rem 3rem 4rem; position: relative; overflow: hidden;
        }
        .page-hero::before { content:''; position:absolute; right:-60px; top:-60px; width:250px; height:250px; background:rgba(255,255,255,0.07); border-radius:50%; }
        .page-hero h1 { color:#fff; font-size:1.5rem; font-weight:700; margin:0 0 0.3rem; }
        .page-hero p  { color:rgba(255,255,255,0.75); margin:0; font-size:0.9rem; }
        .back-link { display:inline-flex; align-items:center; gap:6px; color:rgba(255,255,255,0.8); text-decoration:none; font-size:0.85rem; background:rgba(255,255,255,0.1); padding:5px 12px; border-radius:20px; margin-bottom:1rem; }
        .back-link:hover { background:rgba(255,255,255,0.2); }
        .badge-subject { display:inline-block; background:rgba(255,255,255,0.2); color:#fff; padding:3px 12px; border-radius:20px; font-size:0.8rem; font-weight:700; margin-bottom:0.6rem; }
        .page-content { position:relative; margin-top:-2rem; padding:0 3rem 3rem; z-index:10; }
        .db-section { background:#fff; border:1px solid #e8edf5; border-radius:14px; overflow:hidden; margin-bottom:1.25rem; }
        .filters-bar { display:flex; gap:16px; align-items:flex-end; padding:16px 20px; border-bottom:1px solid #f1f5f9; background:#fafbfd; }
        .form-group-filter { display:flex; flex-direction:column; gap:6px; }
        .form-group-filter label { font-size:0.78rem; font-weight:700; color:#475569; text-transform:uppercase; letter-spacing:0.05em; }
        .form-group-filter select { padding:8px 14px; border:1px solid #e2e8f0; border-radius:8px; font-family:inherit; font-weight:600; color:#1e293b; min-width:160px; cursor:pointer; }
        .grades-table { width:100%; border-collapse:collapse; }
        .grades-table th, .grades-table td { padding:13px 20px; text-align:left; border-bottom:1px solid #f1f5f9; }
        .grades-table th { background:#f8fafc; color:#64748b; font-size:0.78rem; font-weight:700; text-transform:uppercase; letter-spacing:0.05em; }
        .grades-table td { color:#1e293b; font-weight:500; }
        .grades-table tr:last-child td { border-bottom:none; }
        .grade-input { width:80px; padding:8px; border:2px solid #e2e8f0; border-radius:8px; text-align:center; font-weight:700; font-family:inherit; transition:all 0.2s; }
        .grade-input:focus { border-color:#4f46e5; outline:none; box-shadow:0 0 0 3px rgba(79,70,229,0.1); }
        .top-actions { display:flex; gap:10px; }
        .btn-outline { background:transparent; border:1.5px solid #e2e8f0; color:#475569; padding:8px 16px; border-radius:8px; font-weight:600; cursor:pointer; text-decoration:none; display:flex; align-items:center; gap:6px; transition:all 0.2s; font-size:0.88rem; }
        .btn-outline:hover { border-color:#cbd5e1; background:#f8fafc; }
        .sticky-footer { position:sticky; bottom:0; background:white; padding:16px 24px; border-top:1px solid #e2e8f0; display:flex; justify-content:flex-end; gap:12px; z-index:10; }
        @media (max-width:768px) { .page-content { padding:0 1rem 2rem; } .filters-bar { flex-direction:column; } }
    </style>
</head>
<body>

<div class="app-container">
    <?php include '../templates/sidebar.php'; ?>
    
    <main class="main-content">
        <div class="page-hero">
            <a href="grades.php" class="back-link">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5"/><path d="M12 19l-7-7 7-7"/></svg>
                Kembali
            </a>
            <span class="badge-subject"><?php echo htmlspecialchars($class_info['subject']); ?></span>
            <h1><?php echo htmlspecialchars($class_info['name']); ?></h1>
            <p>
                <?php echo $class_info['is_special_class'] ? 'Siswa Gabungan (Lintas Kelas)' : htmlspecialchars($class_info['school_class_name']); ?>
                &bull; <?php echo count($students); ?> Siswa
            </p>
        </div>

        <div class="page-content">

        <?php if ($success): ?>
            <div style="background:#f0fdf4; border:1px solid #bbf7d0; color:#166534; padding:13px 18px; border-radius:10px; margin-bottom:16px; font-weight:600;">
                ✓ <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div style="background:#fef2f2; border:1px solid #fecaca; color:#991b1b; padding:13px 18px; border-radius:10px; margin-bottom:16px; font-weight:600;">
                ⚠️ <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <div style="display:flex; justify-content:flex-end; margin-bottom:1rem;">
            <a href="grades_import.php?class_id=<?php echo $class_id; ?>" class="btn-outline" style="border-color:#10b981; color:#047857; background:#ecfdf5;">📥 Import Excel / CSV</a>
        </div>

        <div class="db-section">
            <form method="GET" action="" id="filterForm" class="filters-bar">
                <input type="hidden" name="class_id" value="<?php echo $class_id; ?>">
                <div class="form-group-filter">
                    <label>Tahun Ajaran</label>
                    <select name="academic_year" onchange="document.getElementById('filterForm').submit()">
                        <option value="2023-2024" <?php if($academic_year=='2023-2024') echo 'selected'; ?>>2023-2024</option>
                        <option value="2024-2025" <?php if($academic_year=='2024-2025') echo 'selected'; ?>>2024-2025</option>
                        <option value="2025-2026" <?php if($academic_year=='2025-2026') echo 'selected'; ?>>2025-2026</option>
                        <option value="2026-2027" <?php if($academic_year=='2026-2027') echo 'selected'; ?>>2026-2027</option>
                    </select>
                </div>
                <div class="form-group-filter">
                    <label>Semester</label>
                    <select name="semester" onchange="document.getElementById('filterForm').submit()">
                        <option value="Ganjil" <?php if($semester=='Ganjil') echo 'selected'; ?>>Ganjil</option>
                        <option value="Genap" <?php if($semester=='Genap') echo 'selected'; ?>>Genap</option>
                    </select>
                </div>
            </form>

        <form method="POST" action="">
            <input type="hidden" name="academic_year" value="<?php echo htmlspecialchars($academic_year); ?>">
            <input type="hidden" name="semester" value="<?php echo htmlspecialchars($semester); ?>">
            <table class="grades-table">
                    <thead>
                        <tr>
                            <th style="width: 50px; text-align: center;">No</th>
                            <th style="width: 150px;">NIS</th>
                            <th>Nama Siswa</th>
                            <th style="width: 150px; text-align: center;">Nilai Akhir</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($students)): ?>
                            <tr><td colspan="4" style="text-align: center; padding: 40px; color: #64748b;">Belum ada siswa terdaftar di kelas ini.</td></tr>
                        <?php else: ?>
                            <?php foreach ($students as $index => $s): 
                                $current_val = $existing_grades[$s['id']] ?? '';
                            ?>
                            <tr>
                                <td style="text-align: center; color: #94a3b8;"><?php echo $index + 1; ?></td>
                                <td style="color: #64748b;"><?php echo htmlspecialchars($s['nis'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($s['full_name']); ?></td>
                                <td style="text-align: center;">
                                    <input type="number" step="0.01" min="0" max="100" name="grades[<?php echo $s['id']; ?>]" value="<?php echo htmlspecialchars((string)$current_val); ?>" class="grade-input" placeholder="0-100">
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
            </table>
        </div><!-- db-section -->

            <?php if (!empty($students)): ?>
            <div class="sticky-footer">
                <button type="submit" name="save_grades" class="btn" style="padding:10px 28px; border-radius:10px; background:#4f46e5; color:white; border:none; font-weight:700; cursor:pointer;">
                    💾 Simpan Nilai
                </button>
            </div>
            <?php endif; ?>
        </form>
        </div><!-- end page-content -->
    </main>
</div>

</body>
</html>
