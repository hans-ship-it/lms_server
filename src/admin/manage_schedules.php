<?php
// src/admin/manage_schedules.php
session_start();
require_once '../../config/database.php';
require_once '../lib/SimpleXLSX.php';

use Shuchkin\SimpleXLSX;

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

$success = "";
$error = "";

// Handle Upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_excel'])) {
    if (isset($_FILES['schedule_file']) && $_FILES['schedule_file']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['schedule_file']['tmp_name'];
        if ($xlsx = SimpleXLSX::parse($file_tmp)) {
            try {
                if (isset($_POST['replace_data'])) $pdo->exec("TRUNCATE TABLE schedules");
                $pdo->beginTransaction();
                $stmt = $pdo->prepare("INSERT INTO schedules (hari, jam_ke, waktu, kelas, mata_pelajaran, nama_guru) VALUES (?, ?, ?, ?, ?, ?)");
                $sheetIndex = 0;
                while ($rows = $xlsx->rows($sheetIndex)) {
                    $guru_map = []; $mapel_map = []; $classes = [];
                    $col_kode_guru = -1; $col_nama_guru = -1; $col_kode_mapel = -1; $col_nama_mapel = -1;
                    foreach ($rows as $row) {
                        if ($col_kode_guru === -1) {
                            foreach ($row as $c_idx => $cell) {
                                $val = strtoupper(trim((string)$cell));
                                if ($val === 'KODE GURU')    $col_kode_guru  = $c_idx;
                                if ($val === 'NAMA GURU')    $col_nama_guru  = $c_idx;
                                if ($val === 'KODE MAPEL')   $col_kode_mapel = $c_idx;
                                if ($val === 'MATA PELAJARAN' && $c_idx > 10) $col_nama_mapel = $c_idx;
                            }
                        }
                        if ($col_kode_guru !== -1) {
                            $kg = trim((string)($row[$col_kode_guru] ?? ''));
                            $ng = trim((string)($row[$col_nama_guru] ?? ''));
                            if ($kg !== '' && $kg !== 'KODE GURU') $guru_map[$kg] = $ng;
                        }
                        if ($col_kode_mapel !== -1) {
                            $km = trim((string)($row[$col_kode_mapel] ?? ''));
                            $nm = trim((string)($row[$col_nama_mapel] ?? ''));
                            if ($km !== '' && $km !== 'KODE MAPEL') $mapel_map[$km] = $nm;
                        }
                        if (empty($classes)) {
                            foreach ($row as $c_idx => $cell) {
                                $val = trim((string)$cell);
                                if (preg_match('/^(X|XI|XII)\s*\d+$/i', $val)) $classes[$c_idx] = $val;
                            }
                        }
                    }
                    $current_hari = '';
                    $valid_hari = ['SENIN','SELASA','RABU','KAMIS',"JUM'AT",'JUMAT','SABTU'];
                    foreach ($rows as $r_idx => $row) {
                        $hari = trim((string)($row[0] ?? ''));
                        if (in_array(strtoupper($hari), $valid_hari)) $current_hari = strtoupper($hari);
                        $jam_ke = trim((string)($row[1] ?? ''));
                        $waktu  = trim((string)($row[2] ?? ''));
                        if (is_numeric($jam_ke) && !empty($classes) && !empty($current_hari)) {
                            $col3 = trim((string)($row[3] ?? ''));
                            if (strpos(strtoupper($col3), 'UPACARA') !== false) {
                                foreach ($classes as $c_idx => $c_name) $stmt->execute([$current_hari, $jam_ke, $waktu, $c_name, 'UPACARA BENDERA', '']);
                            } else {
                                $mapel_row = $rows[$r_idx + 1] ?? [];
                                foreach ($classes as $c_idx => $c_name) {
                                    $kd_guru  = trim((string)($row[$c_idx] ?? ''));
                                    $kd_mapel = trim((string)($mapel_row[$c_idx] ?? ''));
                                    if ($kd_guru !== '' || $kd_mapel !== '') {
                                        $nama_guru  = $guru_map[$kd_guru]   ?? $kd_guru;
                                        $nama_mapel = $mapel_map[$kd_mapel] ?? $kd_mapel;
                                        $stmt->execute([$current_hari, $jam_ke, $waktu, $c_name, $nama_mapel, $nama_guru]);
                                    }
                                }
                            }
                        }
                    }
                    $sheetIndex++;
                }
                require_once 'sync_classes.php';
                $pdo->commit();
                $_SESSION['flash'] = ['type' => 'success', 'message' => 'Jadwal berhasil diimport dan kelas guru telah disinkronkan!'];
            } catch (Exception $e) {
                if ($pdo->inTransaction()) $pdo->rollBack();
                $_SESSION['flash'] = ['type' => 'error', 'message' => 'Gagal import: ' . $e->getMessage()];
            }
        } else {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Format file tidak valid atau error saat membaca Excel.'];
        }
    } else {
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'Silakan pilih file Excel yang valid.'];
    }
    header("Location: manage_schedules.php"); exit;
}

