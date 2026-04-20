<?php
// src/pimpinan/jadwal_sekolah.php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['kepsek', 'wakasek'])) {
    header("Location: ../../login.php");
    exit;
}

$search_query = $_GET['search'] ?? '';
$filter_hari  = $_GET['hari']   ?? '';
$filter_kelas = $_GET['kelas']  ?? '';

try {
    $classes_query = $pdo->query("SELECT DISTINCT kelas FROM schedules ORDER BY kelas ASC");
    $class_options = $classes_query->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    $class_options = [];
}

$sql = "SELECT * FROM schedules WHERE 1=1 ";
$params = [];

if ($search_query) {
    $sql .= " AND (nama_guru LIKE ? OR mata_pelajaran LIKE ?) ";
    $params[] = "%$search_query%";
    $params[] = "%$search_query%";
}
if ($filter_hari)  { $sql .= " AND hari = ? ";  $params[] = $filter_hari;  }
if ($filter_kelas) { $sql .= " AND kelas = ? "; $params[] = $filter_kelas; }

$sql .= " ORDER BY FIELD(hari,'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu'), kelas ASC, CAST(jam_ke AS UNSIGNED) ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$schedules = $stmt->fetchAll();

$days = ['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jadwal Sekolah - Pimpinan</title>
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
        .db-section {
            background: #fff; border-radius: 14px;
            border: 1px solid #e8edf5; overflow: hidden;
        }
        .filter-bar {
            padding: 16px 20px;
            background: #fff; border-radius: 12px;
            border: 1px solid #e8edf5;
            display: flex; gap: 10px; flex-wrap: wrap;
            margin-bottom: 1.2rem;
        }
        .filter-bar input, .filter-bar select {
            padding: 8px 12px; border: 1px solid #e2e8f0;
            border-radius: 8px; font-family: inherit;
            font-size: 0.88rem; background: #f8fafc;
        }
        .filter-bar input { flex: 2; min-width: 180px; }
        .filter-bar select { flex: 1; min-width: 140px; }
        .btn-filter {
            padding: 8px 18px; background: #1d4ed8;
            color: #fff; border: none; border-radius: 8px;
            font-weight: 600; font-size: 0.88rem;
            cursor: pointer; font-family: inherit;
        }
        .btn-filter:hover { background: #1e40af; }
        .btn-reset {
            padding: 8px 16px; background: #f1f5f9;
            color: #475569; border: none; border-radius: 8px;
            font-weight: 600; font-size: 0.88rem;
            cursor: pointer; font-family: inherit;
            text-decoration: none; display: flex; align-items: center;
        }
        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; min-width: 650px; }
        thead th {
            text-align: left; padding: 12px 16px;
            border-bottom: 2px solid #f1f5f9;
            color: #64748b; font-size: 0.78rem;
            text-transform: uppercase; letter-spacing: 0.05em;
            white-space: nowrap;
        }
        tbody td {
            padding: 13px 16px; border-bottom: 1px solid #f8fafc;
            color: #334155; font-size: 0.9rem; vertical-align: middle;
        }
        tbody tr:hover { background: #fafbfc; }
        .badge-hari { font-weight: 700; color: #1d4ed8; }
        .badge-jam  { background: #eef2ff; color: #4338ca; padding: 3px 9px; border-radius: 20px; font-size: 0.78rem; font-weight: 700; }
        .badge-kelas { background: #f0fdf4; color: #166534; padding: 3px 9px; border-radius: 8px; font-size: 0.82rem; font-weight: 700; border: 1px solid #bbf7d0; }
        .empty-state { text-align: center; padding: 4rem 2rem; color: #94a3b8; }
        .total-info { padding: 12px 16px; font-size: 0.82rem; color: #94a3b8; text-align: right; border-top: 1px solid #f1f5f9; }
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
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:middle;margin-right:8px;"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                Jadwal Sekolah
            </h1>
            <p>Pantau seluruh kegiatan belajar mengajar berdasarkan hari dan kelas.</p>
        </div>

        <div class="page-content">
            <!-- Filter -->
            <form method="GET" class="filter-bar">
                <input type="text" name="search" placeholder="Cari guru atau mata pelajaran..." value="<?php echo htmlspecialchars($search_query); ?>">
                <select name="hari">
                    <option value="">Semua Hari</option>
                    <?php foreach ($days as $d): ?>
                        <option value="<?php echo $d; ?>" <?php echo $filter_hari == $d ? 'selected' : ''; ?>><?php echo $d; ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="kelas">
                    <option value="">Semua Kelas</option>
                    <?php foreach ($class_options as $c): ?>
                        <option value="<?php echo $c; ?>" <?php echo $filter_kelas == $c ? 'selected' : ''; ?>>Kelas <?php echo htmlspecialchars($c); ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn-filter">Filter</button>
                <?php if ($search_query || $filter_hari || $filter_kelas): ?>
                    <a href="jadwal_sekolah.php" class="btn-reset">Reset</a>
                <?php endif; ?>
            </form>

            <div class="db-section">
                <?php if (empty($schedules)): ?>
                    <div class="empty-state">
                        <svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin-bottom:1rem;opacity:0.4;"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        <p style="font-size:1rem; font-weight:600; color:#64748b;">Jadwal tidak ditemukan.</p>
                        <p style="font-size:0.88rem;">Coba sesuaikan filter atau data belum diinput oleh admin.</p>
                    </div>
                <?php else: ?>
                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>Hari</th>
                                    <th>Jam Ke</th>
                                    <th>Waktu</th>
                                    <th>Kelas</th>
                                    <th>Mata Pelajaran</th>
                                    <th>Guru Pengajar</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($schedules as $s): ?>
                                <tr>
                                    <td><span class="badge-hari"><?php echo htmlspecialchars($s['hari']); ?></span></td>
                                    <td><span class="badge-jam">Jam <?php echo htmlspecialchars($s['jam_ke']); ?></span></td>
                                    <td style="color:#64748b; font-size:0.88rem; font-weight:500;"><?php echo htmlspecialchars($s['waktu']); ?></td>
                                    <td><span class="badge-kelas"><?php echo htmlspecialchars($s['kelas']); ?></span></td>
                                    <td><strong style="color:#0f172a;"><?php echo htmlspecialchars($s['mata_pelajaran']); ?></strong></td>
                                    <td style="color:#64748b; font-size:0.88rem;">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:middle;margin-right:4px;"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                        <?php echo htmlspecialchars($s['nama_guru']); ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="total-info">Total: <?php echo count($schedules); ?> jadwal ditemukan</div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

</body>
</html>
