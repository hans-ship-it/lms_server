<?php
// src/guru/jadwal_mengajar.php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'guru') {
    header("Location: ../../login.php");
    exit;
}

$teacher_id = $_SESSION['user_id'];
$guru_name = $_SESSION['full_name'];

// Remove academic titles for looser matching (e.g., "M. Gilang, S.Pd." -> "M. Gilang")
$searchName = trim(explode(',', $guru_name)[0]);

// Fetch schedules matching this teacher
$search_query = isset($_GET['q']) ? trim($_GET['q']) : $searchName;

$stmt = $pdo->prepare("SELECT * FROM schedules WHERE LOWER(nama_guru) LIKE LOWER(?) ORDER BY
    FIELD(hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'),
    CAST(jam_ke AS UNSIGNED) ASC, id ASC");
$stmt->execute(['%' . $search_query . '%']);
$schedules = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Jadwal Mengajar - Guru</title>
    <link rel="stylesheet" href="/public/assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after {
            box-sizing: border-box;
        }
        
        body {
            overflow-x: hidden;
        }

        .card {
            background: #fff;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.03), 0 4px 10px rgba(0,0,0,0.02);
            animation: fade-up 0.4s ease-out both;
        }

        @keyframes fade-up {
            from { opacity: 0; transform: translateY(16px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* â”€â”€â”€ Filter Bar â”€â”€â”€ */
        .filter-bar {
            display: flex;
            gap: 15px;
            margin-bottom: 25px;
            align-items: center;
            background: #f8fafc;
            padding: 12px 16px;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
        }

        .filter-input {
            flex-grow: 1;
            padding: 10px 16px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-family: inherit;
            font-size: 0.95rem;
            transition: all 0.2s;
        }

        .filter-input:focus {
            outline: none;
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }

        .btn-filter {
            background: #4f46e5;
            color: white;
            border: none;
            padding: 10px 24px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.95rem;
            cursor: pointer;
            transition: all 0.2s;
            white-space: nowrap;
        }

        .btn-filter:hover {
            background: #4338ca;
            transform: translateY(-1px);
        }

        /* â”€â”€â”€ Table Styles â”€â”€â”€ */
        .table-container {
            width: 100%;
            overflow-x: auto;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            margin-bottom: 1rem;
        }

        table {
            width: 100%;
            min-width: 800px; /* Prevent cramped columns but allow scrolling inside container */
            border-collapse: collapse;
            background: white;
        }

        th, td {
            padding: 16px 20px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }

        th {
            background: #f8fafc;
            color: #475569;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 0.05em;
            white-space: nowrap;
        }

        td {
            color: #334155;
            font-size: 0.95rem;
        }

        tr:last-child td {
            border-bottom: none;
        }

        tr:hover td {
            background-color: #f8fafc;
        }

        .badge-hari {
            font-weight: 700;
            color: #4f46e5;
        }

        .badge-jam {
            background: #eef2ff;
            color: #4338ca;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 700;
        }

        .badge-kelas {
            background: #f0fdf4;
            color: #166534;
            padding: 4px 10px;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 700;
            border: 1px solid #bbf7d0;
        }

        /* â”€â”€â”€ Empty State â”€â”€â”€ */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: #64748b;
        }

        .empty-icon {
            font-size: 3.5rem;
            margin-bottom: 1rem;
            opacity: 0.8;
            filter: grayscale(0.5);
        }

        @media (max-width: 900px) {
            .card { padding: 20px; }
            .filter-bar { flex-direction: column; align-items: stretch; }
            .hero-title { font-size: 1.5rem; }
        }
    </style>
</head>
<body>

<div class="app-container">
    <?php include '../templates/sidebar.php'; ?>

    <main class="main-content">
        <div class="dashboard-hero">
            <div style="position: relative; z-index: 2;">
                <h1 style="color: white; margin-bottom: 0.5rem; font-size: 2rem; font-weight: 800; display: flex; align-items: center; gap: 12px;"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block; vertical-align:middle; line-height:1;"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg> Jadwal Mengajar Anda</h1>
                <p style="color: rgba(255,255,255,0.8); margin-top: 8px; font-size: 1rem;">Jadwal yang telah diatur oleh Admin untuk Anda penuhi.</p>
            </div>
            <!-- Decorative circle since style.css doesn't include the right-top one automatically -->
            <div style="position: absolute; width: 600px; height: 600px; top: -250px; right: -150px; background: radial-gradient(circle, rgba(129,140,248,0.2) 0%, transparent 60%); border-radius: 50%; pointer-events: none;"></div>
        </div>

        <div class="content-overlap">
            <div class="card">
                <form method="GET" class="filter-bar">
                    <input type="text" name="q" value="<?php echo htmlspecialchars($search_query); ?>" placeholder="Cari jadwal berdasarkan kata kunci terkait..." class="filter-input">
                    <button type="submit" class="btn-filter">Cari Jadwal</button>
                    <?php if (!empty($search_query)): ?>
                        <a href="jadwal_mengajar.php" class="btn-filter" style="background: #e2e8f0; color: #475569; text-decoration: none; text-align: center;">Reset</a>
                    <?php
endif; ?>
                </form>

                <?php if (empty($schedules)): ?>
                    <div class="empty-state">
                        <div class="empty-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block; vertical-align:middle; line-height:1;"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg></div>
                        <h3 style="color: #1e293b; margin-bottom: 8px;">Jadwal Tidak Ditemukan</h3>
                        <p>Belum ada jadwal yang ditemukan untuk kata kunci <strong>"<?php echo htmlspecialchars($search_query); ?>"</strong>.</p>
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
                                    <th>Nama Akses</th>
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
                                    <td style="color: #64748b; font-size: 0.85rem; display: flex; align-items: center; gap: 6px;">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block; vertical-align:middle; line-height:1;"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg> <?php echo htmlspecialchars($s['nama_guru']); ?>
                                    </td>
                                </tr>
                                <?php
    endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php
endif; ?>
            </div>
        </div>
    </main>
</div>

</body>
</html>

