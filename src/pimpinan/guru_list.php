<?php
// src/pimpinan/guru_list.php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['kepsek', 'wakasek'])) {
    header("Location: ../../login.php");
    exit;
}

$search = $_GET['search'] ?? '';
$params = [];
$sql = "SELECT u.*, COUNT(DISTINCT tc.class_id) AS total_kelas,
               GROUP_CONCAT(DISTINCT c.name ORDER BY c.name SEPARATOR ', ') AS nama_kelas
        FROM users u
        LEFT JOIN teacher_classes tc ON u.id = tc.teacher_id
        LEFT JOIN classes c ON tc.class_id = c.id
        WHERE u.role = 'guru'";

if ($search) {
    $sql .= " AND (u.full_name LIKE ? OR u.username LIKE ? OR u.email LIKE ?)";
    $params = array_fill(0, 3, "%$search%");
}
$sql .= " GROUP BY u.id ORDER BY u.full_name ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$guru_list = $stmt->fetchAll();

$total_guru = count($guru_list);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Guru - Pimpinan</title>
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
        .stat-bar {
            display: flex; gap: 1rem;
            margin-bottom: 1.2rem; flex-wrap: wrap;
        }
        .stat-chip {
            background: #fff; border: 1px solid #e8edf5;
            border-radius: 12px; padding: 14px 20px;
            display: flex; align-items: center; gap: 10px;
        }
        .stat-chip-val { font-size: 1.4rem; font-weight: 800; color: #1d4ed8; }
        .stat-chip-lbl { font-size: 0.8rem; color: #94a3b8; font-weight: 500; }
        .filter-bar {
            background: #fff; border-radius: 12px;
            border: 1px solid #e8edf5; padding: 14px 18px;
            display: flex; gap: 10px; margin-bottom: 1.2rem;
        }
        .filter-bar input {
            flex: 1; padding: 8px 12px;
            border: 1px solid #e2e8f0; border-radius: 8px;
            font-family: inherit; font-size: 0.88rem; background: #f8fafc;
        }
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
        .guru-row {
            display: flex; align-items: center; gap: 14px;
            padding: 14px 20px; border-bottom: 1px solid #f8fafc;
        }
        .guru-row:last-child { border-bottom: none; }
        .guru-avatar {
            width: 44px; height: 44px; border-radius: 50%;
            background: linear-gradient(135deg, #1d4ed8, #60a5fa);
            display: flex; align-items: center; justify-content: center;
            font-size: 1rem; font-weight: 700; color: #fff; flex-shrink: 0;
        }
        .guru-name { font-weight: 700; color: #0f172a; font-size: 0.95rem; }
        .guru-meta { font-size: 0.8rem; color: #94a3b8; margin-top: 2px; }
        .guru-classes {
            margin-left: auto; text-align: right;
            font-size: 0.8rem; color: #64748b;
        }
        .badge-kelas {
            display: inline-block; background: #eff6ff; color: #1d4ed8;
            padding: 3px 9px; border-radius: 20px;
            font-size: 0.75rem; font-weight: 700; margin-right: 3px;
        }
        .empty-state { text-align: center; padding: 4rem 2rem; color: #94a3b8; }
        @media (max-width: 768px) {
            .page-content { padding: 0 1rem 2rem; }
            .page-hero { padding: 2rem 1.5rem 4.5rem; }
            .guru-classes { display: none; }
        }
    </style>
</head>
<body>

<div class="app-container">
    <?php include '../templates/sidebar.php'; ?>
    
    <main class="main-content">
        <div class="page-hero">
            <h1>
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:middle;margin-right:8px;"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                Direktori Guru
            </h1>
            <p>Pantau daftar dan penugasan seluruh guru di sekolah.</p>
        </div>

        <div class="page-content">
            <div class="stat-bar">
                <div class="stat-chip">
                    <div>
                        <div class="stat-chip-val"><?php echo $total_guru; ?></div>
                        <div class="stat-chip-lbl">Total Guru</div>
                    </div>
                </div>
            </div>

            <form method="GET" class="filter-bar">
                <input type="text" name="search" placeholder="Cari nama, username, atau email guru..." value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="btn-filter">Cari</button>
                <?php if ($search): ?>
                    <a href="guru_list.php" style="padding:8px 14px; background:#f1f5f9; color:#475569; border-radius:8px; font-weight:600; font-size:0.88rem; text-decoration:none; display:flex; align-items:center;">Reset</a>
                <?php endif; ?>
            </form>

            <div class="db-section">
                <div class="section-head">
                    <h3>Daftar Guru <?php if ($search) echo '(Hasil pencarian "'.htmlspecialchars($search).'")'; ?></h3>
                    <span style="font-size:0.82rem; color:#94a3b8;"><?php echo count($guru_list); ?> guru</span>
                </div>
                <?php if (empty($guru_list)): ?>
                    <div class="empty-state">
                        <svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin-bottom:1rem;opacity:0.4;"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        <p style="font-weight:600; color:#64748b;">Tidak ada guru yang ditemukan.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($guru_list as $g): ?>
                        <div class="guru-row">
                            <div class="guru-avatar"><?php echo strtoupper(substr($g['full_name'], 0, 1)); ?></div>
                            <div>
                                <div class="guru-name"><?php echo htmlspecialchars($g['full_name']); ?></div>
                                <div class="guru-meta">@<?php echo htmlspecialchars($g['username']); ?> &bull; <?php echo htmlspecialchars($g['email'] ?? '-'); ?></div>
                            </div>
                            <div class="guru-classes">
                                <?php if ($g['total_kelas'] > 0): ?>
                                    <span style="font-size:0.78rem; color:#64748b; display:block; margin-bottom:4px;"><?php echo $g['total_kelas']; ?> kelas diampu</span>
                                    <?php
                                    $kelas_arr = $g['nama_kelas'] ? explode(', ', $g['nama_kelas']) : [];
                                    foreach (array_slice($kelas_arr, 0, 4) as $k):
                                    ?>
                                        <span class="badge-kelas"><?php echo htmlspecialchars($k); ?></span>
                                    <?php endforeach; ?>
                                    <?php if (count($kelas_arr) > 4): ?>
                                        <span class="badge-kelas">+<?php echo count($kelas_arr) - 4; ?></span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span style="font-size:0.78rem; color:#94a3b8;">Belum ada kelas</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

</body>
</html>
