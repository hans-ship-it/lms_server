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
        .page-header {
            background: white;
            padding: 24px 32px;
            border-radius: 16px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            margin-bottom: 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .class-title {
            font-size: 1.5rem;
            font-weight: 800;
            color: #1e293b;
            margin-bottom: 4px;
        }
        .class-meta {
            color: #64748b;
            font-size: 0.95rem;
            font-weight: 500;
        }
        .filters-form {
            background: white;
            padding: 20px 32px;
            border-radius: 16px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            margin-bottom: 24px;
            display: flex;
            gap: 20px;
            align-items: flex-end;
        }
        .form-group-filter {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .form-group-filter label {
            font-size: 0.85rem;
            font-weight: 700;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .form-group-filter select {
            padding: 10px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-family: inherit;
            font-weight: 600;
            color: #1e293b;
            min-width: 180px;
            cursor: pointer;
        }
        .grades-panel {
            background: white;
            border-radius: 16px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            overflow: hidden;
        }
        .grades-table {
            width: 100%;
            border-collapse: collapse;
        }
        .grades-table th, .grades-table td {
            padding: 16px 24px;
            text-align: left;
            border-bottom: 1px solid #f1f5f9;
        }
        .grades-table th {
            background: #f8fafc;
            color: #64748b;
            font-size: 0.85rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .grades-table td {
            color: #1e293b;
            font-weight: 500;
        }
        .grade-input {
            width: 80px;
            padding: 10px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            text-align: center;
            font-weight: 700;
            font-family: inherit;
            transition: all 0.2s;
        }
        .grade-input:focus {
            border-color: #4f46e5;
            outline: none;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }
        .top-actions {
            display: flex; gap: 12px;
        }
        .btn-outline {
            background: transparent;
            border: 2px solid #e2e8f0;
            color: #475569;
            padding: 10px 20px;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
        }
        .btn-outline:hover {
            border-color: #cbd5e1;
            background: #f8fafc;
        }
        .sticky-footer {
            position: sticky;
            bottom: 0;
            background: white;
            padding: 20px 32px;
            border-top: 1px solid #e2e8f0;
            display: flex;
            justify-content: flex-end;
            gap: 16px;
            z-index: 10;
            box-shadow: 0 -4px 6px -1px rgba(0,0,0,0.05);
        }
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            background: #eef2ff;
            color: #4338ca;
            font-size: 0.8rem;
            font-weight: 700;
            margin-bottom: 8px;
        }
    </style>
</head>
<body>

<div class="app-container">
    <?php include '../templates/sidebar.php'; ?>
    
    <main class="main-content">
        <?php if ($success): ?>
            <div style="background: #dcfce7; border: 1px solid #bbf7d0; color: #166534; padding: 16px; border-radius: 12px; margin-bottom: 24px; font-weight: 600;">
                ✓ <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div style="background: #fee2e2; border: 1px solid #fecaca; color: #991b1b; padding: 16px; border-radius: 12px; margin-bottom: 24px; font-weight: 600;">
                ⚠️ <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <div class="page-header">
            <div>
                <span class="badge"><?php echo htmlspecialchars($class_info['subject']); ?></span>
                <div class="class-title"><?php echo htmlspecialchars($class_info['name']); ?></div>
                <div class="class-meta">
                    <?php echo $class_info['is_special_class'] ? 'Siswa Gabungan (Lintas Kelas)' : htmlspecialchars($class_info['school_class_name']); ?> • <?php echo count($students); ?> Siswa
                </div>
            </div>
            <div class="top-actions">
                <a href="grades.php" class="btn-outline">&larr; Kembali</a>
                <a href="grades_import.php?class_id=<?php echo $class_id; ?>" class="btn-outline" style="border-color: #10b981; color: #047857; background: #ecfdf5;">📥 Import Excel / CSV</a>
            </div>
        </div>

        <form method="GET" action="" id="filterForm" class="filters-form">
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
            
            <div class="grades-panel">
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
            </div>

            <?php if (!empty($students)): ?>
            <div class="sticky-footer">
                <button type="submit" name="save_grades" class="btn" style="padding: 12px 30px; border-radius: 12px; background: #4f46e5; color: white; border: none; font-weight: 700; cursor: pointer;">
                    💾 Simpan Nilai
                </button>
            </div>
            <?php endif; ?>
        </form>

    </main>
</div>

</body>
</html>
