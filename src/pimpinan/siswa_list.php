<?php
// src/pimpinan/siswa_list.php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['kepsek', 'wakasek'])) {
    header("Location: ../../login.php");
    exit;
}

$search          = $_GET['search']     ?? '';
$filter_kelas    = $_GET['kelas']      ?? '';
$filter_jenjang  = $_GET['jenjang']    ?? '';

// Get class list for filter
$all_classes = $pdo->query("SELECT DISTINCT name FROM classes ORDER BY name ASC")->fetchAll(PDO::FETCH_COLUMN);

$sql = "SELECT u.id, u.full_name, u.username,
               c.name AS kelas_name, c.grade_level
        FROM users u
        LEFT JOIN classes c ON u.class_id = c.id
        WHERE u.role = 'siswa'";

$params = [];
if ($search) {
    $sql .= " AND (u.full_name LIKE ? OR u.username LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($filter_kelas)   { $sql .= " AND c.name = ?";        $params[] = $filter_kelas;   }
if ($filter_jenjang) { $sql .= " AND c.grade_level = ?"; $params[] = $filter_jenjang; }

$sql .= " ORDER BY c.grade_level ASC, c.name ASC, u.full_name ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$siswa_list = $stmt->fetchAll();
$total_siswa = count($siswa_list);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Siswa - Pimpinan</title>
    <link rel="stylesheet" href="/public/assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .main-content { background: #f5f7fb !important; padding: 0 !important; }
        .page-hero {
            background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 50%, #1d4ed8 100%);
            padding: 2.5rem 3rem 5rem;
            position: relative; overflow: hidden;
        }
        .page-hero::before {
            content: ''; position: absolute;
            right: -60px; top: -60px;
            width: 250px; height: 250px;
            background: rgba(255,255,255,0.07); border-radius: 50%;
        }
        .page-hero h1 { color: #fff; font-size: 1.6rem; font-weight: 700; margin: 0 0 0.4rem; }
        .page-hero p  { color: rgba(255,255,255,0.8); margin: 0; font-size: 0.95rem; }
        .page-content {
            position: relative; margin-top: -2.5rem;
            padding: 0 3rem 3rem; z-index: 10;
        }
        .stat-chip {
            background: #fff; border: 1px solid #e8edf5;
            border-radius: 12px; padding: 14px 20px;
            display: inline-flex; align-items: center; gap: 10px;
            margin-bottom: 1.2rem;
        }
        .stat-chip-val { font-size: 1.4rem; font-weight: 800; color: #1d4ed8; }
        .stat-chip-lbl { font-size: 0.8rem; color: #94a3b8; font-weight: 500; }
        .filter-bar {
            background: #fff; border-radius: 12px;
            border: 1px solid #e8edf5; padding: 14px 18px;
            display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 1.2rem;
        }
        .filter-bar input, .filter-bar select {
            padding: 8px 12px; border: 1px solid #e2e8f0;
            border-radius: 8px; font-family: inherit;
            font-size: 0.88rem; background: #f8fafc;
        }
        .filter-bar input { flex: 2; min-width: 180px; }
        .filter-bar select { flex: 1; min-width: 130px; }
        .btn-filter {
            padding: 8px 18px; background: #1d4ed8;
            color: #fff; border: none; border-radius: 8px;
            font-weight: 600; font-size: 0.88rem;
            cursor: pointer; font-family: inherit;
        }
        .db-section {
            background: #fff; border-radius: 14px;
            border: 1px solid #e8edf5; overflow: hidden;
        }
        .section-head {
            padding: 14px 20px; border-bottom: 1px solid #f1f5f9;
            display: flex; justify-content: space-between; align-items: center;
        }
        .section-head h3 { font-size: 0.95rem; font-weight: 700; color: #0f172a; margin: 0; }
        .siswa-row {
            display: flex; align-items: center; gap: 14px;
            padding: 13px 20px; border-bottom: 1px solid #f8fafc;
        }
        .siswa-row:last-child { border-bottom: none; }
        .siswa-avatar {
            width: 40px; height: 40px; border-radius: 50%;
            background: linear-gradient(135deg, #7c3aed, #a78bfa);
            display: flex; align-items: center; justify-content: center;
            font-size: 0.9rem; font-weight: 700; color: #fff; flex-shrink: 0;
        }
        .siswa-name { font-weight: 700; color: #0f172a; font-size: 0.9rem; }
        .siswa-meta { font-size: 0.78rem; color: #94a3b8; margin-top: 2px; }
        .badge-kelas {
            margin-left: auto; flex-shrink: 0;
            background: #eff6ff; color: #1d4ed8;
            padding: 4px 12px; border-radius: 20px;
            font-size: 0.78rem; font-weight: 700;
        }
        .no-kelas { color: #94a3b8; font-size: 0.78rem; margin-left: auto; }
        .empty-state { text-align: center; padding: 4rem 2rem; color: #94a3b8; }
        @media (max-width: 768px) {
            .page-content { padding: 0 1rem 2rem; }
            .page-hero { padding: 2rem 1.5rem 4.5rem; }
            .filter-bar { flex-direction: column; }
        }
    </style>
</head>
<body>

<div class="app-container">
    <?php include '../templates/sidebar.php'; ?>
    
    <main class="main-content">
        <div class="page-hero">
            <h1>
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:middle;margin-right:8px;"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                Direktori Siswa
            </h1>
            <p>Pantau data dan penempatan kelas seluruh siswa.</p>
        </div>

        <div class="page-content">
            <div class="stat-chip">
                <div>
                    <div class="stat-chip-val"><?php echo $total_siswa; ?></div>
                    <div class="stat-chip-lbl">Total Siswa (terfilter)</div>
                </div>
            </div>

            <form method="GET" class="filter-bar">
                <input type="text" name="search" placeholder="Cari nama atau username siswa..." value="<?php echo htmlspecialchars($search); ?>">
                <select name="jenjang">
                    <option value="">Semua Jenjang</option>
                    <option value="10" <?php echo $filter_jenjang == '10' ? 'selected' : ''; ?>>Kelas 10</option>
                    <option value="11" <?php echo $filter_jenjang == '11' ? 'selected' : ''; ?>>Kelas 11</option>
                    <option value="12" <?php echo $filter_jenjang == '12' ? 'selected' : ''; ?>>Kelas 12</option>
                </select>
                <select name="kelas">
                    <option value="">Semua Kelas</option>
                    <?php foreach ($all_classes as $k): ?>
                        <option value="<?php echo $k; ?>" <?php echo $filter_kelas == $k ? 'selected' : ''; ?>><?php echo htmlspecialchars($k); ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn-filter">Filter</button>
                <?php if ($search || $filter_kelas || $filter_jenjang): ?>
                    <a href="siswa_list.php" style="padding:8px 14px; background:#f1f5f9; color:#475569; border-radius:8px; font-weight:600; font-size:0.88rem; text-decoration:none; display:flex; align-items:center;">Reset</a>
                <?php endif; ?>
            </form>

            <div class="db-section">
                <div class="section-head">
                    <h3>Daftar Siswa</h3>
                    <span style="font-size:0.82rem; color:#94a3b8;"><?php echo $total_siswa; ?> siswa</span>
                </div>
                <?php if (empty($siswa_list)): ?>
                    <div class="empty-state">
                        <svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin-bottom:1rem;opacity:0.4;"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                        <p style="font-weight:600; color:#64748b;">Tidak ada siswa yang ditemukan.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($siswa_list as $s): ?>
                        <div class="siswa-row">
                            <div class="siswa-avatar"><?php echo strtoupper(substr($s['full_name'], 0, 1)); ?></div>
                            <div>
                                <div class="siswa-name"><?php echo htmlspecialchars($s['full_name']); ?></div>
                                <div class="siswa-meta">@<?php echo htmlspecialchars($s['username']); ?> &bull; <?php echo htmlspecialchars($s['email'] ?? '-'); ?></div>
                            </div>
                            <?php if ($s['kelas_name']): ?>
                                <span class="badge-kelas"><?php echo htmlspecialchars($s['kelas_name']); ?></span>
                            <?php else: ?>
                                <span class="no-kelas">Belum ada kelas</span>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

</body>
</html>
