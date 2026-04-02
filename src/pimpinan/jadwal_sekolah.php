<?php
// src/pimpinan/jadwal_sekolah.php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['kepsek', 'wakasek'])) {
    header("Location: ../../login.php");
    exit;
}

$search_query = $_GET['search'] ?? '';
$filter_hari = $_GET['hari'] ?? '';
$filter_kelas = $_GET['kelas'] ?? '';

// Update classes dynamically from database or schedules
try {
    $classes_query = $pdo->query("SELECT DISTINCT kelas FROM schedules ORDER BY kelas ASC");
    $class_options = $classes_query->fetchAll(PDO::FETCH_COLUMN);
}
catch (PDOException $e) {
    $class_options = [];
}

// Filter logic
$sql = "SELECT * FROM schedules WHERE 1=1 ";
$params = [];

if ($search_query) {
    $sql .= " AND (nama_guru LIKE ? OR mata_pelajaran LIKE ?) ";
    $params[] = "%$search_query%";
    $params[] = "%$search_query%";
}

if ($filter_hari) {
    $sql .= " AND hari = ? ";
    $params[] = $filter_hari;
}

if ($filter_kelas) {
    $sql .= " AND kelas = ? ";
    $params[] = $filter_kelas;
}

// Order logically by Hari, Kelas, Jam
$sql .= " ORDER BY 
    FIELD(hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'),
    kelas ASC,
    CAST(jam_ke AS UNSIGNED) ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$schedules = $stmt->fetchAll();

$days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Jadwal Sekolah - Pimpinan</title>
    <link rel="stylesheet" href="/public/assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .main-content > .dashboard-hero {
            margin: -2rem -2rem 2rem -2rem !important;
            width: calc(100% + 4rem) !important;
            padding: 2.5rem 3rem !important;
            border-radius: 0 0 40px 0 !important;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .main-content {
            padding: 2rem;
        }
        .table-container { overflow-x: auto; background: white; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.02); }
        table { width: 100%; border-collapse: collapse; min-width: 800px; }
        th { text-align: left; padding: 1rem; border-bottom: 2px solid #f1f5f9; color: #64748b; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.05em; white-space: nowrap; }
        td { padding: 1rem; border-bottom: 1px solid #f1f5f9; vertical-align: middle; color: #334155; font-size: 0.95rem; }
        
        .badge-hari { font-weight: 700; color: #4f46e5; }
        .badge-jam { background: #eef2ff; color: #4338ca; padding: 4px 10px; border-radius: 20px; font-size: 0.8rem; font-weight: 700; }
        .badge-kelas { background: #f0fdf4; color: #166534; padding: 4px 10px; border-radius: 8px; font-size: 0.85rem; font-weight: 700; border: 1px solid #bbf7d0; }
        
        .empty-state { text-align: center; padding: 4rem 2rem; color: #64748b; }
        .empty-icon { font-size: 3.5rem; margin-bottom: 1rem; opacity: 0.8; filter: grayscale(0.5); }
    </style>
</head>
<body style="background-color: #f8fafc;">

<div class="app-container">
    <?php include '../templates/sidebar.php'; ?>
    
    <main class="main-content">
        <!-- Unified Hero Header -->
        <div class="dashboard-hero">
            <div style="position: relative; z-index: 2;">
                <h1 style="color: white; margin-bottom: 0.5rem;"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block; vertical-align:middle; line-height:1;"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg> Jadwal Sekolah</h1>
                <p style="color: rgba(255,255,255,0.8);">Pantau seluruh kegiatan belajar mengajar berdasarkan hari dan kelas.</p>
            </div>
            <!-- Decorative circle -->
            <div style="position: absolute; right: -50px; top: -50px; width: 200px; height: 200px; background: rgba(255,255,255,0.1); border-radius: 50%;"></div>
        </div>
        
        <div style="background: white; border-radius: 12px; padding: 1.5rem; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
            <div style="margin-bottom: 1.5rem;">
                <form method="GET" style="display: flex; gap: 10px; flex-wrap: wrap;">
                    <input type="text" name="search" placeholder="Cari nama guru, mapel..." value="<?php echo htmlspecialchars($search_query); ?>" style="flex: 2; min-width: 200px; padding: 0.75rem 1rem; border: 1px solid #e2e8f0; border-radius: 8px; font-family: inherit;">
                    
                    <select name="hari" style="flex: 1; min-width: 150px; padding: 0.75rem 1rem; border: 1px solid #e2e8f0; border-radius: 8px; font-family: inherit; background: white;">
                        <option value="">Semua Hari</option>
                        <?php foreach ($days as $d): ?>
                            <option value="<?php echo $d; ?>" <?php echo $filter_hari == $d ? 'selected' : ''; ?>><?php echo $d; ?></option>
                        <?php
endforeach; ?>
                    </select>

                    <select name="kelas" style="flex: 1; min-width: 150px; padding: 0.75rem 1rem; border: 1px solid #e2e8f0; border-radius: 8px; font-family: inherit; background: white;">
                        <option value="">Semua Kelas</option>
                        <?php foreach ($class_options as $c): ?>
                            <option value="<?php echo $c; ?>" <?php echo $filter_kelas == $c ? 'selected' : ''; ?>>
                                Kelas <?php echo htmlspecialchars($c); ?>
                            </option>
                        <?php
endforeach; ?>
                    </select>

                    <button type="submit" style="background: #4f46e5; color: white; border: none; padding: 0.75rem 1.5rem; border-radius: 8px; font-weight: 600; cursor: pointer;">Filter</button>
                    <!-- Reset Button -->
                    <?php if ($search_query || $filter_hari || $filter_kelas): ?>
                        <a href="jadwal_sekolah.php" style="background: #f1f5f9; color: #475569; text-decoration: none; padding: 0.75rem 1.5rem; border-radius: 8px; font-weight: 600; display: flex; align-items: center; justify-content: center;">Reset</a>
                    <?php
endif; ?>
                </form>
            </div>

            <?php if (empty($schedules)): ?>
                <div class="empty-state">
                    <div class="empty-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block; vertical-align:middle; line-height:1;"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg></div>
                    <h3 style="color: #1e293b; margin-bottom: 8px;">Jadwal Tidak Ditemukan</h3>
                    <p>Silakan sesuaikan filter pencarian, atau data jadwal belum diupload oleh admin.</p>
                </div>
            <?php
else: ?>
                <div class="table-container">
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
                                <td><span style="color: #64748b; font-size: 0.9rem; font-weight: 500;"><?php echo htmlspecialchars($s['waktu']); ?></span></td>
                                <td><span class="badge-kelas">Kelas <?php echo htmlspecialchars($s['kelas']); ?></span></td>
                                <td><strong style="color: #0f172a;"><?php echo htmlspecialchars($s['mata_pelajaran']); ?></strong></td>
                                <td style="color: #64748b; font-size: 0.85rem;">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block; vertical-align:middle; line-height:1;"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg> <?php echo htmlspecialchars($s['nama_guru']); ?>
                                </td>
                            </tr>
                            <?php
    endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <div style="margin-top: 1rem; font-size: 0.85rem; color: #64748b; text-align: right;">
                    Total Data: <?php echo count($schedules); ?> (terfilter)
                </div>
            <?php
endif; ?>
        </div>
    </main>
</div>

</body>
</html>

