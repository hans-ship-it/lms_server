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
                // Determine whether to append or replace
                if (isset($_POST['replace_data'])) {
                    $pdo->exec("TRUNCATE TABLE schedules");
                }

                $pdo->beginTransaction();
                $stmt = $pdo->prepare("INSERT INTO schedules (hari, jam_ke, waktu, kelas, mata_pelajaran, nama_guru) VALUES (?, ?, ?, ?, ?, ?)");

                // Parse across all sheets if multiple exist
                $sheetIndex = 0;
                while ($rows = $xlsx->rows($sheetIndex)) {
                    $guru_map = [];
                    $mapel_map = [];
                    $classes = [];
                    $col_kode_guru = -1;
                    $col_nama_guru = -1;
                    $col_kode_mapel = -1;
                    $col_nama_mapel = -1;

                    // --- PASS 1: Build Mappings & Detect Classes ---
                    foreach ($rows as $row) {
                        // 1. Detect mapping columns
                        if ($col_kode_guru === -1) {
                            foreach ($row as $c_idx => $cell) {
                                $val = strtoupper(trim((string)$cell));
                                if ($val === 'KODE GURU')
                                    $col_kode_guru = $c_idx;
                                if ($val === 'NAMA GURU')
                                    $col_nama_guru = $c_idx;
                                if ($val === 'KODE MAPEL')
                                    $col_kode_mapel = $c_idx;
                                if ($val === 'MATA PELAJARAN' && $c_idx > 10)
                                    $col_nama_mapel = $c_idx;
                            }
                        }

                        // 2. Build mapping dictionary
                        if ($col_kode_guru !== -1) {
                            $kg = trim((string)($row[$col_kode_guru] ?? ''));
                            $ng = trim((string)($row[$col_nama_guru] ?? ''));
                            if ($kg !== '' && $kg !== 'KODE GURU') {
                                $guru_map[$kg] = $ng;
                            }
                        }
                        if ($col_kode_mapel !== -1) {
                            $km = trim((string)($row[$col_kode_mapel] ?? ''));
                            $nm = trim((string)($row[$col_nama_mapel] ?? ''));
                            if ($km !== '' && $km !== 'KODE MAPEL') {
                                $mapel_map[$km] = $nm;
                            }
                        }

                        // 3. Detect Classes Header
                        if (empty($classes)) {
                            $has_class = false;
                            foreach ($row as $c_idx => $cell) {
                                $val = trim((string)$cell);
                                if (preg_match('/^(X|XI|XII)\s*\d+$/i', $val)) {
                                    $classes[$c_idx] = $val;
                                    $has_class = true;
                                }
                            }
                        }
                    }

                    // --- PASS 2: Parse Schedule and Insert ---
                    $current_hari = '';
                    $valid_hari = ['SENIN', 'SELASA', 'RABU', 'KAMIS', 'JUM\'AT', 'JUMAT', 'SABTU'];

                    foreach ($rows as $r_idx => $row) {
                        $hari = trim((string)($row[0] ?? ''));
                        if (in_array(strtoupper($hari), $valid_hari)) {
                            $current_hari = strtoupper($hari);
                        }

                        $jam_ke = trim((string)($row[1] ?? ''));
                        $waktu = trim((string)($row[2] ?? ''));

                        if (is_numeric($jam_ke) && !empty($classes) && !empty($current_hari)) {
                            $col3 = trim((string)($row[3] ?? ''));
                            if (strpos(strtoupper($col3), 'UPACARA') !== false) {
                                foreach ($classes as $c_idx => $c_name) {
                                    $stmt->execute([$current_hari, $jam_ke, $waktu, $c_name, 'UPACARA BENDERA', '']);
                                }
                            }
                            else {
                                // Next row should be mapel
                                $mapel_row = $rows[$r_idx + 1] ?? [];
                                foreach ($classes as $c_idx => $c_name) {
                                    $kd_guru = trim((string)($row[$c_idx] ?? ''));
                                    $kd_mapel = trim((string)($mapel_row[$c_idx] ?? ''));

                                    // Only insert if there's actually a mapel or guru code present
                                    if ($kd_guru !== '' || $kd_mapel !== '') {
                                        $nama_guru = $guru_map[$kd_guru] ?? $kd_guru;
                                        $nama_mapel = $mapel_map[$kd_mapel] ?? $kd_mapel;

                                        $stmt->execute([$current_hari, $jam_ke, $waktu, $c_name, $nama_mapel, $nama_guru]);
                                    }
                                }
                            }
                        }
                    }
                    $sheetIndex++;
                }

                // --- PASS 3: Auto-create Teacher Classes ---
                require_once 'sync_classes.php';

                $pdo->commit();
                $_SESSION['flash'] = ['type' => 'success', 'message' => "Jadwal berhasil diimport dan kelas guru telah disinkronkan!"];
            }
            catch (Exception $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                $_SESSION['flash'] = ['type' => 'error', 'message' => "Gagal import: " . $e->getMessage()];
            }
        }
        else {
            $_SESSION['flash'] = ['type' => 'error', 'message' => "Format file tidak valid atau error saat membaca Excel."];
        }
    }
    else {
        $_SESSION['flash'] = ['type' => 'error', 'message' => "Silakan pilih file Excel yang valid."];
    }
    header("Location: manage_schedules.php");
    exit;
}

