<?php
// keaktifan_siswa.php
session_start();
require_once 'config/database.php';

// Cek login
if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_after_login'] = '/keaktifan_siswa.php';
    header("Location: login.php?error=Silakan login terlebih dahulu.");
    exit;
}

// Hanya siswa
if ($_SESSION['role'] !== 'siswa') {
    session_destroy();
    header("Location: login.php?portal=nilai&error=Akses Ditolak! Portal ini hanya untuk Siswa.");
    exit;
}

$student_id   = $_SESSION['user_id'];
$student_name = $_SESSION['full_name'] ?? 'Siswa';
$student_nis  = $_SESSION['nis'] ?? '-';

// Ambil nama kelas & class_id
$class_name = '-';
$student_class_id = null;
try {
    $s = $pdo->prepare("SELECT u.class_id, c.name FROM users u LEFT JOIN classes c ON u.class_id = c.id WHERE u.id = ?");
    $s->execute([$student_id]);
    $r = $s->fetch();
    if ($r) {
        $student_class_id = $r['class_id'];
        if (!empty($r['name'])) $class_name = $r['name'];
    }
} catch (PDOException $e) {}

// ── Query SEMUA tugas yang ditujukan ke kelas siswa ──
// Termasuk yang belum dikumpulkan dan belum dinilai
try {
    $stmt = $pdo->prepare("
        SELECT
            s.name          AS subject_name,
            s.id            AS subject_id,
            a.id            AS assignment_id,
            a.title         AS assignment_title,
            a.deadline,
            sub.id          AS submission_id,
            sub.grade,
            sub.feedback,
            sub.submitted_at,
            sub.status      AS submission_status,
            u.full_name     AS teacher_name
        FROM assignments a
        JOIN assignment_classes ac  ON a.id              = ac.assignment_id
        JOIN users u                ON a.teacher_id      = u.id
        LEFT JOIN teacher_classes tc ON a.teacher_class_id = tc.id
        LEFT JOIN subjects s        ON tc.subject_id     = s.id
        LEFT JOIN submissions sub   ON sub.assignment_id  = a.id
                                   AND sub.student_id     = ?
                                   AND sub.is_archived    = 0
        WHERE ac.class_id           = ?
          AND a.assignment_type     = 'tugas'
          AND a.status              = 'active'
        ORDER BY subject_name ASC, a.deadline DESC
    ");
    $stmt->execute([$student_id, $student_class_id]);
    $rows = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Keaktifan Siswa Error: " . $e->getMessage());
    $rows = [];
}

// Kumpulkan daftar mapel unik (untuk filter)
$subject_list = [];
foreach ($rows as $row) {
    $sn = $row['subject_name'];
    $sid = $row['subject_id'];
    if ($sn && !isset($subject_list[$sid])) {
        $subject_list[$sid] = $sn;
    }
}
asort($subject_list);

// Filter berdasarkan mapel (jika ada)
$filter_subject = $_GET['mapel'] ?? 'all';
if ($filter_subject !== 'all') {
    $rows = array_filter($rows, function($r) use ($filter_subject) {
        return (string)$r['subject_id'] === (string)$filter_subject;
    });
}

// Kelompokkan per mata pelajaran
$grouped = [];
foreach ($rows as $row) {
    $grouped[$row['subject_name']][] = $row;
}

// Hitung ringkasan
$total_tugas    = count($rows);
$graded_rows    = array_filter($rows, fn($r) => $r['grade'] !== null);
$total_dinilai  = count($graded_rows);
$total_mapel    = count($grouped);
$avg_nilai      = $total_dinilai > 0
    ? array_sum(array_column($graded_rows, 'grade')) / $total_dinilai
    : 0;

// Status helper
function getStatus($row) {
    if ($row['submission_id'] === null) {
        // Belum mengumpulkan
        $past_deadline = $row['deadline'] && strtotime($row['deadline']) < time();
        if ($past_deadline) {
            return ['label' => 'Tidak Dikumpulkan', 'color' => '#b91c1c', 'bg' => '#fee2e2', 'icon' => '✕'];
        }
        return ['label' => 'Belum Dikumpulkan', 'color' => '#b45309', 'bg' => '#fef3c7', 'icon' => '⏳'];
    }
    if ($row['grade'] === null) {
        return ['label' => 'Menunggu Penilaian', 'color' => '#1d4ed8', 'bg' => '#dbeafe', 'icon' => '⏳'];
    }
    return ['label' => 'Dinilai', 'color' => '#15803d', 'bg' => '#dcfce7', 'icon' => '✓'];
}

function getPredikat($grade) {
    if ($grade >= 90) return ['label' => 'A', 'color' => '#15803d', 'bg' => '#dcfce7'];
    if ($grade >= 80) return ['label' => 'B', 'color' => '#1d4ed8', 'bg' => '#dbeafe'];
    if ($grade >= 70) return ['label' => 'C', 'color' => '#b45309', 'bg' => '#fef3c7'];
    return ['label' => 'D', 'color' => '#b91c1c', 'bg' => '#fee2e2'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keaktifan Siswa – <?php echo htmlspecialchars($student_name); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary:      #1e3a8a;
            --primary-light:#2563eb;
            --accent:       #6366f1;
            --bg:           #f0f4ff;
            --surface:      #ffffff;
            --text-dark:    #1e293b;
            --text-muted:   #64748b;
            --border:       #e2e8f0;
            --radius:       14px;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--bg);
            color: var(--text-dark);
            min-height: 100vh;
        }

        /* ── Top Bar ── */
        .top-bar {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            color: white;
            padding: 0 2rem;
            height: 64px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 20px rgba(30,58,138,0.3);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .top-bar .brand {
            display: flex; align-items: center; gap: 0.6rem;
            font-weight: 700; font-size: 1rem; letter-spacing: 0.3px;
        }
        .top-bar .brand svg { opacity: 0.9; }
        .top-nav { display: flex; align-items: center; gap: 0.6rem; }
        .btn-nav {
            display: inline-flex; align-items: center; gap: 0.4rem;
            padding: 0.45rem 1rem; border-radius: 8px;
            font-size: 0.85rem; font-weight: 600; text-decoration: none;
            border: none; cursor: pointer; transition: all 0.2s; font-family: inherit;
        }
        .btn-nav-ghost { background: rgba(255,255,255,0.12); color: white; border: 1px solid rgba(255,255,255,0.2); }
        .btn-nav-ghost:hover { background: rgba(255,255,255,0.22); }
        .btn-nav-danger { background: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5; }
        .btn-nav-danger:hover { background: #fecaca; }

        /* ── Hero ── */
        .hero {
            background: linear-gradient(135deg, var(--primary) 0%, #3730a3 100%);
            padding: 2.5rem 2rem 4rem;
            color: white;
            position: relative; overflow: hidden;
        }
        .hero::before { content:''; position:absolute; top:-60px; right:-60px; width:220px; height:220px; background:rgba(255,255,255,0.06); border-radius:50%; }
        .hero::after { content:''; position:absolute; bottom:-40px; left:30%; width:140px; height:140px; background:rgba(255,255,255,0.04); border-radius:50%; }
        .hero-inner { max-width: 900px; margin: 0 auto; position: relative; z-index: 1; }
        .hero-tag {
            display:inline-flex; align-items:center; gap:6px;
            background:rgba(255,255,255,0.15); backdrop-filter:blur(6px);
            border:1px solid rgba(255,255,255,0.2);
            padding:4px 12px; border-radius:20px;
            font-size:0.78rem; font-weight:600; letter-spacing:0.5px; text-transform:uppercase; margin-bottom:1rem;
        }
        .hero h1 { font-size: 1.9rem; font-weight: 800; margin-bottom: 0.4rem; }
        .hero-sub { color: rgba(255,255,255,0.8); font-size: 0.95rem; }
        .hero-meta { display:flex; gap:1.5rem; margin-top:1.2rem; font-size:0.85rem; color:rgba(255,255,255,0.75); flex-wrap:wrap; }
        .hero-meta span { display:flex; align-items:center; gap:5px; }

        /* ── Content ── */
        .content { max-width:960px; margin:-2rem auto 3rem; padding:0 1.5rem; position:relative; z-index:2; }

        /* ── Stats ── */
        .stats-row { display:grid; grid-template-columns:repeat(4,1fr); gap:1rem; margin-bottom:1.5rem; }
        .stat-card {
            background:var(--surface); border-radius:var(--radius);
            padding:1.15rem 1.25rem; box-shadow:0 2px 12px rgba(0,0,0,0.06);
            display:flex; align-items:center; gap:0.85rem;
        }
        .stat-icon { width:44px; height:44px; border-radius:11px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
        .stat-label { font-size:0.7rem; color:var(--text-muted); font-weight:600; text-transform:uppercase; letter-spacing:0.05em; margin-bottom:2px; }
        .stat-value { font-size:1.5rem; font-weight:800; color:var(--text-dark); line-height:1; }

        /* ── Filter Bar ── */
        .filter-bar {
            background: var(--surface);
            border-radius: var(--radius);
            padding: 1.15rem 1.5rem;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
        }
        .filter-bar label {
            font-size: 0.82rem;
            font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.04em;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .filter-dropdown {
            padding: 0.6rem 2.5rem 0.6rem 1rem;
            border-radius: 8px;
            font-size: 0.88rem;
            font-weight: 600;
            color: var(--text-dark);
            border: 1px solid var(--border);
            background: #f8fafc;
            cursor: pointer;
            font-family: inherit;
            min-width: 200px;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 0.7rem center;
            background-size: 16px;
        }
        .filter-dropdown:hover {
            border-color: #c7d2fe;
            background-color: #eef2ff;
        }
        .filter-dropdown:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(99,102,241,0.15);
        }

        /* ── Section Card ── */
        .section-card { background:var(--surface); border-radius:var(--radius); box-shadow:0 2px 12px rgba(0,0,0,0.06); overflow:hidden; }
        .section-header { padding:1.25rem 1.5rem; border-bottom:1px solid var(--border); display:flex; align-items:center; gap:0.75rem; }
        .section-header h2 { font-size:1rem; font-weight:700; color:var(--text-dark); }
        .section-header p { font-size:0.82rem; color:var(--text-muted); margin-top:1px; }

        /* ── Table ── */
        .table-wrapper { overflow-x:auto; -webkit-overflow-scrolling:touch; }
        table.activity-table { width:100%; border-collapse:collapse; font-size:0.875rem; }
        .activity-table th {
            background:#f8fafc; color:var(--text-muted); font-size:0.72rem; font-weight:700;
            text-transform:uppercase; letter-spacing:0.05em;
            padding:0.7rem 1.25rem; text-align:left; border-bottom:1px solid var(--border); white-space:nowrap;
        }
        .activity-table td { padding:0.7rem 1.25rem; border-bottom:1px solid #f1f5f9; vertical-align:middle; color:var(--text-dark); }
        .activity-table tbody tr:last-child td { border-bottom:none; }
        .activity-table tbody tr:hover td { background:#fafbff; }

        /* Mapel divider row */
        .mapel-row td {
            background:linear-gradient(90deg,#eef2ff 0%,#f0f9ff 100%);
            padding:0.6rem 1.25rem; border-bottom:1px solid #dbeafe; border-top:2px solid #c7d2fe;
        }
        .mapel-name { font-weight:700; font-size:0.875rem; color:#3730a3; display:flex; align-items:center; gap:6px; }
        .mapel-teacher { font-size:0.75rem; color:#6366f1; font-weight:500; margin-top:2px; }

        /* Badge */
        .badge {
            display:inline-flex; align-items:center; justify-content:center;
            width:32px; height:32px; border-radius:8px; font-size:0.9rem; font-weight:800;
        }

        /* Status pill */
        .status-pill {
            display:inline-flex; align-items:center; gap:4px;
            padding:3px 10px; border-radius:20px;
            font-size:0.75rem; font-weight:700; white-space:nowrap;
        }

        /* Nilai */
        .nilai-cell { font-size:1.05rem; font-weight:800; color:var(--text-dark); font-variant-numeric:tabular-nums; }

        /* No data */
        .empty-state { padding:4rem 2rem; text-align:center; color:var(--text-muted); }
        .empty-state svg { margin-bottom:1rem; opacity:0.3; }
        .empty-state h3 { font-size:1rem; font-weight:700; margin-bottom:0.4rem; color:var(--text-dark); }
        .empty-state p { font-size:0.875rem; }

        /* Legend */
        .legend { padding:0.75rem 1.25rem; font-size:0.75rem; color:var(--text-muted); border-top:1px solid var(--border); background:#fafbff; }

        /* Responsive */
        @media (max-width: 768px) {
            .top-bar { padding: 0 1rem; }
            .hero { padding: 2rem 1rem 3rem; }
            .hero h1 { font-size: 1.4rem; }
            .content { padding: 0 0.75rem; }
            .stats-row { grid-template-columns: repeat(2, 1fr); }
            .filter-bar { padding: 0.75rem 1rem; }
            /* Membiarkan label / teks deskriptif icon terbaca */
        }
        @media (max-width: 480px) {
            .stats-row { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<!-- Top Bar -->
<div class="top-bar">
    <div class="brand">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        Keaktifan Siswa
    </div>
    <div class="top-nav">
        <a href="/pantauan_nilai.php" class="btn-nav btn-nav-ghost">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            <span>Laporan KHS</span>
        </a>
        <a href="/src/auth/logout.php" class="btn-nav btn-nav-danger" onclick="return confirm('Keluar dari sistem?');">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
            <span>Keluar</span>
        </a>
    </div>
</div>

<!-- Hero -->
<div class="hero">
    <div class="hero-inner">
        <div class="hero-tag">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
            Portal Pantauan Nilai
        </div>
        <h1>Keaktifan Siswa</h1>
        <p class="hero-sub">Rekap seluruh tugas dan nilai dari guru per mata pelajaran</p>
        <div class="hero-meta">
            <span>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                <?php echo htmlspecialchars($student_name); ?>
            </span>
            <span>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg>
                <?php echo htmlspecialchars($class_name); ?>
            </span>
            <span>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="8" rx="2"/><rect x="2" y="14" width="20" height="8" rx="2"/><line x1="6" y1="6" x2="6.01" y2="6"/><line x1="6" y1="18" x2="6.01" y2="18"/></svg>
                NIS: <?php echo htmlspecialchars($student_nis); ?>
            </span>
        </div>
    </div>
</div>

<!-- Content -->
<div class="content">

    <!-- Stats Row -->
    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-icon" style="background:#eef2ff;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#4f46e5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
            </div>
            <div>
                <div class="stat-label">Total Tugas</div>
                <div class="stat-value"><?php echo $total_tugas; ?></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:#dcfce7;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#15803d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
            </div>
            <div>
                <div class="stat-label">Sudah Dinilai</div>
                <div class="stat-value" style="color:#15803d;"><?php echo $total_dinilai; ?></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:#ecfdf5;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
            </div>
            <div>
                <div class="stat-label">Mata Pelajaran</div>
                <div class="stat-value"><?php echo $total_mapel; ?></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:#fff7ed;">
                <?php $avg_info = getPredikat($avg_nilai); ?>
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
            </div>
            <div>
                <div class="stat-label">Rata-rata</div>
                <div class="stat-value" style="color:<?php echo $avg_info['color']; ?>;">
                    <?php echo $total_dinilai > 0 ? number_format($avg_nilai, 1) : '—'; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="filter-bar">
        <label for="filterMapel">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
            Filter Mapel:
        </label>
        <select id="filterMapel" class="filter-dropdown" onchange="window.location.href='?mapel='+this.value">
            <option value="all" <?php echo $filter_subject === 'all' ? 'selected' : ''; ?>>Semua Mata Pelajaran</option>
            <?php foreach ($subject_list as $sid => $sname): ?>
                <option value="<?php echo htmlspecialchars($sid); ?>" <?php echo (string)$filter_subject === (string)$sid ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($sname); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <!-- Table Card -->
    <div class="section-card">
        <div class="section-header">
            <div style="width:38px;height:38px;border-radius:10px;background:#eef2ff;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#4f46e5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
            </div>
            <div>
                <h2>Rekap Tugas Per Mata Pelajaran</h2>
                <p>Hanya menampilkan mata pelajaran yang sudah memiliki kelas mapel aktif</p>
            </div>
        </div>

        <?php if (empty($grouped)): ?>
        <div class="empty-state">
            <svg width="52" height="52" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
            <h3>Belum ada tugas</h3>
            <p>Belum ada tugas yang diterbitkan oleh guru untuk kelas Anda.</p>
        </div>
        <?php else: ?>
        <div class="table-wrapper">
            <table class="activity-table">
                <thead>
                    <tr>
                        <th style="width:40px; text-align:center;">No</th>
                        <th>Judul Tugas</th>
                        <th style="width:130px; text-align:center;">Status</th>
                        <th style="width:70px; text-align:center;">Nilai</th>
                        <th style="width:70px; text-align:center;">Predikat</th>
                        <th style="min-width:140px;">Feedback Guru</th>
                        <th style="width:95px; text-align:center;">Deadline</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $no = 1;
                foreach ($grouped as $mapel => $tasks):
                ?>
                    <tr class="mapel-row">
                        <td colspan="7">
                            <div class="mapel-name">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#4f46e5" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                                <?php echo htmlspecialchars($mapel); ?>
                                <span style="font-weight:400; font-size:0.75rem; color:#94a3b8;">(<?php echo count($tasks); ?> tugas)</span>
                            </div>
                            <div class="mapel-teacher"><?php echo htmlspecialchars($tasks[0]['teacher_name']); ?></div>
                        </td>
                    </tr>
                    <?php foreach ($tasks as $task):
                        $status = getStatus($task);
                        $has_grade = $task['grade'] !== null;
                        $pred = $has_grade ? getPredikat($task['grade']) : null;
                    ?>
                    <tr>
                        <td style="text-align:center; color:#94a3b8; font-size:0.8rem;"><?php echo $no++; ?></td>
                        <td style="font-weight:600;"><?php echo htmlspecialchars($task['assignment_title']); ?></td>
                        <td style="text-align:center;">
                            <span class="status-pill" style="background:<?php echo $status['bg']; ?>; color:<?php echo $status['color']; ?>;">
                                <?php echo $status['label']; ?>
                            </span>
                        </td>
                        <td style="text-align:center;" class="nilai-cell">
                            <?php if ($has_grade): ?>
                                <?php echo number_format($task['grade'], 0); ?>
                            <?php else: ?>
                                <span style="color:#cbd5e1;">—</span>
                            <?php endif; ?>
                        </td>
                        <td style="text-align:center;">
                            <?php if ($has_grade && $pred): ?>
                                <span class="badge" style="background:<?php echo $pred['bg']; ?>; color:<?php echo $pred['color']; ?>;">
                                    <?php echo $pred['label']; ?>
                                </span>
                            <?php else: ?>
                                <span style="color:#cbd5e1;">—</span>
                            <?php endif; ?>
                        </td>
                        <td style="font-size:0.8rem; color:var(--text-muted);">
                            <?php echo !empty($task['feedback'])
                                ? htmlspecialchars($task['feedback'])
                                : '<span style="color:#cbd5e1;">—</span>'; ?>
                        </td>
                        <td style="text-align:center; font-size:0.78rem; color:var(--text-muted);">
                            <?php echo $task['deadline'] ? date('d/m/Y', strtotime($task['deadline'])) : '—'; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="legend">
            <strong>Predikat:</strong> A ≥ 90 &nbsp;|&nbsp; B = 80–89 &nbsp;|&nbsp; C = 70–79 &nbsp;|&nbsp; D &lt; 70
            &nbsp;&nbsp;•&nbsp;&nbsp;
            <strong>Status:</strong>
            <span style="color:#15803d;">✓ Dinilai</span> &nbsp;|&nbsp;
            <span style="color:#1d4ed8;">⏳ Menunggu Penilaian</span> &nbsp;|&nbsp;
            <span style="color:#b45309;">⏳ Belum Dikumpulkan</span> &nbsp;|&nbsp;
            <span style="color:#b91c1c;">✕ Tidak Dikumpulkan</span>
        </div>
        <?php endif; ?>
    </div>

</div><!-- /content -->

</body>
</html>
