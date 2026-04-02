<?php
// src/siswa/dashboard.php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'siswa') {
    header("Location: ../../login.php");
    exit;
}

$student_id = $_SESSION['user_id'];

$hour = (int)date('H');
if ($hour < 11)
    $greeting = "Selamat Pagi";
elseif ($hour < 15)
    $greeting = "Selamat Siang";
elseif ($hour < 18)
    $greeting = "Selamat Sore";
else
    $greeting = "Selamat Malam";

// Get Student's Class
$stmt = $pdo->prepare("SELECT class_id FROM users WHERE id = ?");
$stmt->execute([$student_id]);
$user_data = $stmt->fetch();
$class_id = $user_data['class_id'];

// Stats
$stmt = $pdo->prepare("SELECT COUNT(*) FROM materials WHERE class_id = ? OR class_id IS NULL");
$stmt->execute([$class_id]);
$total_materials = $stmt->fetchColumn();

// Pending Tasks (Assignments for this class that are NOT submitted, regardless of deadline)
$pending_tasks = 0;
if ($class_id) {
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM assignments a
        JOIN assignment_classes ac ON a.id = ac.assignment_id
        WHERE ac.class_id = ? 
        AND a.status = 'active'
        AND NOT EXISTS (
            SELECT 1 FROM submissions s 
            WHERE s.assignment_id = a.id AND s.student_id = ?
        )
    ");
    $stmt->execute([$class_id, $student_id]);
    $pending_tasks = $stmt->fetchColumn();
}

$stmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM submissions s 
    JOIN assignments a ON s.assignment_id = a.id
    JOIN assignment_classes ac ON a.id = ac.assignment_id
    WHERE s.student_id = ? AND ac.class_id = ?
");
$stmt->execute([$student_id, $class_id]);
$completed_tasks = $stmt->fetchColumn();

