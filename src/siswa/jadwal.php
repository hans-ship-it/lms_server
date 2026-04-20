<?php
// src/siswa/jadwal.php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'siswa') {
    header("Location: ../../login.php");
    exit;
}

$siswa_id = $_SESSION['user_id'];

$stmt_user = $pdo->prepare("SELECT class_id FROM users WHERE id = ?");
$stmt_user->execute([$siswa_id]);
$user = $stmt_user->fetch();
$class_id = $user['class_id'] ?? null;

$class_name = "Belum Terdaftar di Kelas";
$schedules = [];

$search_query = isset($_GET['q']) ? trim($_GET['q']) : '';

if ($class_id) {
    $stmt_class = $pdo->prepare("SELECT name FROM classes WHERE id = ?");
    $stmt_class->execute([$class_id]);
    $class_info = $stmt_class->fetch();

    if ($class_info) {
        $class_name = $class_info['name'];

        $sql = "
            SELECT * FROM schedules 
            WHERE REPLACE(REPLACE(LOWER(kelas), ' ', ''), '-', '') = REPLACE(REPLACE(LOWER(?), ' ', ''), '-', '')
        ";
        $params = [$class_name];

        if (!empty($search_query)) {
            $sql .= " AND LOWER(mata_pelajaran) LIKE LOWER(?)";
            $params[] = '%' . $search_query . '%';
        }

        $sql .= "
            ORDER BY
            FIELD(hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'),
            CAST(jam_ke AS UNSIGNED) ASC, id ASC
        ";

        $stmt_sched = $pdo->prepare($sql);
        $stmt_sched->execute($params);
        $schedules = $stmt_sched->fetchAll();
    }
}

$day_colors = [
    'Senin'  => '#4f46e5',
    'Selasa' => '#0891b2',
    'Rabu'   => '#059669',
    'Kamis'  => '#d97706',
    'Jumat'  => '#dc2626',
    'Sabtu'  => '#7c3aed',
    'Minggu' => '#db2777',
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Jadwal Pelajaran - Siswa</title>
    <link rel="stylesheet" href="/public/assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .main-content { background: #f5f7fb !important; padding: 0 !important; max-width: 100% !important; }

        .page-hero {
            background: linear-gradient(135deg, #0c4a6e 0%, #0369a1 50%, #0ea5e9 100%);
            padding: 2.5rem 3rem 4rem;
            position: relative; overflow: hidden;
        }
        .page-hero::after {
            content: '';
            position: absolute;
            width: 400px; height: 400px; top:-200px; right:-100px;
            background: radial-gradient(circle, rgba(56,189,248,.25) 0%, transparent 60%);
            border-radius: 50%; pointer-events: none;
        }
        .hero-inner { position: relative; z-index: 2; }
        .hero-inner h1 { font-size: 1.6rem; font-weight: 800; color: #fff; margin-bottom: 0.3rem; }
        .hero-sub { color: rgba(255,255,255,0.65); font-size: 0.9rem; }

        .page-content {
            position: relative;
            margin-top: -2rem;
            padding: 0 3rem 3rem;
            z-index: 10;
        }

        /* Search bar */
        .search-bar {
            display: flex;
            gap: 10px;
            margin-bottom: 24px;
        }
        .search-bar input {
            flex: 1;
            border: 1px solid #e2e8f0;
            border-radius: 9px;
            padding: 10px 14px;
            font-size: 0.88rem;
            background: #fff;
            outline: none;
            transition: border-color 0.2s;
        }
        .search-bar input:focus { border-color: #4f46e5; }
        .search-bar button, .search-bar a {
            padding: 10px 18px;
            border-radius: 9px;
            font-size: 0.85rem;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            border: none;
            transition: background 0.15s;
        }
        .btn-search { background: #4f46e5; color: #fff; }
        .btn-search:hover { background: #4338ca; }
        .btn-reset { background: #f1f5f9; color: #64748b; }
        .btn-reset:hover { background: #e2e8f0; }

        /* Day section */
        .day-section { margin-bottom: 28px; }
        .day-label {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 0.8rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            padding: 5px 14px;
            border-radius: 20px;
            color: #fff;
            margin-bottom: 12px;
        }

        /* Schedule list (no table, rows) */
        .sched-list { display: flex; flex-direction: column; gap: 0; }
        .sched-row {
            display: flex;
            align-items: center;
            gap: 0;
            padding: 0;
            background: #fff;
            border-bottom: 1px solid #f1f5f9;
        }
        .sched-row:first-child { border-radius: 12px 12px 0 0; }
        .sched-row:last-child { border-bottom: none; border-radius: 0 0 12px 12px; }
        .sched-row:only-child { border-radius: 12px; }

        .sched-jam {
            width: 56px;
            text-align: center;
            padding: 14px 10px;
            font-size: 0.72rem;
            font-weight: 800;
            color: #94a3b8;
            border-right: 1px solid #f1f5f9;
            flex-shrink: 0;
        }
        .sched-time {
            width: 110px;
            padding: 14px 12px;
            font-size: 0.78rem;
            color: #64748b;
            font-weight: 500;
            border-right: 1px solid #f1f5f9;
            flex-shrink: 0;
        }
        .sched-mapel {
            flex: 1;
            padding: 14px 16px;
            font-size: 0.88rem;
            font-weight: 700;
            color: #1e293b;
        }
        .sched-guru {
            padding: 14px 16px;
            font-size: 0.78rem;
            color: #64748b;
            text-align: right;
            min-width: 140px;
        }

        /* Empty / no class state */
        .empty-state {
            background: #fff;
            border-radius: 12px;
            text-align: center;
            padding: 3rem 1rem;
            color: #94a3b8;
        }
        .empty-state svg { opacity: 0.35; margin-bottom: 10px; }
        .empty-state h3 { color: #334155; font-size: 1rem; margin-bottom: 8px; }

        @media (max-width: 900px) {
            .page-hero { padding: 2rem 1.5rem 3.5rem; }
            .page-content { padding: 0 1.2rem 2rem; }
            .sched-time { display: none; }
            .sched-guru { min-width: 0; font-size: 0.72rem; }
        }
        @media (max-width: 768px) {
            .search-bar { flex-wrap: wrap; }
        }
    </style>
</head>
<body class="admin-full-layout">

<div class="app-container">
    <?php include '../templates/sidebar.php'; ?>

    <main class="main-content">
        <!-- Hero -->
        <div class="page-hero">
            <div class="hero-inner">
                <h1>
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:middle;margin-right:8px;"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    Jadwal Pelajaran
                </h1>
                <p class="hero-sub">Kelas <strong><?php echo htmlspecialchars($class_name); ?></strong></p>
            </div>
        </div>

        <div class="page-content">

            <?php if (!$class_id): ?>
                <div class="empty-state">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    <h3>Akun Anda Belum Terdaftar di Kelas</h3>
                    <p>Silakan hubungi administrator untuk memasukkan Anda ke kelas.</p>
                </div>
            <?php else: ?>
                <!-- Search -->
                <form method="GET" class="search-bar">
                    <input type="text" name="q" value="<?php echo htmlspecialchars($search_query); ?>" placeholder="Cari mata pelajaran...">
                    <button type="submit" class="btn-search">Cari</button>
                    <?php if (!empty($search_query)): ?>
                        <a href="jadwal.php" class="btn-reset">Reset</a>
                    <?php endif; ?>
                </form>

                <?php if (empty($schedules)): ?>
                    <div class="empty-state">
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        <h3>Jadwal Tidak Ditemukan</h3>
                        <?php if (!empty($search_query)): ?>
                            <p>Belum ada jadwal untuk mata pelajaran "<strong><?php echo htmlspecialchars($search_query); ?></strong>".</p>
                        <?php else: ?>
                            <p>Belum ada jadwal yang diunggah untuk <strong>Kelas <?php echo htmlspecialchars($class_name); ?></strong>.</p>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <?php
                    $schedulesByDay = [];
                    foreach ($schedules as $s) {
                        $schedulesByDay[$s['hari']][] = $s;
                    }
                    ?>
                    <?php foreach ($schedulesByDay as $hari => $daySchedules): ?>
                    <?php $color = $day_colors[$hari] ?? '#64748b'; ?>
                    <div class="day-section">
                        <div class="day-label" style="background: <?php echo $color; ?>;">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                            <?php echo htmlspecialchars($hari); ?>
                        </div>
                        <div class="sched-list">
                            <?php foreach ($daySchedules as $s): ?>
                            <div class="sched-row">
                                <div class="sched-jam">Jam <?php echo htmlspecialchars($s['jam_ke']); ?></div>
                                <div class="sched-time"><?php echo htmlspecialchars($s['waktu']); ?></div>
                                <div class="sched-mapel"><?php echo htmlspecialchars($s['mata_pelajaran']); ?></div>
                                <div class="sched-guru"><?php echo htmlspecialchars($s['nama_guru']); ?></div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            <?php endif; ?>

        </div>
    </main>
</div>

</body>
</html>