// Handle Delete All
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_all'])) {
    $pdo->exec("TRUNCATE TABLE schedules");
    $_SESSION['flash'] = ['type' => 'success', 'message' => 'Semua data jadwal berhasil dihapus.'];
    header("Location: manage_schedules.php"); exit;
}

// Pagination & Search
$search = $_GET['search'] ?? '';
$page   = max(1, (int)($_GET['page'] ?? 1));
$limit  = 50;
$offset = ($page - 1) * $limit;

$whereClause = "";
$params = [];
if ($search) {
    $whereClause = " WHERE kelas LIKE ? OR mata_pelajaran LIKE ? OR nama_guru LIKE ? OR hari LIKE ?";
    $searchTerm  = "%$search%";
    $params = [$searchTerm, $searchTerm, $searchTerm, $searchTerm];
}

$total_stmt = $pdo->prepare("SELECT COUNT(*) FROM schedules" . $whereClause);
$total_stmt->execute($params);
$total_schedules_filtered = $total_stmt->fetchColumn();
$total_pages = max(1, ceil($total_schedules_filtered / $limit));

$stmt = $pdo->prepare("SELECT * FROM schedules" . $whereClause . " ORDER BY id ASC LIMIT $limit OFFSET $offset");
$stmt->execute($params);
$schedules = $stmt->fetchAll();

