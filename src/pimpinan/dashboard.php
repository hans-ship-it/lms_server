<?php
// src/pimpinan/dashboard.php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['kepsek', 'wakasek'])) {
    header("Location: ../../login.php");
    exit;
}

$role_display = $_SESSION['role'] === 'kepsek' ? 'Kepala Sekolah' : 'Wakil Kepala Sekolah';

$stats = [];
$stats['guru'] = $pdo->query("SELECT COUNT(*) FROM users WHERE role='guru'")->fetchColumn();
$stats['siswa'] = $pdo->query("SELECT COUNT(*) FROM users WHERE role='siswa'")->fetchColumn();
// Try to get class count, if classes table doesn't exist yet it might fail, so we catch it
try {
    $stats['classes'] = $pdo->query("SELECT COUNT(*) FROM classes")->fetchColumn();
}
catch (PDOException $e) {
    $stats['classes'] = 0;
}
$stats['news'] = $pdo->query("SELECT COUNT(*) FROM news")->fetchColumn();

// Get recent news
$recent_stmt = $pdo->query("SELECT title, created_at FROM news ORDER BY created_at DESC LIMIT 5");
$recent_news = $recent_stmt->fetchAll();

// Get tracer stats
$tracer_stats = [];
try {
    $ts_stmt = $pdo->query("SELECT kegiatan, COUNT(*) as count FROM tracer_study GROUP BY kegiatan");
    while ($row = $ts_stmt->fetch()) {
        $tracer_stats[$row['kegiatan']] = $row['count'];
    }
} catch (PDOException $e) {}

// Indonesian Date
$days = ['Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'];
$months = ['January' => 'Januari', 'February' => 'Februari', 'March' => 'Maret', 'April' => 'April', 'May' => 'Mei', 'June' => 'Juni', 'July' => 'Juli', 'August' => 'Agustus', 'September' => 'September', 'October' => 'Oktober', 'November' => 'November', 'December' => 'Desember'];
$dayName = $days[date('l')] ?? date('l');
$monthName = $months[date('F')] ?? date('F');
$dateStr = $dayName . ', ' . date('d') . ' ' . $monthName . ' ' . date('Y');

$hour = (int)date('H');
if ($hour < 11)
    $greeting = "Selamat Pagi";
elseif ($hour < 15)
    $greeting = "Selamat Siang";
elseif ($hour < 18)
    $greeting = "Selamat Sore";
