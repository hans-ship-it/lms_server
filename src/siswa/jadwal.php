<?php
// src/siswa/jadwal.php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'siswa') {
    header("Location: ../../login.php");
    exit;
}

$siswa_id = $_SESSION['user_id'];

// Get user's class info
$stmt_user = $pdo->prepare("SELECT class_id FROM users WHERE id = ?");
$stmt_user->execute([$siswa_id]);
$user = $stmt_user->fetch();
$class_id = $user['class_id'] ?? null;

$class_name = "Belum Terdaftar di Kelas";
$schedules = [];

// Handle search query
$search_query = isset($_GET['q']) ? trim($_GET['q']) : '';

if ($class_id) {
    // Get class name
    $stmt_class = $pdo->prepare("SELECT name FROM classes WHERE id = ?");
    $stmt_class->execute([$class_id]);
    $class_info = $stmt_class->fetch();

    if ($class_info) {
        $class_name = $class_info['name'];

        // Fetch schedules for this exact class name, optionally filtered by search query
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
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Jadwal Pelajaran - Siswa</title>
    <link rel="stylesheet" href="/public/assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">

</head>
<body class="admin-full-layout">

<div class="app-container">
    <?php include '../templates/sidebar.php'; ?>

    <main class="main-content">
        <!-- Dashboard Hero -->
        <div class="dashboard-hero">
            <div style="position: relative; z-index: 2;">
                <h1 style="color: white; margin-bottom: 0.5rem;">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:middle;"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    Jadwal Pelajaran
                </h1>
                <p style="color: rgba(255,255,255,0.8);">Anda berada di <strong>Kelas <?php echo htmlspecialchars($class_name); ?></strong></p>
            </div>
            <div style="position: absolute; right: -50px; top: -50px; width: 200px; height: 200px; background: rgba(255,255,255,0.08); border-radius: 50%;"></div>
        </div>

        <div class="content-overlap">
                <?php if (!$class_id): ?>
                    <div class="empty-state">
                        <div class="empty-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block; vertical-align:middle; line-height:1;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg></div>
                        <h3 style="color: #1e293b; margin-bottom: 8px;">Akun Anda Belum Terdaftar di Kelas</h3>
                        <p>Silakan hubungi administrator untuk memasukkan Anda ke kelas.</p>
                    </div>
                <?php
else: ?>
                    <form method="GET" class="filter-section" style="margin-bottom: 20px; display: flex; gap: 10px;">
                        <input type="text" name="q" value="<?php echo htmlspecialchars($search_query); ?>" placeholder="Cari berdasarkan mata pelajaran..." class="form-control" style="flex-grow: 1;">
                        <button type="submit" class="btn btn-primary">Cari Jadwal</button>
                        <?php if (!empty($search_query)): ?>
                            <a href="jadwal.php" class="btn btn-secondary">Reset</a>
                        <?php
    endif; ?>
                    </form>

                    <?php if (empty($schedules)): ?>
                        <div class="empty-state">
                            <div class="empty-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block; vertical-align:middle; line-height:1;"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg></div>
                            <h3 style="color: #1e293b; margin-bottom: 8px;">Jadwal Tidak Ditemukan</h3>
                            <?php if (!empty($search_query)): ?>
                                <p>Belum ada jadwal yang ditemukan untuk mata pelajaran <strong>"<?php echo htmlspecialchars($search_query); ?>"</strong> di kelas Anda.</p>
                            <?php
        else: ?>
                                <p>Belum ada jadwal yang diunggah untuk <strong>Kelas <?php echo htmlspecialchars($class_name); ?></strong>.</p>
                            <?php
        endif; ?>
                        </div>
                    <?php
    else: ?>
                        <?php
        // Group schedules by day
        $schedulesByDay = [];
        foreach ($schedules as $s) {
            $hari = $s['hari'];
            if (!isset($schedulesByDay[$hari])) {
                $schedulesByDay[$hari] = [];
            }
            $schedulesByDay[$hari][] = $s;
        }
?>

                        <?php foreach ($schedulesByDay as $hari => $daySchedules): ?>
                            <h3 style="margin-top: 1.5rem; margin-bottom: 0.75rem; color: #1e293b; font-size: 1.25rem;">Hari <?php echo htmlspecialchars($hari); ?></h3>
                            <div class="table-responsive" style="margin-bottom: 2rem;">
                                <table class="data-table">
                                    <thead>
                                        <tr>
                                            <th>Jam Ke</th>
                                            <th>Waktu</th>
                                            <th>Kelas</th>
                                            <th>Mata Pelajaran</th>
                                            <th>Nama Guru</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($daySchedules as $s): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($s['jam_ke']); ?></td>
                                            <td><?php echo htmlspecialchars($s['waktu']); ?></td>
                                            <td><?php echo htmlspecialchars($s['kelas']); ?></td>
                                            <td><strong><?php echo htmlspecialchars($s['mata_pelajaran']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($s['nama_guru']); ?></td>
                                        </tr>
                                        <?php
            endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php
        endforeach; ?>
                    <?php
    endif; ?>
                <?php
endif; ?>
            </div>
        </div><!-- /.content-overlap -->
        </main>
</div>

</body>
</html>

