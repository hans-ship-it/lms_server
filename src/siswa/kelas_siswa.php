<?php
// src/siswa/kelas_siswa.php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'siswa') {
    header("Location: ../../login.php");
    exit;
}

$student_id = $_SESSION['user_id'];

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

$total_materials = 0;
if ($class_id) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM materials WHERE class_id = ? OR class_id IS NULL");
    $stmt->execute([$class_id]);
    $total_materials = $stmt->fetchColumn();
}

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
        LIMIT 8
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
            padding: 2.5rem 3rem 4rem;
            position: relative; overflow: hidden;
        }
        .page-hero::after {
            content: '';
            position: absolute;
            width: 400px; height: 400px; top:-200px; right:-100px;
            background: radial-gradient(circle, rgba(129,140,248,.25) 0%, transparent 60%);
            border-radius: 50%; pointer-events: none;
        }
        .hero-inner { position: relative; z-index: 2; }
        .hero-inner h1 { font-size: 1.6rem; font-weight: 800; color: #fff; margin-bottom: 0.3rem; }
        .hero-sub { color: rgba(255,255,255,0.6); font-size: 0.9rem; }

        .page-content {
            position: relative;
            margin-top: -2rem;
            padding: 0 3rem 3rem;
            z-index: 10;
            max-width: 700px;
        }

        /* ── Kelas Info Panel ── */
        .kelas-panel {
            background: #fff;
            border-radius: 14px;
            border: 1px solid #e8edf5;
            overflow: hidden;
            margin-bottom: 20px;
        }

        .kelas-header {
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            padding: 1.5rem 1.8rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .kelas-name { color: #fff; font-size: 1.5rem; font-weight: 800; letter-spacing: -0.02em; }
        .kelas-grade { color: rgba(255,255,255,0.7); font-size: 0.85rem; margin-top: 2px; }
        .kelas-badge {
            background: rgba(255,255,255,0.2);
            color: #fff;
            font-size: 0.75rem;
            font-weight: 700;
            padding: 5px 14px;
            border-radius: 20px;
            backdrop-filter: blur(4px);
        }

        /* ── Stat row ── */
        .stat-row {
            display: flex;
            border-bottom: 1px solid #f1f5f9;
        }
        .stat-cell {
            flex: 1;
            padding: 16px 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            position: relative;
        }
        .stat-cell + .stat-cell::before {
            content: '';
            position: absolute; left: 0; top: 20%; height: 60%;
            width: 1px; background: #f1f5f9;
        }
        .stat-ico2 {
            width: 36px; height: 36px;
            border-radius: 9px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .stat-ico2.purple { background: #ede9fe; color: #7c3aed; }
        .stat-ico2.green  { background: #d1fae5; color: #059669; }
        .stat-ico2.red    { background: #fee2e2; color: #dc2626; }
        .stat-num2 { font-size: 1.5rem; font-weight: 900; letter-spacing: -0.03em; line-height: 1; color: #1e293b; }
        .stat-lbl2 { font-size: 0.72rem; font-weight: 600; color: #94a3b8; margin-top: 2px; }

        /* ── Guru list ── */
        .section-body { padding: 16px 20px; }
        .section-label {
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.07em;
            color: #94a3b8;
            margin-bottom: 12px;
        }
        .teacher-list { display: flex; flex-direction: column; gap: 0; }
        .teacher-row {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px 0;
            border-bottom: 1px solid #f8fafc;
            font-size: 0.84rem;
        }
        .teacher-row:last-child { border-bottom: none; }
        .teacher-ava {
            width: 30px; height: 30px;
            border-radius: 50%;
            background: #eef2ff;
            color: #4f46e5;
            display: flex; align-items: center; justify-content: center;
            font-size: 0.72rem; font-weight: 800;
            flex-shrink: 0;
        }
        .teacher-name { font-weight: 600; color: #1e293b; flex: 1; }
        .teacher-subj { font-size: 0.72rem; color: #94a3b8; }

        /* ── Action buttons ── */
        .action-list { display: flex; flex-direction: column; gap: 0; }
        .action-item {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 14px 20px;
            text-decoration: none;
            color: inherit;
            border-bottom: 1px solid #f1f5f9;
            transition: background 0.15s;
        }
        .action-item:last-child { border-bottom: none; }
        .action-item:hover { background: #fafbff; }
        .action-ico {
            width: 36px; height: 36px;
            border-radius: 9px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .action-label { font-size: 0.9rem; font-weight: 700; color: #1e293b; flex: 1; }
        .action-arrow { color: #cbd5e1; }

        .pending-notice {
            background: #fffbeb;
            border: 1px solid #fde68a;
            border-radius: 10px;
            padding: 10px 16px;
            font-size: 0.82rem;
            color: #92400e;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 16px;
        }

        .no-class-state {
            background: #fff;
            border-radius: 12px;
            border: 1px solid #e8edf5;
            text-align: center;
            padding: 3rem 1rem;
            color: #94a3b8;
        }
        .no-class-state svg { opacity: 0.35; margin-bottom: 10px; }

        @media (max-width: 900px) {
            .page-hero { padding: 2rem 1.5rem 3.5rem; }
            .page-content { padding: 0 1.2rem 2rem; max-width: 100%; }
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
                <p class="hero-sub">Akses materi, tugas, dan informasi kelas Anda</p>
            </div>
        </div>

        <div class="page-content">
            <?php if (!$class_id || !$class_name): ?>
            <div class="no-class-state">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                <p style="font-size:0.95rem; color:#334155; font-weight:600; margin-bottom:4px;">Anda belum terdaftar di kelas manapun.</p>
                <p style="font-size:0.82rem;">Hubungi Admin untuk mendaftarkan Anda ke kelas.</p>
            </div>

            <?php else: ?>

            <?php if ($pending > 0): ?>
            <div class="pending-notice">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <?php echo $pending; ?> tugas belum dikerjakan — segera selesaikan!
            </div>
            <?php endif; ?>

            <!-- Kelas Card -->
            <div class="kelas-panel">
                <div class="kelas-header">
                    <div>
                        <div class="kelas-name"><?php echo htmlspecialchars($class_name); ?></div>
                        <?php
                        $sub_label = '';
                        if (!empty($student['grade']))  $sub_label .= 'Kelas ' . htmlspecialchars($student['grade']);
                        if (!empty($student['major']))  $sub_label .= ' · ' . htmlspecialchars($student['major']);
                        if ($sub_label): ?>
                        <div class="kelas-grade"><?php echo $sub_label; ?></div>
                        <?php endif; ?>
                    </div>
                    <span class="kelas-badge">Kelas Aktif</span>
                </div>

                <!-- Stats -->
                <div class="stat-row">
                    <div class="stat-cell">
                        <div class="stat-ico2 purple">
                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/></svg>
                        </div>
                        <div>
                            <div class="stat-num2"><?php echo $total_materials; ?></div>
                            <div class="stat-lbl2">Materi</div>
                        </div>
                    </div>
                    <div class="stat-cell">
                        <div class="stat-ico2 green">
                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                        </div>
                        <div>
                            <div class="stat-num2"><?php echo $total_assignments; ?></div>
                            <div class="stat-lbl2">Tugas Aktif</div>
                        </div>
                    </div>
                    <div class="stat-cell">
                        <div class="stat-ico2 red">
                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        </div>
                        <div>
                            <div class="stat-num2"><?php echo $pending; ?></div>
                            <div class="stat-lbl2">Belum Dikerjakan</div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="action-list">
                    <a href="kelas_detail_siswa.php?class_id=<?php echo intval($class_id); ?>&tab=materi" class="action-item">
                        <div class="action-ico" style="background:#ede9fe; color:#7c3aed;">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/></svg>
                        </div>
                        <span class="action-label">Lihat Materi Pelajaran</span>
                        <div class="action-arrow"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"/></svg></div>
                    </a>
                    <a href="kelas_detail_siswa.php?class_id=<?php echo intval($class_id); ?>&tab=tugas" class="action-item">
                        <div class="action-ico" style="background:#fef3c7; color:#d97706;">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                        </div>
                        <span class="action-label">Lihat Tugas Saya</span>
                        <?php if ($pending > 0): ?>
                        <span style="background:#fee2e2; color:#dc2626; font-size:0.68rem; font-weight:800; padding:2px 8px; border-radius:10px;"><?php echo $pending; ?> belum</span>
                        <?php endif; ?>
                        <div class="action-arrow"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"/></svg></div>
                    </a>
                </div>
            </div>

            <!-- Guru Section -->
            <?php if (!empty($teachers)): ?>
            <div class="kelas-panel">
                <div class="section-body">
                    <div class="section-label">Daftar Guru</div>
                    <div class="teacher-list">
                        <?php foreach ($teachers as $t): ?>
                        <div class="teacher-row">
                            <div class="teacher-ava"><?php echo mb_strtoupper(mb_substr($t['full_name'], 0, 1)); ?></div>
                            <span class="teacher-name"><?php echo htmlspecialchars($t['full_name']); ?></span>
                            <?php if ($t['subject_name']): ?>
                            <span class="teacher-subj"><?php echo htmlspecialchars($t['subject_name']); ?></span>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php endif; ?>
        </div>

    </main>
</div>
</body>
</html>