else
    $greeting = "Selamat Malam";
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Pimpinan</title>
    <link rel="stylesheet" href="/public/assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .main-content {
            max-width: 100% !important;
            background: #f5f7fb !important;
            padding: 0 !important;
        }
        
        /* Hero pimpinan: navy + aksen emas (formal, beda dari admin slate) */
        .db-hero {
            position: relative;
            background: linear-gradient(135deg, #172554 0%, #1e3a8a 48%, #312e81 100%);
            color: white;
            padding: 2.5rem 3rem 5.5rem 5rem;
            width: 100%;
            border-bottom-right-radius: 60px;
            overflow: hidden;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }
        
        .db-hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            pointer-events: none;
        }
        .db-hero::after {
            content: '';
            position: absolute;
            width: 500px; height: 500px;
            top: -250px; right: -100px;
            background: radial-gradient(circle, rgba(250, 204, 21, 0.2) 0%, transparent 58%);
            border-radius: 50%;
            pointer-events: none;
        }
        .hero-inner {
            position: relative; z-index: 2;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .hero-inner h1 {
            font-size: 1.65rem;
            font-weight: 800;
            color: #fff;
            letter-spacing: -0.03em;
            margin-bottom: 0.35rem;
        }
        .hero-sub { color: rgba(255,255,255,0.55); font-size: 0.88rem; }
        .hero-date {
            color: rgba(255,255,255,0.45);
            font-size: 0.85rem;
            text-align: right;
            font-weight: 500;
        }

        .db-content {
            position: relative;
            margin-top: -3rem;
            padding: 0 3rem 3rem;
            z-index: 10;
        }
        .stat-bar {
            display: flex;
            background: #fff;
            border-radius: 14px;
            border: 1px solid #e8edf5;
            margin-bottom: 28px;
            overflow: hidden;
            animation: fade-up 0.4s ease-out 0.05s both;
        }
        .stat-item {
            flex: 1;
            padding: 18px 20px;
            display: flex;
            align-items: center;
            gap: 14px;
            position: relative;
        }
        .stat-item + .stat-item::before {
            content: '';
            position: absolute;
            left: 0; top: 20%; height: 60%;
            width: 1px;
            background: #e8edf5;
        }
        .stat-ico {
            width: 40px; height: 40px;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
            background: #f8fafc;
            color: #64748b;
        }
        .stat-ico.c1 { background: #e0f2fe; color: #0284c7; }
        .stat-ico.c2 { background: #e0e7ff; color: #4f46e5; }
        .stat-ico.c3 { background: #d1fae5; color: #059669; }
        .stat-ico.c4 { background: #fef3c7; color: #d97706; }
        .stat-num {
            font-size: 1.7rem;
            font-weight: 900;
            letter-spacing: -0.03em;
            line-height: 1;
        }
        .stat-item.c1 .stat-num { color: #0284c7; }
        .stat-item.c2 .stat-num { color: #4f46e5; }
        .stat-item.c3 .stat-num { color: #059669; }
        .stat-item.c4 .stat-num { color: #d97706; }
        .stat-lbl {
            font-size: 0.75rem;
            font-weight: 600;
            color: #64748b;
            margin-top: 2px;
        }
        @keyframes fade-up {
            from { opacity: 0; transform: translateY(16px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .db-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            animation: fade-up 0.4s ease-out 0.3s both;
        }
        .db-section {
            background: #fff;
            border-radius: 14px;
            border: 1px solid #e8edf5;
            overflow: hidden;
            animation: fade-up 0.4s ease-out 0.3s both;
        }
        .db-section-head {
            padding: 14px 20px;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .db-section-title {
            font-size: 0.78rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #64748b;
        }

        /* Table */
        .user-table { width: 100%; border-collapse: collapse; }
        .user-table th {
            text-align: left;
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #94a3b8;
            padding: 12px 20px;
            background: #f8fafc;
            border-bottom: 1px solid #e8edf5;
        }
        .user-table td {
            padding: 12px 20px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 0.88rem;
        }
        .user-table tr:last-child td { border-bottom: none; }
        .user-table .name { font-weight: 600; color: #1e293b; }
        .date-muted { color: #94a3b8; font-size: 0.82rem; }

        /* Quick Actions */
        .qa-list { display: flex; flex-direction: column; gap: 0; }
        .qa-item {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 14px 20px;
            border-bottom: 1px solid #f8fafc;
            text-decoration: none;
            color: inherit;
            transition: all 0.15s;
            background: #fff;
        }
        .qa-item:hover { background: #fafbff; }
        .qa-ico {
            width: 36px; height: 36px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.15rem;
            flex-shrink: 0;
            background: #e0f2fe;
            color: #0284c7;
        }
        .qa-title { font-size: 0.88rem; font-weight: 600; color: #1e293b; }
        .qa-desc { font-size: 0.75rem; color: #94a3b8; margin-top: 2px; }
        .qa-arrow {
            margin-left: auto;
            color: #cbd5e1;
            font-size: 1.2rem;
            transition: all 0.2s;
        }
        .qa-item:hover .qa-arrow { color: #0ea5e9; transform: translateX(3px); }

        @media (max-width: 900px) {
            .stat-bar { flex-wrap: wrap; }
            .stat-item + .stat-item::before { display: none; }
            .stat-item { border-bottom: 1px solid #f1f5f9; min-width: 45%; }
            .db-grid { grid-template-columns: 1fr; }
            
            .db-hero { 
                margin: -5rem -1.5rem 2rem -1.5rem;
                padding: 5rem 1.5rem 4rem 1.5rem;
                width: calc(100% + 3rem);
                border-bottom-right-radius: 40px;
            }
            
            .db-content { 
                margin-top: -3rem;
                padding: 0 1.5rem 2rem; 
            }
        }
    </style>
</head>
<body>

<div class="app-container">
    <?php include '../templates/sidebar.php'; ?>
    
    <main class="main-content">
        <div class="db-hero">
            <div class="hero-inner">
                <div>
                    <h1><?php echo $greeting; ?>, <?php echo htmlspecialchars($_SESSION['full_name']); ?>!</h1>
                    <p class="hero-sub">Dashboard Monitoring <?php echo $role_display; ?></p>
                </div>
                <div class="hero-date"><?php echo $dateStr; ?></div>
            </div>
        </div>

        <div class="db-content">
            <div class="stat-bar">
                <div class="stat-item c1">
                    <div class="stat-ico c1">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                    </div>
                    <div>
                        <div class="stat-num"><?php echo $stats['guru']; ?></div>
                        <div class="stat-lbl">Total Guru</div>
                    </div>
                </div>
                <div class="stat-item c2">
                    <div class="stat-ico c2">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                    </div>
                    <div>
                        <div class="stat-num"><?php echo $stats['siswa']; ?></div>
                        <div class="stat-lbl">Total Siswa</div>
                    </div>
                </div>
                <div class="stat-item c3">
                    <div class="stat-ico c3">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path></svg>
                    </div>
                    <div>
                        <div class="stat-num"><?php echo $stats['classes']; ?></div>
                        <div class="stat-lbl">Total Kelas</div>
                    </div>
                </div>
                <div class="stat-item c4">
                    <div class="stat-ico c4">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                    </div>
                    <div>
                        <div class="stat-num"><?php echo $stats['news']; ?></div>
                        <div class="stat-lbl">Berita Sekolah</div>
                    </div>
                </div>
            </div>

            <div class="db-grid">

                <!-- Quick Actions -->
                <div class="db-section">
                    <div class="db-section-head">
                        <span class="db-section-title">Akses Cepat Monitoring</span>
                    </div>
                    <div class="qa-list">
                        <a href="guru_list.php" class="qa-item">
                            <div class="qa-ico"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block; vertical-align:middle; line-height:1;"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/><path d="M12 14h.01"/><path d="M16 14h.01"/><path d="M8 14h.01"/></svg></div>
                            <div>
                                <div class="qa-title">Data Guru</div>
                                <div class="qa-desc">Pantau daftar dan jadwal guru</div>
                            </div>
                            <span class="qa-arrow">›</span>
                        </a>
                        <a href="siswa_list.php" class="qa-item">
                            <div class="qa-ico"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block; vertical-align:middle; line-height:1;"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg></div>
                            <div>
                                <div class="qa-title">Data Siswa</div>
                                <div class="qa-desc">Pantau daftar siswa per kelas</div>
                            </div>
                            <span class="qa-arrow">›</span>
                        </a>
                        <a href="jadwal_sekolah.php" class="qa-item">
                            <div class="qa-ico"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block; vertical-align:middle; line-height:1;"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg></div>
                            <div>
                                <div class="qa-title">Jadwal Sekolah</div>
                                <div class="qa-desc">Lihat jadwal kegiatan belajar mengajar</div>
                            </div>
                            <span class="qa-arrow">›</span>
                        </a>
                    </div>
                </div>

                <!-- Recent News -->
                <div class="db-section">
                    <div class="db-section-head">
                        <span class="db-section-title">Berita & Informasi Terbaru</span>
                    </div>
                    <table class="user-table">
                        <thead>
                            <tr>
                                <th>Judul Informasi</th>
                                <th>Tanggal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recent_news)): ?>
                            <tr>
                                <td colspan="2" style="text-align:center; color:#94a3b8;">Belum ada informasi.</td>
                            </tr>
                            <?php else: ?>
                                <?php foreach ($recent_news as $n): ?>
                                <tr>
                                    <td class="name"><?php echo htmlspecialchars($n['title']); ?></td>
                                    <td class="date-muted"><?php echo date('d M Y', strtotime($n['created_at'])); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Tracer Study Chart -->
                <div class="db-section" style="grid-column: 1 / -1;">
                    <div class="db-section-head">
                        <span class="db-section-title">Statistik Penelusuran Alumni (Tracer Study)</span>
                    </div>
                    <div style="height: 280px; display: flex; justify-content: center; padding: 20px;">
                        <canvas id="tracerChart"></canvas>
                    </div>
                </div>

            </div>
        </div>
    </main>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const ctx = document.getElementById('tracerChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: [
                    'Kuliah (<?php echo $tracer_stats['Kuliah'] ?? 0; ?>)', 
                    'Kerja (<?php echo $tracer_stats['Kerja'] ?? 0; ?>)', 
                    'Wirausaha (<?php echo $tracer_stats['Wirausaha'] ?? 0; ?>)', 
                    'Belum/Tidak Bekerja (<?php echo $tracer_stats["Belum/Tidak Bekerja"] ?? 0; ?>)'
                ],
                datasets: [{
                    data: [
                        <?php echo $tracer_stats['Kuliah'] ?? 0; ?>,
                        <?php echo $tracer_stats['Kerja'] ?? 0; ?>,
                        <?php echo $tracer_stats['Wirausaha'] ?? 0; ?>,
                        <?php echo $tracer_stats["Belum/Tidak Bekerja"] ?? 0; ?>
                    ],
                    backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#ef4444'],
                    borderWidth: 0,
                    hoverOffset: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'right', labels: { font: { family: "'Plus Jakarta Sans', sans-serif" } } }
                },
                cutout: '70%'
            }
        });
    }
});
</script>

</body>
</html>
