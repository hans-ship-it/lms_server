<?php
// src/siswa/kelas_siswa.php
// Halaman "Kelas Saya" â€” menampilkan kelas siswa beserta ringkasan materi & tugas
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'siswa') {
    header("Location: ../../login.php");
    exit;
}

$student_id = $_SESSION['user_id'];

// Get student's class info
$stmt = $pdo->prepare("
    SELECT u.class_id, c.name as class_name, c.grade, c.major
    FROM users u
    LEFT JOIN classes c ON u.class_id = c.id
    WHERE u.id = ?
");
$stmt->execute([$student_id]);
$student = $stmt->fetch();
$class_id   = $student['class_id'];
$class_name = $student['class_name'] ?? null;

// Count materials for this class
$total_materials = 0;
if ($class_id) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM materials WHERE class_id = ? OR class_id IS NULL");
    $stmt->execute([$class_id]);
    $total_materials = $stmt->fetchColumn();
}

// Count active assignments
$total_assignments = 0;
if ($class_id) {
    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM assignments a
        JOIN assignment_classes ac ON a.id = ac.assignment_id
        WHERE ac.class_id = ? AND a.status = 'active'
    ");
    $stmt->execute([$class_id]);
    $total_assignments = $stmt->fetchColumn();
}

// Count pending (belum dikerjakan)
$pending = 0;
if ($class_id) {
    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM assignments a
        JOIN assignment_classes ac ON a.id = ac.assignment_id
        WHERE ac.class_id = ? AND a.status = 'active'
        AND NOT EXISTS (
            SELECT 1 FROM submissions s WHERE s.assignment_id = a.id AND s.student_id = ?
        )
    ");
    $stmt->execute([$class_id, $student_id]);
    $pending = $stmt->fetchColumn();
}