// Handle Delete All
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_all'])) {
    $pdo->exec("TRUNCATE TABLE schedules");
    $_SESSION['flash'] = ['type' => 'success', 'message' => "Semua data jadwal berhasil dihapus."];
    header("Location: manage_schedules.php");
    exit;
}

// Pagination & Search
$search = $_GET['search'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 50;
$offset = ($page - 1) * $limit;

$whereClause = "";
$params = [];

if ($search) {
    $whereClause = " WHERE kelas LIKE ? OR mata_pelajaran LIKE ? OR nama_guru LIKE ? OR hari LIKE ?";
    $searchTerm = "%$search%";
    $params = [$searchTerm, $searchTerm, $searchTerm, $searchTerm];
}

// Calculate total filtered records for pagination
$total_stmt = $pdo->prepare("SELECT COUNT(*) FROM schedules" . $whereClause);
$total_stmt->execute($params);
$total_schedules_filtered = $total_stmt->fetchColumn();
$total_pages = max(1, ceil($total_schedules_filtered / $limit));

// Fetch records for current page
$stmt = $pdo->prepare("SELECT * FROM schedules" . $whereClause . " ORDER BY id ASC LIMIT $limit OFFSET $offset");
$stmt->execute($params);
$schedules = $stmt->fetchAll();

// Total count (unfiltered)
$total_schedules = $pdo->query("SELECT COUNT(*) FROM schedules")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Jadwal - Admin</title>
    <link rel="stylesheet" href="/public/assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body class="admin-full-layout">

<div class="app-container">
    <?php include '../templates/sidebar.php'; ?>
    
    <main class="main-content">
        <div class="dashboard-hero">
            <div style="position: relative; z-index: 2;">
                <h1 style="color: white; margin-bottom: 0.5rem;"><svg width='20' height='20' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' style='display:inline-block; vertical-align:middle; line-height:1;'><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg> Kelola Jadwal Pelajaran</h1>
                <p style="color: rgba(255,255,255,0.8);">Upload file Excel untuk memperbarui jadwal guru dan kelas</p>
            </div>
            <!-- Decorative circle -->
            <div style="position: absolute; right: -50px; top: -50px; width: 200px; height: 200px; background: rgba(255,255,255,0.1); border-radius: 50%; blur: 20px;"></div>
        </div>

        <div class="content-overlap">
            
            <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 20px; align-items: start;">
                <!-- Upload Panel -->
                <div class="card">
                    <h3 style="margin-bottom: 15px; border-bottom: 1px solid #e2e8f0; padding-bottom: 10px;"><svg width='20' height='20' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' style='display:inline-block; vertical-align:middle; line-height:1;'><path d='M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4'/><polyline points='17 8 12 3 7 8'/><line x1='12' y1='3' x2='12' y2='15'/></svg> Upload Excel</h3>
                    
                    <?php
if (isset($_SESSION['flash'])) {
    $flash = $_SESSION['flash'];
    $cls = ($flash['type'] == 'error') ? 'badge-danger' : 'badge-success';
    echo "<div class='badge $cls' style='display:block; padding:10px; margin-bottom:15px; text-align:center;'>" . htmlspecialchars($flash['message']) . "</div>";
    unset($_SESSION['flash']);
}
?>

                    <form method="POST" enctype="multipart/form-data">
                        <div class="form-group">
                            <label>File Excel (.xlsx)</label>
                            <input type="file" name="schedule_file" accept=".xlsx" required class="filter-input" style="width: 100%; padding: 8px;">
                            <small style="color: var(--text-muted); display: block; margin-top: 5px;">
                                Header tabel excel harus mengandung: HARI, JAM KE, WAKTU, MATA PELAJARAN, NAMA GURU, atau KELAS.
                            </small>
                        </div>
                        <div class="form-group" style="margin-top: 15px;">
                            <label>
                                <input type="checkbox" name="replace_data" value="1" checked> 
                                Hapus data lama (Replace All)
                            </label>
                        </div>
                        <button type="submit" name="upload_excel" class="btn btn-primary" style="width: 100%; margin-top: 10px;">Import Jadwal</button>
                    </form>

                    <div style="margin-top: 30px; border-top: 1px dashed #e2e8f0; padding-top: 20px;">
                        <form method="POST" onsubmit="return confirm('Anda yakin ingin menghapus SEMUA data jadwal?');">
                            <button type="submit" name="delete_all" class="btn btn-danger" style="width: 100%;">Kosongkan Tabel Jadwal</button>
                        </form>
                    </div>
                </div>

                <!-- Preview Panel -->
                <div class="card">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; border-bottom: 1px solid #e2e8f0; padding-bottom: 10px;">
                        <h3 style="margin: 0;"><svg width='20' height='20' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' style='display:inline-block; vertical-align:middle; line-height:1;'><path d='M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2'/><rect x='8' y='2' width='8' height='4' rx='1' ry='1'/></svg> Preview Data (Total: <?php echo $total_schedules; ?>)</h3>
                        <form method="GET" style="display: flex; gap: 10px;">
                            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Cari guru, kelas, mapel..." class="filter-input" style="padding: 5px 10px; border: 1px solid #e2e8f0; border-radius: 4px;">
                            <button type="submit" class="btn btn-primary" style="padding: 5px 15px;">Cari</button>
                            <?php if ($search): ?>
                                <a href="manage_schedules.php" class="btn btn-danger" style="padding: 5px 15px; text-decoration: none;">Reset</a>
                            <?php
endif; ?>
                        </form>
                    </div>

                    <?php if (empty($schedules)): ?>
                        <div style="text-align: center; padding: 40px; color: var(--text-muted);">
                            <div style="font-size: 3rem; margin-bottom: 10px;"><svg width='20' height='20' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' style='display:inline-block; vertical-align:middle; line-height:1;'><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg></div>
                            <p>Belum ada data jadwal.</p>
                        </div>
                    <?php
else: ?>
                        <div class="table-container" style="max-height: 500px; overflow-y: auto;">
                        <table style="width: 100%; border-collapse: collapse;">
                            <thead>
                                <tr>
                                    <th style="padding: 10px; border-bottom: 1px solid #e2e8f0; text-align: left;">Hari</th>
                                    <th style="padding: 10px; border-bottom: 1px solid #e2e8f0; text-align: left;">Jam Ke</th>
                                    <th style="padding: 10px; border-bottom: 1px solid #e2e8f0; text-align: left;">Waktu</th>
                                    <th style="padding: 10px; border-bottom: 1px solid #e2e8f0; text-align: left;">Kelas</th>
                                    <th style="padding: 10px; border-bottom: 1px solid #e2e8f0; text-align: left;">Mapel</th>
                                    <th style="padding: 10px; border-bottom: 1px solid #e2e8f0; text-align: left;">Guru</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($schedules as $s): ?>
                                <tr>
                                    <td style="padding: 8px 10px; border-bottom: 1px solid #f1f5f9;"><?php echo htmlspecialchars($s['hari']); ?></td>
                                    <td style="padding: 8px 10px; border-bottom: 1px solid #f1f5f9;"><?php echo htmlspecialchars($s['jam_ke']); ?></td>
                                    <td style="padding: 8px 10px; border-bottom: 1px solid #f1f5f9; color: var(--text-muted);"><?php echo htmlspecialchars($s['waktu']); ?></td>
                                    <td style="padding: 8px 10px; border-bottom: 1px solid #f1f5f9;"><strong><?php echo htmlspecialchars($s['kelas']); ?></strong></td>
                                    <td style="padding: 8px 10px; border-bottom: 1px solid #f1f5f9;"><strong><?php echo htmlspecialchars($s['mata_pelajaran']); ?></strong></td>
                                    <td style="padding: 8px 10px; border-bottom: 1px solid #f1f5f9;"><?php echo htmlspecialchars($s['nama_guru']); ?></td>
                                </tr>
                                <?php
    endforeach; ?>
                            </tbody>
                        </table>
                        </div>
                        
                        <?php if ($total_pages > 1): ?>
                            <div style="display: flex; justify-content: center; gap: 5px; margin-top: 15px;">
                                <?php if ($page > 1): ?>
                                    <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>" class="btn btn-secondary" style="padding: 5px 10px; text-decoration: none;">&laquo; Prev</a>
                                <?php
        endif; ?>
                                
                                <span style="padding: 5px 10px; border: 1px solid #e2e8f0; border-radius: 4px; background: #f8fafc;">Halaman <?php echo $page; ?> dari <?php echo $total_pages; ?></span>
                                
                                <?php if ($page < $total_pages): ?>
                                    <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>" class="btn btn-secondary" style="padding: 5px 10px; text-decoration: none;">Next &raquo;</a>
                                <?php
        endif; ?>
                            </div>
                        <?php
    endif; ?>
                        
                    <?php
endif; ?>
                </div>
            </div>

        </div>
    </main>
</div>

</body>
</html>