$total_schedules = $pdo->query("SELECT COUNT(*) FROM schedules")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Jadwal - Admin</title>
    <link rel="stylesheet" href="/public/assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .main-content { background: #f5f7fb !important; padding: 0 !important; }
        .page-hero {
            background: linear-gradient(135deg, #1e1b4b 0%, #312e81 50%, #4338ca 100%);
            padding: 2.5rem 3rem 5rem; position: relative; overflow: hidden;
        }
        .page-hero::before { content:''; position:absolute; right:-60px; top:-60px; width:250px; height:250px; background:rgba(255,255,255,0.07); border-radius:50%; }
        .page-hero h1 { color:#fff; font-size:1.6rem; font-weight:700; margin:0 0 0.4rem; }
        .page-hero p  { color:rgba(255,255,255,0.8); margin:0; font-size:0.95rem; }
        .page-content { position:relative; margin-top:-2.5rem; padding:0 3rem 3rem; z-index:10; }
        .two-col { display:grid; grid-template-columns:1fr 2fr; gap:1.5rem; align-items:flex-start; }
        .db-section { background:#fff; border-radius:14px; border:1px solid #e8edf5; overflow:hidden; }
        .section-head { padding:16px 22px; border-bottom:1px solid #f1f5f9; display:flex; justify-content:space-between; align-items:center; }
        .section-head h3 { font-size:1rem; font-weight:700; color:#0f172a; margin:0; }
        .section-body { padding:20px 22px; }
        .alert-success { background:#dcfce7;color:#166534;padding:10px 16px;border-radius:8px;margin-bottom:1rem;font-weight:500;border:1px solid #bbf7d0;font-size:0.9rem; }
        .alert-error   { background:#fee2e2;color:#991b1b;padding:10px 16px;border-radius:8px;margin-bottom:1rem;font-weight:500;border:1px solid #fecaca;font-size:0.9rem; }
        .form-group { margin-bottom:1rem; }
        .form-group label { display:block; font-size:0.85rem; font-weight:600; color:#374151; margin-bottom:5px; }
        .form-group input[type="file"], .form-group input[type="text"] {
            width:100%; padding:9px 12px; border:1px solid #e2e8f0; border-radius:8px;
            font-family:inherit; font-size:0.88rem; background:#fff; box-sizing:border-box;
        }
        .form-group small { color:#94a3b8; font-size:0.78rem; margin-top:4px; display:block; }
        .btn-import { width:100%; padding:10px; background:linear-gradient(135deg,#312e81,#4338ca); color:#fff; border:none; border-radius:9px; font-weight:700; font-size:0.95rem; cursor:pointer; font-family:inherit; margin-top:0.5rem; }
        .btn-import:hover { background:linear-gradient(135deg,#1e1b4b,#312e81); }
        .btn-danger-full { width:100%; padding:9px; background:#fee2e2; color:#991b1b; border:none; border-radius:9px; font-weight:600; font-size:0.9rem; cursor:pointer; font-family:inherit; }
        .btn-danger-full:hover { background:#fecaca; }
        .divider { margin:20px 0; border:none; border-top:1px dashed #e2e8f0; }
        .search-bar { display:flex; gap:8px; }
        .search-bar input { flex:1; padding:8px 12px; border:1px solid #e2e8f0; border-radius:8px; font-family:inherit; font-size:0.88rem; }
        .btn-search { padding:8px 16px; background:#4338ca; color:#fff; border:none; border-radius:8px; font-weight:600; cursor:pointer; font-family:inherit; font-size:0.88rem; }
        .table-wrap { overflow-x:auto; max-height:480px; overflow-y:auto; }
        table { width:100%; border-collapse:collapse; min-width:550px; }
        thead th { text-align:left; padding:11px 14px; border-bottom:2px solid #f1f5f9; color:#64748b; font-size:0.78rem; text-transform:uppercase; letter-spacing:0.05em; white-space:nowrap; position:sticky; top:0; background:#fff; }
        tbody td { padding:10px 14px; border-bottom:1px solid #f8fafc; color:#334155; font-size:0.88rem; }
        tbody tr:hover { background:#fafbfc; }
        .empty-state { text-align:center; padding:3rem 2rem; color:#94a3b8; }
        .pagination { display:flex; justify-content:center; gap:6px; padding:14px 20px; border-top:1px solid #f1f5f9; }
        .page-btn { padding:5px 12px; border:1px solid #e2e8f0; border-radius:7px; background:#f8fafc; color:#475569; text-decoration:none; font-size:0.82rem; font-weight:600; }
        .page-btn.active { background:#4338ca; color:#fff; border-color:#4338ca; }
        @media (max-width: 768px) { .two-col { grid-template-columns:1fr; } .page-content { padding:0 1rem 2rem; } .page-hero { padding:2rem 1.5rem 4.5rem; } }
    </style>
</head>
<body>

<div class="app-container">
    <?php include '../templates/sidebar.php'; ?>
    
    <main class="main-content">
        <div class="page-hero">
            <h1>
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:middle;margin-right:8px;"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                Kelola Jadwal Pelajaran
            </h1>
            <p>Upload file Excel untuk memperbarui jadwal guru dan kelas.</p>
        </div>

        <div class="page-content">
            <?php if (isset($_SESSION['flash'])):
                $flash = $_SESSION['flash']; unset($_SESSION['flash']);
                $cls = $flash['type'] === 'error' ? 'alert-error' : 'alert-success';
            ?>
                <div class="<?php echo $cls; ?>"><?php echo htmlspecialchars($flash['message']); ?></div>
            <?php endif; ?>

            <div class="two-col">
                <!-- Upload Panel -->
                <div class="db-section">
                    <div class="section-head">
                        <h3>Upload Excel</h3>
                    </div>
                    <div class="section-body">
                        <form method="POST" enctype="multipart/form-data">
                            <div class="form-group">
                                <label>File Excel (.xlsx)</label>
                                <input type="file" name="schedule_file" accept=".xlsx" required>
                                <small>Header excel harus mengandung: HARI, JAM KE, WAKTU, MATA PELAJARAN, dll.</small>
                            </div>
                            <div class="form-group">
                                <label style="font-weight:400; cursor:pointer;">
                                    <input type="checkbox" name="replace_data" value="1" checked> Hapus data lama (Replace All)
                                </label>
                            </div>
                            <button type="submit" name="upload_excel" class="btn-import">Import Jadwal</button>
                        </form>
                        <hr class="divider">
                        <form method="POST" onsubmit="return confirm('Yakin hapus SEMUA data jadwal?');">
                            <button type="submit" name="delete_all" class="btn-danger-full">Kosongkan Tabel Jadwal</button>
                        </form>
                    </div>
                </div>

                <!-- Preview Panel -->
                <div class="db-section">
                    <div class="section-head">
                        <h3>Preview Jadwal (Total: <?php echo $total_schedules; ?>)</h3>
                        <form method="GET" class="search-bar">
                            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Cari guru, kelas, mapel...">
                            <button type="submit" class="btn-search">Cari</button>
                            <?php if ($search): ?>
                                <a href="manage_schedules.php" style="padding:8px 12px;background:#f1f5f9;color:#475569;border-radius:8px;font-weight:600;font-size:0.82rem;text-decoration:none;display:flex;align-items:center;">Reset</a>
                            <?php endif; ?>
                        </form>
                    </div>
                    <?php if (empty($schedules)): ?>
                        <div class="empty-state">
                            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin-bottom:1rem;opacity:0.4;"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/></svg>
                            <p style="font-weight:600;color:#64748b;">Belum ada data jadwal.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-wrap">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Hari</th><th>Jam</th><th>Waktu</th><th>Kelas</th><th>Mapel</th><th>Guru</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($schedules as $s): ?>
                                    <tr>
                                        <td style="font-weight:700;color:#4338ca;"><?php echo htmlspecialchars($s['hari']); ?></td>
                                        <td><?php echo htmlspecialchars($s['jam_ke']); ?></td>
                                        <td style="color:#94a3b8;"><?php echo htmlspecialchars($s['waktu']); ?></td>
                                        <td><strong><?php echo htmlspecialchars($s['kelas']); ?></strong></td>
                                        <td><strong><?php echo htmlspecialchars($s['mata_pelajaran']); ?></strong></td>
                                        <td style="color:#64748b;"><?php echo htmlspecialchars($s['nama_guru']); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php if ($total_pages > 1): ?>
                            <div class="pagination">
                                <?php if ($page > 1): ?>
                                    <a href="?page=<?php echo $page-1; ?>&search=<?php echo urlencode($search); ?>" class="page-btn">&laquo; Prev</a>
                                <?php endif; ?>
                                <span class="page-btn active">Hal <?php echo $page; ?> / <?php echo $total_pages; ?></span>
                                <?php if ($page < $total_pages): ?>
                                    <a href="?page=<?php echo $page+1; ?>&search=<?php echo urlencode($search); ?>" class="page-btn">Next &raquo;</a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
</div>

</body>
</html>