// Get teachers for this class (from assignments and materials)
$teachers = [];
if ($class_id) {
    $stmt = $pdo->prepare("
        SELECT DISTINCT u.full_name, s.name as subject_name
        FROM users u
        LEFT JOIN subjects s ON u.subject_id = s.id
        WHERE u.role = 'guru'
        AND (
            EXISTS (SELECT 1 FROM materials m WHERE m.teacher_id = u.id AND (m.class_id = ? OR m.class_id IS NULL))
            OR
            EXISTS (SELECT 1 FROM assignments a JOIN assignment_classes ac ON a.id = ac.assignment_id WHERE a.teacher_id = u.id AND ac.class_id = ?)
        )
        ORDER BY u.full_name ASC
        LIMIT 6
    ");
    $stmt->execute([$class_id, $class_id]);
    $teachers = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelas Saya</title>
    <link rel="stylesheet" href="/public/assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        .main-content { background: #f5f7fb !important; padding: 0 !important; max-width: 100% !important; }

        .page-hero {
            background: linear-gradient(135deg, #1e1b4b 0%, #312e81 50%, #4338ca 100%);
            padding: 2.5rem 3rem 5rem;
            position: relative;
            overflow: hidden;
        }
        .page-hero::after {
            content: '';
            position: absolute;
            width: 400px; height: 400px;
            top: -200px; right: -100px;
            background: radial-gradient(circle, rgba(129,140,248,0.25) 0%, transparent 60%);
            border-radius: 50%;
            pointer-events: none;
        }
        .hero-inner { position: relative; z-index: 2; }
        .hero-inner h1 { font-size: 1.7rem; font-weight: 800; color: #fff; margin-bottom: 0.3rem; }
        .hero-sub { color: rgba(255,255,255,0.6); font-size: 0.9rem; }

        .page-content {
            position: relative;
            margin-top: -2.5rem;
            padding: 0 3rem 3rem;
            z-index: 10;
        }

        /* Class Card */
        .class-card {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.06);
            overflow: hidden;
            transition: transform 0.25s, box-shadow 0.25s;
            text-decoration: none;
            color: inherit;
            display: flex;
            flex-direction: column;
        }
        .class-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 36px rgba(0,0,0,0.1);
        }
        .class-card-header {
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            padding: 1.8rem 1.5rem 1rem;
            position: relative;
        }
        .class-card-header h2 {
            color: #fff;
            font-size: 1.4rem;
            font-weight: 800;
            margin: 0 0 4px;
        }
        .class-card-header .grade { color: rgba(255,255,255,0.7); font-size: 0.85rem; }

        .class-card-body { padding: 1.5rem; flex: 1; }

        .stat-row {
            display: flex;
            gap: 12px;
            margin-bottom: 1.2rem;
        }
        .stat-pill {
            flex: 1;
            text-align: center;
            background: #f8fafc;
            border-radius: 12px;
            padding: 12px 8px;
        }
        .stat-pill .sp-num {
            font-size: 1.5rem;
            font-weight: 800;
            color: #4f46e5;
            line-height: 1;
        }
        .stat-pill.green .sp-num { color: #059669; }
        .stat-pill.red .sp-num   { color: #dc2626; }
        .stat-pill .sp-lbl {
            font-size: 0.68rem;
            font-weight: 600;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-top: 4px;
        }

        .pending-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: #fef3c7;
            color: #92400e;
            font-size: 0.78rem;
            font-weight: 700;
            padding: 5px 12px;
            border-radius: 20px;
            margin-bottom: 1rem;
        }

        .teacher-list { display: flex; flex-wrap: wrap; gap: 6px; }
        .teacher-chip {
            background: #eef2ff;
            color: #4f46e5;
            font-size: 0.72rem;
            font-weight: 600;
            padding: 3px 10px;
            border-radius: 20px;
        }

        .enter-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            background: #4f46e5;
            color: #fff;
            padding: 11px;
            border-radius: 10px;
            font-weight: 700;
            font-size: 0.88rem;
            margin-top: 1.2rem;
            transition: background 0.2s;
        }
        .class-card:hover .enter-btn { background: #4338ca; }

        .no-class-card {
            background: #fff;
            border-radius: 20px;
            padding: 3rem;
            text-align: center;
            color: #94a3b8;
            box-shadow: 0 4px 24px rgba(0,0,0,0.06);
        }
    </style>
</head>
<body>
<div class="app-container">
    <?php include '../templates/sidebar.php'; ?>
    <main class="main-content">

        <!-- Hero -->
        <div class="page-hero">
            <div class="hero-inner">
                <h1>Kelas Saya</h1>
                <p class="hero-sub">Akses materi dan tugas dari kelas Anda</p>
            </div>
        </div>

        <div class="page-content">
            <?php if (!$class_id || !$class_name): ?>
            <div class="no-class-card">
                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom:12px;"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                <p style="font-size:1rem; color:#64748b; margin-bottom:4px; font-weight:600;">Anda belum terdaftar di kelas manapun.</p>
                <p style="font-size:0.85rem;">Hubungi Admin untuk mendaftarkan Anda ke kelas.</p>
            </div>

            <?php else: ?>
            <!-- Class Card -->
            <a href="kelas_detail_siswa.php?class_id=<?php echo intval($class_id); ?>" class="class-card">
                <div class="class-card-header">
                    <h2><?php echo htmlspecialchars($class_name); ?></h2>
                    <?php
                    $sub_label = '';
                    if (!empty($student['grade']))  $sub_label .= 'Kelas ' . htmlspecialchars($student['grade']);
                    if (!empty($student['major']))  $sub_label .= ' Â· ' . htmlspecialchars($student['major']);
                    ?>
                    <?php if ($sub_label): ?>
                        <div class="grade"><?php echo $sub_label; ?></div>
                    <?php endif; ?>
                </div>

                <div class="class-card-body">
                    <!-- Stats -->
                    <div class="stat-row">
                        <div class="stat-pill">
                            <div class="sp-num"><?php echo $total_materials; ?></div>
                            <div class="sp-lbl">Materi</div>
                        </div>
                        <div class="stat-pill green">
                            <div class="sp-num"><?php echo $total_assignments; ?></div>
                            <div class="sp-lbl">Tugas Aktif</div>
                        </div>
                        <div class="stat-pill red">
                            <div class="sp-num"><?php echo $pending; ?></div>
                            <div class="sp-lbl">Belum Dikerjakan</div>
                        </div>
                    </div>

                    <?php if ($pending > 0): ?>
                    <div class="pending-badge">
                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        <?php echo $pending; ?> tugas menunggu dikerjakan
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($teachers)): ?>
                    <p style="font-size:0.72rem; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:.05em; margin-bottom:6px;">Guru</p>
                    <div class="teacher-list">
                        <?php foreach ($teachers as $t): ?>
                        <span class="teacher-chip"><?php echo htmlspecialchars($t['full_name']); ?><?php if ($t['subject_name']): ?> Â· <?php echo htmlspecialchars($t['subject_name']); ?><?php endif; ?></span>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                    <div class="enter-btn">
                        Masuk ke Kelas
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                    </div>
                </div>
            </a>
            <?php endif; ?>
        </div>

    </main>
</div>
</body>
</html>