// Recent Materials (Filtered by Class)
$stmt = $pdo->prepare("
    SELECT m.title, m.type, m.file_path, m.created_at, u.full_name as teacher_name
    FROM materials m
    JOIN users u ON m.teacher_id = u.id
    WHERE (m.class_id = ? OR m.class_id IS NULL)
    ORDER BY m.created_at DESC LIMIT 5
");
$stmt->execute([$class_id]);
$recent_materials = $stmt->fetchAll();

// Upcoming / Pending Assignments (Belum dikerjakan)
// Show ALL pending tasks, even if deadline passed (so they know they missed it)
$upcoming_assignments = [];
if ($class_id) {
    $stmt = $pdo->prepare("
        SELECT a.title, a.deadline, u.full_name as teacher_name
        FROM assignments a
        JOIN assignment_classes ac ON a.id = ac.assignment_id
        JOIN users u ON a.teacher_id = u.id
        WHERE ac.class_id = ?
        AND a.status = 'active'
        AND NOT EXISTS (
            SELECT 1 FROM submissions s WHERE s.assignment_id = a.id AND s.student_id = ?
        )
        ORDER BY a.deadline ASC LIMIT 5
    ");
    $stmt->execute([$class_id, $student_id]);
    $upcoming_assignments = $stmt->fetchAll();
}

// Completed assignments (sudah dikerjakan)
// Completed assignments (sudah dikerjakan) - Scoped to current class
$stmt = $pdo->prepare("
    SELECT a.title, s.submitted_at, s.grade, u.full_name as teacher_name
    FROM submissions s
    JOIN assignments a ON s.assignment_id = a.id
    JOIN assignment_classes ac ON a.id = ac.assignment_id
    JOIN users u ON a.teacher_id = u.id
    WHERE s.student_id = ? AND ac.class_id = ?
    ORDER BY s.submitted_at DESC LIMIT 5
");
$stmt->execute([$student_id, $class_id]);
$done_assignments = $stmt->fetchAll();

// Indonesian Date
$days = ['Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'];
$months = ['January' => 'Januari', 'February' => 'Februari', 'March' => 'Maret', 'April' => 'April', 'May' => 'Mei', 'June' => 'Juni', 'July' => 'Juli', 'August' => 'Agustus', 'September' => 'September', 'October' => 'Oktober', 'November' => 'November', 'December' => 'Desember'];
$dayName = $days[date('l')] ?? date('l');
$monthName = $months[date('F')] ?? date('F');
$dateStr = $dayName . ', ' . date('d') . ' ' . $monthName . ' ' . date('Y');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Siswa</title>
    <link rel="stylesheet" href="/public/assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* * { font-family: 'Inter', system-ui, -apple-system, sans-serif; } */
        .main-content {
            max-width: 100% !important;
            background: #f5f7fb !important;
            padding: 0 !important;
        }

        /* Hero siswa: teal / emerald (beda dari biru guru & slate admin) */
        .db-hero {
            position: relative;
            background: linear-gradient(135deg, #064e3b 0%, #047857 38%, #0d9488 100%);
            padding: 2.5rem 3rem 5.5rem 5rem;
            overflow: hidden;
        }
        .db-hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            pointer-events: none;
        }
        .db-hero::after {
            content: '';
            position: absolute;
            width: 500px; height: 500px;
            top: -250px; right: -100px;
            background: radial-gradient(circle, rgba(52, 211, 153, 0.28) 0%, transparent 60%);
            border-radius: 50%;
            pointer-events: none;
        }
        .hero-inner {
            position: relative; z-index: 2;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .hero-inner h1 {
            font-size: 1.65rem;
            font-weight: 800;
            color: #fff;
            letter-spacing: -0.03em;
            margin-bottom: 0.35rem;
        }
        .hero-sub { color: rgba(255,255,255,0.55); font-size: 0.88rem; }
        .hero-date {
            color: rgba(255,255,255,0.45);
            font-size: 0.85rem;
            text-align: right;
            font-weight: 500;
        }

        .db-content {
            position: relative;
            margin-top: -3rem;
            padding: 0 3rem 3rem;
            z-index: 10;
        }

        /* Stats */
        .db-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            margin-bottom: 20px;
        }
        .db-stat {
            background: #fff;
            border-radius: 16px;
            padding: 22px 24px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04), 0 6px 24px rgba(0,0,0,0.04);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            animation: fade-up 0.4s ease-out both;
        }
        .db-stat:nth-child(1) { animation-delay: 0.05s; }
        .db-stat:nth-child(2) { animation-delay: 0.1s; }
        .db-stat:nth-child(3) { animation-delay: 0.15s; }
        @keyframes fade-up {
            from { opacity: 0; transform: translateY(16px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .db-stat::after {
            content: '';
            position: absolute;
            bottom: 0; left: 0;
            width: 100%; height: 3px;
        }
        .db-stat.c-red::after   { background: linear-gradient(90deg, #ef4444, #fca5a5); }
        .db-stat.c-green::after { background: linear-gradient(90deg, #10b981, #6ee7b7); }
        .db-stat.c-blue::after  { background: linear-gradient(90deg, #3b82f6, #93c5fd); }
        .db-stat:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 30px rgba(0,0,0,0.08);
        }
        .db-stat .num {
            font-size: 2.2rem;
            font-weight: 900;
            line-height: 1;
            margin-bottom: 6px;
            letter-spacing: -0.03em;
        }
        .db-stat.c-red .num   { color: #dc2626; }
        .db-stat.c-green .num { color: #059669; }
        .db-stat.c-blue .num  { color: #2563eb; }
        .db-stat .lbl {
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #94a3b8;
        }

        /* Two-column grid */
        .db-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
            animation: fade-up 0.4s ease-out 0.25s both;
        }

        /* Full-width panel */
        .db-full {
            animation: fade-up 0.4s ease-out 0.35s both;
        }

        .db-panel {
            background: #fff;
            border-radius: 18px;
            padding: 26px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04), 0 6px 24px rgba(0,0,0,0.04);
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        .db-panel h3 {
            font-size: 0.82rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #94a3b8;
            margin-bottom: 18px;
            padding-bottom: 14px;
            border-bottom: 1px solid #f1f5f9;
        }

        /* Material rows */
        .mat-row {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 12px 0;
            border-bottom: 1px solid #f1f5f9;
            text-decoration: none;
            color: inherit;
            transition: all 0.2s;
            cursor: pointer;
        }
        .mat-row:last-child { border-bottom: none; }
        .mat-row:hover { padding-left: 6px; }
        .mat-ico {
            width: 38px; height: 38px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            flex-shrink: 0;
        }
        .mat-ico.pdf  { background: #fee2e2; }
        .mat-ico.video { background: #dbeafe; }
        .mat-ico.ppt  { background: #fef3c7; }
        .mat-ico.link { background: #d1fae5; }
        .mat-ico.epub { background: #fce7f3; }
        .mat-ico.doc  { background: #ede9fe; }
        .mat-info { flex: 1; min-width: 0; }
        .mat-title {
            font-size: 0.85rem;
            font-weight: 600;
            color: #1e293b;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .mat-meta { font-size: 0.73rem; color: #94a3b8; margin-top: 2px; }

        /* Assignment rows */
        .asgn-row {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 12px 0;
            border-bottom: 1px solid #f1f5f9;
        }
        .asgn-row:last-child { border-bottom: none; }
        .asgn-dot {
            width: 10px; height: 10px;
            border-radius: 50%;
            flex-shrink: 0;
        }
        .asgn-info { flex: 1; min-width: 0; }
        .asgn-title {
            font-size: 0.85rem;
            font-weight: 600;
            color: #1e293b;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .asgn-meta { font-size: 0.73rem; color: #94a3b8; margin-top: 2px; }
        .asgn-badge {
            font-size: 0.7rem;
            font-weight: 700;
            padding: 4px 10px;
            border-radius: 6px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            white-space: nowrap;
        }
        .badge-done   { background: #d1fae5; color: #065f46; }
        .badge-graded { background: #dbeafe; color: #1e40af; }
        .badge-pending { background: #fef3c7; color: #92400e; }

        .empty-state {
            text-align: center;
            padding: 2rem 1rem;
            color: #94a3b8;
            font-size: 0.88rem;
        }

        @media (max-width: 900px) {
            .db-stats { grid-template-columns: 1fr; }
            .db-grid { grid-template-columns: 1fr; }
            .db-hero { padding: 2rem 1.5rem 5rem; }
            .db-content { padding: 0 1.5rem 2rem; }
        }

        @media (max-width: 768px) {
            .db-hero {
                padding: 1.2rem 1rem 3.4rem;
                border-bottom-right-radius: 24px;
            }
            .hero-inner {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.55rem;
            }
            .hero-inner h1 {
                font-size: 1.15rem;
                line-height: 1.35;
                margin-bottom: 0.2rem;
            }
            .hero-sub {
                font-size: 0.78rem;
            }
            .hero-date {
                text-align: left;
                font-size: 0.72rem;
                opacity: 0.9;
            }

            .db-content {
                margin-top: -2rem;
                padding: 0 0.85rem 1rem;
            }
            .db-stats {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 0.65rem;
                margin-bottom: 0.8rem;
            }
            .db-stat {
                border-radius: 12px;
                padding: 0.85rem 0.75rem;
            }
            .db-stat .num {
                font-size: 1.35rem;
                margin-bottom: 0.3rem;
            }
            .db-stat .lbl {
                font-size: 0.62rem;
                letter-spacing: 0.05em;
            }

            .db-grid {
                gap: 0.75rem;
                margin-bottom: 0.75rem;
            }
            .db-panel {
                border-radius: 12px;
                padding: 0.85rem;
            }
            .db-panel h3 {
                font-size: 0.68rem;
                margin-bottom: 0.65rem;
                padding-bottom: 0.55rem;
            }

            .mat-row,
            .asgn-row {
                gap: 0.55rem;
                padding: 0.55rem 0;
                align-items: flex-start;
            }
            .mat-ico {
                width: 30px;
                height: 30px;
                border-radius: 8px;
            }
            .mat-title,
            .asgn-title {
                font-size: 0.72rem;
                white-space: normal;
                line-height: 1.3;
            }
            .mat-meta,
            .asgn-meta {
                font-size: 0.62rem;
                line-height: 1.35;
            }
            .asgn-badge {
                font-size: 0.58rem;
                padding: 0.25rem 0.45rem;
                border-radius: 5px;
                align-self: flex-start;
            }
            .empty-state {
                padding: 1rem 0.6rem;
                font-size: 0.76rem;
            }
        }
    </style>
</head>
<body>

<div class="app-container">
    <?php include '../templates/sidebar.php'; ?>
    
    <main class="main-content">

        <div class="db-hero">
            <div class="hero-inner">
                <div>
                    <h1><?php echo $greeting; ?>, <?php echo htmlspecialchars($_SESSION['full_name']); ?>!</h1>
                    <?php if (!empty($_SESSION['nis'])): ?>
                        <div style="color: rgba(255,255,255,0.7); font-size: 0.9rem; margin-bottom: 4px; font-weight: 500;">NIS: <?php echo htmlspecialchars($_SESSION['nis']); ?></div>
                    <?php
endif; ?>
                    <p class="hero-sub">Ruang belajar dan aktivitas akademik kamu</p>
                </div>
                <div class="hero-date"><?php echo $dateStr; ?></div>
            </div>
        </div>

        <div class="db-content">

            <!-- Stats -->
            <div class="db-stats">
                <div class="db-stat c-red">
                    <div class="num"><?php echo $pending_tasks; ?></div>
                    <div class="lbl">Tugas Belum Selesai</div>
                </div>
                <div class="db-stat c-green">
                    <div class="num"><?php echo $completed_tasks; ?></div>
                    <div class="lbl">Sudah Dikumpulkan</div>
                </div>
                <div class="db-stat c-blue">
                    <div class="num"><?php echo $total_materials; ?></div>
                    <div class="lbl">Total Materi</div>
                </div>
            </div>

            <!-- Two-column: Materi Terbaru + Tugas Mendatang -->
            <div class="db-grid">

                <!-- Materi Terbaru -->
                <div class="db-panel">
                    <h3>Materi Terbaru</h3>
                    <?php if (empty($recent_materials)): ?>
                        <div class="empty-state">
                            <p style="font-size:2rem; margin-bottom:8px;"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block; vertical-align:middle; line-height:1;"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/></svg></p>
                            <p>Belum ada materi tersedia.</p>
                        </div>
                    <?php
else: ?>
                        <?php foreach ($recent_materials as $m):
        $typeIcon = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block; vertical-align:middle; line-height:1;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>';
        $typeClass = 'doc';
        if ($m['type'] === 'pdf') {
            $typeIcon = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block; vertical-align:middle; line-height:1;"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/></svg>';
            $typeClass = 'pdf';
        }
        elseif ($m['type'] === 'video') {
            $typeIcon = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block; vertical-align:middle; line-height:1;"><polygon points="23 7 16 12 23 17 23 7"/><rect x="1" y="5" width="15" height="14" rx="2" ry="2"/></svg>';
            $typeClass = 'video';
        }
        elseif ($m['type'] === 'ppt') {
            $typeIcon = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block; vertical-align:middle; line-height:1;"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>';
            $typeClass = 'ppt';
        }
        elseif ($m['type'] === 'epub') {
            $typeIcon = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block; vertical-align:middle; line-height:1;"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>';
            $typeClass = 'epub';
        }
        elseif ($m['type'] === 'link') {
            $typeIcon = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block; vertical-align:middle; line-height:1;"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>';
            $typeClass = 'link';
        }
?>
                        <?php
        $href = $m['file_path'];
        if ($m['type'] !== 'link' && strpos($href, 'http') !== 0) {
            $href = '/' . $href;
        }
?>
                        <a href="<?php echo htmlspecialchars($href); ?>" target="_blank" class="mat-row">
                            <div class="mat-ico <?php echo $typeClass; ?>"><?php echo $typeIcon; ?></div>
                            <div class="mat-info">
                                <div class="mat-title"><?php echo htmlspecialchars($m['title']); ?></div>
                                <div class="mat-meta"><?php echo htmlspecialchars($m['teacher_name']); ?> Â· <?php echo date('d M Y', strtotime($m['created_at'])); ?></div>
                            </div>
                        </a>
                        <?php
    endforeach; ?>
                    <?php
endif; ?>
                </div>

                <!-- Tugas Mendatang -->
                <div class="db-panel">
                    <h3>Tugas Mendatang</h3>
                    <?php if (empty($upcoming_assignments)): ?>
                        <div class="empty-state">
                            <p style="font-size:2rem; margin-bottom:8px;"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block; vertical-align:middle; line-height:1;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg></p>
                            <p>Tidak ada tugas mendatang!</p>
                        </div>
                    <?php
else: ?>
                        <?php foreach ($upcoming_assignments as $a): ?>
                        <div class="asgn-row">
                            <div class="asgn-dot" style="background: #f59e0b;"></div>
                            <div class="asgn-info">
                                <div class="asgn-title"><?php echo htmlspecialchars($a['title']); ?></div>
                                <div class="asgn-meta">Deadline: <?php echo date('d M Y, H:i', strtotime($a['deadline'])); ?></div>
                            </div>
                            <span class="asgn-badge badge-pending">Belum</span>
                        </div>
                        <?php
    endforeach; ?>
                    <?php
endif; ?>
                </div>
            </div>

            <!-- Full-width: Tugas yang telah dikerjakan -->
            <div class="db-full">
                <div class="db-panel">
                    <h3>Tugas yang Telah Dikerjakan</h3>
                    <?php if (empty($done_assignments)): ?>
                        <div class="empty-state">
                            <p style="font-size:2rem; margin-bottom:8px;"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block; vertical-align:middle; line-height:1;"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg></p>
                            <p>Belum ada tugas yang dikumpulkan.</p>
                        </div>
                    <?php
else: ?>
                        <?php foreach ($done_assignments as $d): ?>
                        <div class="asgn-row">
                            <div class="asgn-dot" style="background: #10b981;"></div>
                            <div class="asgn-info">
                                <div class="asgn-title"><?php echo htmlspecialchars($d['title']); ?></div>
                                <div class="asgn-meta">Dikumpulkan: <?php echo date('d M Y, H:i', strtotime($d['submitted_at'])); ?> Â· <?php echo htmlspecialchars($d['teacher_name']); ?></div>
                            </div>
                            <?php if ($d['grade'] !== null): ?>
                                <span class="asgn-badge badge-graded">Nilai: <?php echo $d['grade']; ?></span>
                            <?php
        else: ?>
                                <span class="asgn-badge badge-done">Selesai</span>
                            <?php
        endif; ?>
                        </div>
                        <?php
    endforeach; ?>
                    <?php
endif; ?>
                </div>
            </div>

        </div>
    </main>
</div>

</body>
</html>

