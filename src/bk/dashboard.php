<?php
// src/bk/dashboard.php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'bk') {
    header("Location: ../../login.php");
    exit;
}

// Stats
$total_tickets    = $pdo->query("SELECT COUNT(*) FROM pengaduan")->fetchColumn();
$pending_tickets  = $pdo->query("SELECT COUNT(*) FROM pengaduan WHERE status='Pending'")->fetchColumn();
$resolved_tickets = $pdo->query("SELECT COUNT(*) FROM pengaduan WHERE status='Selesai'")->fetchColumn();
$bullying_tickets = $pdo->query("SELECT COUNT(*) FROM pengaduan WHERE kategori='Bullying' AND status!='Selesai'")->fetchColumn();

// Chart: Tiket per kategori
$kategori_stats = $pdo->query("SELECT kategori, COUNT(*) as total FROM pengaduan GROUP BY kategori ORDER BY total DESC")->fetchAll();

// Chart: Status distribusi
$status_stats = $pdo->query("SELECT status, COUNT(*) as total FROM pengaduan GROUP BY status")->fetchAll();

// Chart: Tren tiket 6 bulan terakhir
$tren_stats = $pdo->query("
    SELECT DATE_FORMAT(created_at,'%b %Y') as bulan, COUNT(*) as total
    FROM pengaduan
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY YEAR(created_at), MONTH(created_at), bulan
    ORDER BY YEAR(created_at) ASC, MONTH(created_at) ASC
")->fetchAll();

// Recent Tickets
$recent_tickets = $pdo->query("SELECT * FROM pengaduan ORDER BY created_at DESC LIMIT 5")->fetchAll();

// Indonesian Date
$days = ['Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'];
$months = ['January' => 'Januari', 'February' => 'Februari', 'March' => 'Maret', 'April' => 'April', 'May' => 'Mei', 'June' => 'Juni', 'July' => 'Juli', 'August' => 'Agustus', 'September' => 'September', 'October' => 'Oktober', 'November' => 'November', 'December' => 'Desember'];
$dayName = $days[date('l')] ?? date('l');
$monthName = $months[date('F')] ?? date('F');
$dateStr = $dayName . ', ' . date('d') . ' ' . $monthName . ' ' . date('Y');

$hour = (int)date('H');
if ($hour < 11) $greeting = "Selamat Pagi";
elseif ($hour < 15) $greeting = "Selamat Siang";
elseif ($hour < 18) $greeting = "Selamat Sore";
else $greeting = "Selamat Malam";
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Guru BK</title>
    <link rel="stylesheet" href="/public/assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .main-content { max-width: 100% !important; background: #f5f7fb !important; padding: 0 !important; }
        /* Hero BK: ungu / violet (layanan konseling, beda dari indigo & pimpinan) */
        .db-hero {
            position: relative;
            background: linear-gradient(135deg, #4c1d95 0%, #6d28d9 45%, #7c3aed 100%);
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
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.04'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            pointer-events: none;
        }
        .db-hero::after {
            content: '';
            position: absolute;
            width: 480px; height: 480px;
            top: -220px; right: -90px;
            background: radial-gradient(circle, rgba(244, 114, 182, 0.22) 0%, transparent 60%);
            border-radius: 50%;
            pointer-events: none;
        }
        .hero-inner { position: relative; z-index: 2; display: flex; justify-content: space-between; align-items: center; }
        .hero-inner h1 { font-size: 1.65rem; font-weight: 800; color: #fff; letter-spacing: -0.03em; margin-bottom: 0.35rem; }
        .hero-sub { color: rgba(255,255,255,0.7); font-size: 0.88rem; }
        .hero-date { color: rgba(255,255,255,0.7); font-size: 0.85rem; text-align: right; font-weight: 500; }

        .db-content { position: relative; margin-top: -3rem; padding: 0 3rem 3rem; z-index: 10; }
        .db-stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 20px; }
        .db-stat { background: #fff; border-radius: 16px; padding: 22px 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.04), 0 6px 24px rgba(0,0,0,0.04); transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); position: relative; overflow: hidden; }
        .db-stat::after { content: ''; position: absolute; bottom: 0; left: 0; width: 100%; height: 3px; }
        .db-stat.c1::after { background: linear-gradient(90deg, #6366f1, #a5b4fc); }
        .db-stat.c2::after { background: linear-gradient(90deg, #f59e0b, #fde68a); }
        .db-stat.c3::after { background: linear-gradient(90deg, #10b981, #6ee7b7); }
        .db-stat.c4::after { background: linear-gradient(90deg, #ef4444, #fca5a5); }
        .db-stat .num { font-size: 2.2rem; font-weight: 900; line-height: 1; margin-bottom: 6px; letter-spacing: -0.03em; }
        .db-stat.c1 .num { color: #4f46e5; }
        .db-stat.c2 .num { color: #d97706; }
        .db-stat.c3 .num { color: #059669; }
        .db-stat.c4 .num { color: #dc2626; }
        .db-stat .lbl { font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.08em; color: #94a3b8; }

        .db-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .db-panel { background: #fff; border-radius: 18px; padding: 26px; box-shadow: 0 1px 3px rgba(0,0,0,0.04), 0 6px 24px rgba(0,0,0,0.04); overflow-x: auto; -webkit-overflow-scrolling: touch; }
        .db-panel h3 { font-size: 0.82rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: #94a3b8; margin-bottom: 18px; padding-bottom: 14px; border-bottom: 1px solid #f1f5f9; }

        .qa-list { display: flex; flex-direction: column; gap: 8px; }
        .qa-item { display: flex; align-items: center; gap: 16px; padding: 14px 16px; border-radius: 12px; background: #f8fafc; border: 1px solid #eef2f7; text-decoration: none; color: inherit; transition: all 0.25s; }
        .qa-item:hover { background: #e0e7ff; border-color: #c7d2fe; transform: translateX(4px); box-shadow: 0 2px 12px rgba(79,70,229,0.06); }
        .qa-ico { width: 42px; height: 42px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.15rem; flex-shrink: 0; background: #e0e7ff; color: #4f46e5; }
        .qa-title { font-size: 0.88rem; font-weight: 600; color: #1e293b; }
        .qa-desc { font-size: 0.75rem; color: #94a3b8; margin-top: 2px; }
        .qa-arrow { margin-left: auto; color: #cbd5e1; font-size: 1.2rem; transition: all 0.2s; }
        .qa-item:hover .qa-arrow { color: #4f46e5; transform: translateX(3px); }

        .ticket-row { display: flex; align-items: center; gap: 14px; padding: 12px 0; border-bottom: 1px solid #f1f5f9; text-decoration: none; color: inherit; transition: all 0.2s; }
        .ticket-row:last-child { border-bottom: none; }
        .ticket-row:hover { padding-left: 6px; }
        .ticket-info { flex: 1; min-width: 0; }
        .ticket-title { font-size: 0.88rem; font-weight: 600; color: #1e293b; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .ticket-meta { font-size: 0.75rem; color: #94a3b8; margin-top: 2px; }
        .badge-status { font-size: 0.7rem; font-weight: 700; padding: 4px 10px; border-radius: 6px; text-transform: uppercase; letter-spacing: 0.04em; white-space: nowrap; }
        .badge-pending { background: #fef3c7; color: #92400e; }
        .badge-proses { background: #dbeafe; color: #1e40af; }
        .badge-selesai { background: #d1fae5; color: #065f46; }

        @media (max-width: 900px) {
            .db-stats { grid-template-columns: repeat(2, 1fr); }
            .db-grid { grid-template-columns: 1fr; }
            .db-hero { padding: 2rem 1.5rem 5rem; }
            .db-content { padding: 0 1.5rem 2rem; }
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
                    <p class="hero-sub">Dashboard Monitoring Layanan Bimbingan Konseling (E-Counseling)</p>
                </div>
                <div class="hero-date"><?php echo $dateStr; ?></div>
            </div>
        </div>

        <div class="db-content">
            <div class="db-stats">
                <div class="db-stat c1">
                    <div class="num"><?php echo $total_tickets; ?></div>
                    <div class="lbl">Total Tiket</div>
                </div>
                <div class="db-stat c2">
                    <div class="num"><?php echo $pending_tickets; ?></div>
                    <div class="lbl">Tiket Pending</div>
                </div>
                <div class="db-stat c3">
                    <div class="num"><?php echo $resolved_tickets; ?></div>
                    <div class="lbl">Tiket Selesai</div>
                </div>
                <div class="db-stat c4">
                    <div class="num"><?php echo $bullying_tickets; ?></div>
                    <div class="lbl">Darurat (Bullying)</div>
                </div>
            </div>

            <div class="db-grid">
                <div class="db-panel">
                    <h3>Akses Cepat</h3>
                    <div class="qa-list">
                        <a href="pengaduan.php" class="qa-item">
                            <div class="qa-ico"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg></div>
                            <div>
                                <div class="qa-title">Kelola Laporan Siswa</div>
                                <div class="qa-desc">Tanggapi dan perbarui status laporan E-Counseling</div>
                            </div>
                            <span class="qa-arrow">›</span>
                        </a>
                        <a href="../profile.php" class="qa-item">
                            <div class="qa-ico"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></div>
                            <div>
                                <div class="qa-title">Profil Saya</div>
                                <div class="qa-desc">Kelola pengaturan akun</div>
                            </div>
                            <span class="qa-arrow">›</span>
                        </a>
                    </div>
                </div>

                <div class="db-panel">
                    <h3>Laporan Masuk Terbaru</h3>
                    <div class="qa-list">
                        <?php if (empty($recent_tickets)): ?>
                            <div style="text-align:center; padding: 2rem 0; color: #94a3b8;">Belum ada laporan masuk.</div>
                        <?php else: ?>
                            <?php foreach ($recent_tickets as $t): 
                                $badgeClass = 'badge-pending';
                                if ($t['status'] === 'Diproses') $badgeClass = 'badge-proses';
                                if ($t['status'] === 'Selesai') $badgeClass = 'badge-selesai';
                            ?>
                                <a href="pengaduan.php" class="ticket-row">
                                    <div class="ticket-info">
                                        <div class="ticket-title"><?php echo htmlspecialchars($t['nama_siswa'] ?: 'Siswa (Anonim)'); ?></div>
                                        <div class="ticket-meta"><?php echo htmlspecialchars($t['kategori']); ?> · <?php echo date('d M Y', strtotime($t['created_at'])); ?></div>
                                    </div>
                                    <span class="badge-status <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($t['status']); ?></span>
                                </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>


            <!-- ====== ANALYTICS CHARTS ====== -->
            <div style="margin-top:20px;">
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">

                    <!-- Kategori Bar Chart -->
                    <div class="db-panel">
                        <h3>Distribusi Kategori Pengaduan</h3>
                        <canvas id="chartKategori" height="220"></canvas>
                    </div>

                    <!-- Status Donut Chart -->
                    <div class="db-panel">
                        <h3>Status Tiket Overview</h3>
                        <canvas id="chartStatus" height="220"></canvas>
                    </div>

                </div>

                <!-- Tren Line Chart (full width) -->
                <div class="db-panel" style="margin-top:20px;">
                    <h3>Tren Tiket Masuk (6 Bulan Terakhir)</h3>
                    <canvas id="chartTren" height="120"></canvas>
                </div>
            </div>

        </div><!-- end .db-content -->
    </main>
</div><!-- end .app-container -->

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const palette = ['#6366f1','#f59e0b','#10b981','#ef4444','#3b82f6','#a855f7','#ec4899'];

// ── Kategori Bar Chart ─────────────────────────────────────
new Chart(document.getElementById('chartKategori'), {
    type: 'bar',
    data: {
        labels: <?php echo json_encode(array_column($kategori_stats, 'kategori')); ?>,
        datasets: [{
            label: 'Jumlah Tiket',
            data:  <?php echo json_encode(array_column($kategori_stats, 'total')); ?>,
            backgroundColor: palette,
            borderRadius: 8,
            borderSkipped: false,
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, ticks: { stepSize: 1 }, grid: { color: '#f1f5f9' } },
            x: { grid: { display: false } }
        }
    }
});

// ── Status Donut Chart ─────────────────────────────────────
new Chart(document.getElementById('chartStatus'), {
    type: 'doughnut',
    data: {
        labels: <?php echo json_encode(array_column($status_stats, 'status')); ?>,
        datasets: [{
            data: <?php echo json_encode(array_column($status_stats, 'total')); ?>,
            backgroundColor: ['#fbbf24','#60a5fa','#34d399'],
            borderWidth: 3,
            borderColor: '#fff',
            hoverOffset: 10,
        }]
    },
    options: {
        responsive: true,
        cutout: '65%',
        plugins: {
            legend: { position: 'bottom', labels: { padding: 16, boxWidth: 12 } }
        }
    }
});

// ── Tren Line Chart ────────────────────────────────────────
new Chart(document.getElementById('chartTren'), {
    type: 'line',
    data: {
        labels: <?php echo json_encode(array_column($tren_stats, 'bulan')); ?>,
        datasets: [{
            label: 'Tiket Masuk',
            data:  <?php echo json_encode(array_column($tren_stats, 'total')); ?>,
            borderColor: '#6366f1',
            backgroundColor: 'rgba(99,102,241,0.1)',
            tension: 0.4,
            fill: true,
            pointBackgroundColor: '#6366f1',
            pointRadius: 5,
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, ticks: { stepSize: 1 }, grid: { color: '#f1f5f9' } },
            x: { grid: { display: false } }
        }
    }
});
</script>
</body>
</html>
