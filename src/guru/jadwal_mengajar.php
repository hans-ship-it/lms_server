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
        *, *::before, *::after { box-sizing: border-box; }
        body { overflow-x: hidden; }
        .main-content { background: #f5f7fb !important; padding: 0 !important; }
        .page-hero {
            background: linear-gradient(135deg, #1e1b4b 0%, #312e81 50%, #4338ca 100%);
            padding: 2.5rem 3rem 5rem; position: relative; overflow: hidden;
        }
        .page-hero::before { content:''; position:absolute; right:-60px; top:-60px; width:250px; height:250px; background:rgba(255,255,255,0.07); border-radius:50%; }
        .page-hero h1 { color:#fff; font-size:1.6rem; font-weight:700; margin:0 0 0.4rem; }
        .page-hero p  { color:rgba(255,255,255,0.8); margin:0; font-size:0.95rem; }
        .page-content { position:relative; margin-top:-2.5rem; padding:0 3rem 3rem; z-index:10; }
        .db-section { background:#fff; border:1px solid #e8edf5; border-radius:14px; overflow:hidden; }


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
        <div class="page-hero">
            <h1>
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:middle;margin-right:8px;"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                Jadwal Mengajar Anda
            </h1>
            <p>Jadwal yang telah diatur oleh Admin untuk Anda penuhi.</p>
        </div>

        <div class="page-content">
            <div class="db-section">
                <form method="GET" class="filter-bar">
                    <input type="text" name="q" value="<?php echo htmlspecialchars($search_query); ?>" placeholder="Cari jadwal berdasarkan kata kunci..." class="filter-input">
                    <button type="submit" class="btn-filter">Cari Jadwal</button>
                    <?php if (!empty($search_query)): ?>
                        <a href="jadwal_mengajar.php" class="btn-filter secondary">Reset</a>
                    <?php endif; ?>
                </form>

                <?php if (empty($schedules)): ?>
                    <div class="empty-state">
                        <h3 style="color: #1e293b; margin-bottom: 8px;">Jadwal Tidak Ditemukan</h3>
                        <p>Belum ada jadwal yang ditemukan untuk kata kunci <strong>"<?php echo htmlspecialchars($search_query); ?>"</strong>.</p>
                    </div>
                <?php else: ?>
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
                                    <td><span style="color: #64748b; font-size: 0.88rem; font-weight: 500;"><?php echo htmlspecialchars($s['waktu']); ?></span></td>
                                    <td><span class="badge-kelas">Kelas <?php echo htmlspecialchars($s['kelas']); ?></span></td>
                                    <td><strong style="color: #0f172a;"><?php echo htmlspecialchars($s['mata_pelajaran']); ?></strong></td>
                                    <td style="color: #64748b; font-size: 0.85rem;"><?php echo htmlspecialchars($s['nama_guru']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div><!-- end page-content -->
    </main>
</div>

</body>
</html>

