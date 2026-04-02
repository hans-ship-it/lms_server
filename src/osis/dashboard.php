<?php
// src/osis/dashboard.php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'osis') {
    header("Location: ../../login.php");
    exit;
}

$hour = (int)date('H');
if ($hour < 11)
    $greeting = "Selamat Pagi";
elseif ($hour < 15)
    $greeting = "Selamat Siang";
elseif ($hour < 18)
    $greeting = "Selamat Sore";
else
    $greeting = "Selamat Malam";

// Stats
$news_count = $pdo->query("SELECT COUNT(*) FROM news")->fetchColumn();
$news_published = $news_count;

// Recent News
$recent_news = $pdo->query("SELECT title, created_at FROM news ORDER BY created_at DESC LIMIT 5")->fetchAll();

// Indonesian Date
$days = ['Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'];
$months = ['January' => 'Januari', 'February' => 'Februari', 'March' => 'Maret', 'April' => 'April', 'May' => 'Mei', 'June' => 'Juni', 'July' => 'Juli', 'August' => 'Agustus', 'September' => 'September', 'October' => 'Oktober', 'November' => 'November', 'December' => 'Desember'];
$dayName = $days[date('l')] ?? date('l');
$monthName = $months[date('F')] ?? date('F');
$dateStr = $dayName . ', ' . date('d') . ' ' . $monthName . ' ' . date('Y');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard OSIS</title>
    <link rel="stylesheet" href="/public/assets/css/style.css">
    <!-- <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet"> -->
    <style>
        /* * { font-family: 'Inter', system-ui, -apple-system, sans-serif; } */
        .main-content {
            max-width: 100% !important;
            background: #f5f7fb !important;
            padding: 0 !important;
        }

        /* Hero OSIS: amber / oranye organisasi (tetap hangat, jelas beda dari biru siswa) */
        .db-hero {
            position: relative;
            background: linear-gradient(135deg, #9a3412 0%, #c2410c 40%, #ea580c 100%);
            padding: 2.5rem 3rem 5.5rem 5rem;
            overflow: hidden;
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
            background: radial-gradient(circle, rgba(253, 224, 71, 0.3) 0%, transparent 60%);
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
        .db-stats {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
            margin-bottom: 20px;
        }
        .db-stat {
            background: #fff;
            border-radius: 16px;
            padding: 22px 24px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04), 0 6px 24px rgba(0,0,0,0.04);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            animation: fade-up 0.4s ease-out both;
        }
        .db-stat:nth-child(1) { animation-delay: 0.05s; }
        .db-stat:nth-child(2) { animation-delay: 0.1s; }
        @keyframes fade-up {
            from { opacity: 0; transform: translateY(16px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .db-stat::after {
            content: '';
            position: absolute;
            bottom: 0; left: 0;
            width: 100%; height: 3px;
        }
        .db-stat.c-amber::after  { background: linear-gradient(90deg, #f59e0b, #fde68a); }
        .db-stat.c-green::after  { background: linear-gradient(90deg, #10b981, #6ee7b7); }
        .db-stat:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 30px rgba(0,0,0,0.08);
        }
        .db-stat .num {
            font-size: 2.2rem;
            font-weight: 900;
            line-height: 1;
            margin-bottom: 6px;
            letter-spacing: -0.03em;
        }
        .db-stat.c-amber .num { color: #d97706; }
        .db-stat.c-green .num { color: #059669; }
        .db-stat .lbl {
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #94a3b8;
        }

        .db-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            animation: fade-up 0.4s ease-out 0.2s both;
        }
        .db-panel {
            background: #fff;
            border-radius: 18px;
            padding: 26px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04), 0 6px 24px rgba(0,0,0,0.04);
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        .db-panel h3 {
            font-size: 0.82rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #94a3b8;
            margin-bottom: 18px;
            padding-bottom: 14px;
            border-bottom: 1px solid #f1f5f9;
        }

        /* Quick Actions */
        .qa-list { display: flex; flex-direction: column; gap: 8px; }
        .qa-item {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 14px 16px;
            border-radius: 12px;
            background: #f8fafc;
            border: 1px solid #eef2f7;
            text-decoration: none;
            color: inherit;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .qa-item:hover {
            background: #fffbeb;
            border-color: #fde68a;
            transform: translateX(4px);
            box-shadow: 0 2px 12px rgba(245,158,11,0.06);
        }
        .qa-ico {
            width: 42px; height: 42px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.15rem;
            flex-shrink: 0;
        }
        .qa-item:nth-child(1) .qa-ico { background: #fef3c7; }
        .qa-item:nth-child(2) .qa-ico { background: #d1fae5; }
        .qa-title { font-size: 0.88rem; font-weight: 600; color: #1e293b; }
        .qa-desc { font-size: 0.75rem; color: #94a3b8; margin-top: 2px; }
        .qa-arrow {
            margin-left: auto;
            color: #cbd5e1;
            font-size: 1.2rem;
            transition: all 0.2s;
        }
        .qa-item:hover .qa-arrow { color: #f59e0b; transform: translateX(3px); }

        /* News List */
        .news-row {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 13px 0;
            border-bottom: 1px solid #f1f5f9;
        }
        .news-row:last-child { border-bottom: none; }
        .news-dot {
            width: 10px; height: 10px;
            border-radius: 50%;
            flex-shrink: 0;
        }
        .news-info { flex: 1; min-width: 0; }
        .news-title {
            font-size: 0.88rem;
            font-weight: 600;
            color: #1e293b;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .news-date { font-size: 0.75rem; color: #94a3b8; margin-top: 2px; }

        @media (max-width: 900px) {
            .db-stats { grid-template-columns: 1fr; }
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
                    <p class="hero-sub">Portal informasi dan berita sekolah OSIS</p>
                </div>
                <div class="hero-date"><?php echo $dateStr; ?></div>
            </div>
        </div>

        <div class="db-content">

            <div class="db-stats">
                <div class="db-stat c-amber">
                    <div class="num"><?php echo $news_count; ?></div>
                    <div class="lbl">Total Berita</div>
                </div>
                <div class="db-stat c-green">
                    <div class="num"><?php echo $news_published; ?></div>
                    <div class="lbl">Berita Terbit</div>
                </div>
            </div>

            <div class="db-grid">

                <!-- Quick Actions -->
                <div class="db-panel">
                    <h3>Menu Cepat</h3>
                    <div class="qa-list">
                        <a href="manage_news.php" class="qa-item">
                            <div class="qa-ico"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block; vertical-align:middle; line-height:1;"><path d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 1-2 2Zm0 0a2 2 0 0 1-2-2v-9c0-1.1.9-2 2-2h2"/><path d="M18 14h-8"/><path d="M15 18h-5"/><path d="M10 6h8v4h-8V6Z"/></svg></div>
                            <div>
                                <div class="qa-title">Kelola Berita</div>
                                <div class="qa-desc">Tulis, edit, dan publikasi berita sekolah</div>
                            </div>
                            <span class="qa-arrow">›</span>
                        </a>
                        <a href="../profile.php" class="qa-item">
                            <div class="qa-ico"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block; vertical-align:middle; line-height:1;"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></div>
                            <div>
                                <div class="qa-title">Profil Saya</div>
                                <div class="qa-desc">Ubah data diri dan password</div>
                            </div>
                            <span class="qa-arrow">›</span>
                        </a>
                    </div>
                </div>

                <!-- Recent News -->
                <div class="db-panel">
                    <h3>Berita Terbaru</h3>
                    <?php if (empty($recent_news)): ?>
                        <div style="text-align:center; padding:2.5rem 1rem; color:#94a3b8;">
                            <p style="font-size:2.5rem; margin-bottom:10px;"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block; vertical-align:middle; line-height:1;"><path d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 1-2 2Zm0 0a2 2 0 0 1-2-2v-9c0-1.1.9-2 2-2h2"/><path d="M18 14h-8"/><path d="M15 18h-5"/><path d="M10 6h8v4h-8V6Z"/></svg></p>
                            <p style="margin-bottom:8px;">Belum ada berita.</p>
                            <a href="manage_news.php" style="color:#f59e0b; font-weight:600; text-decoration:none;">+ Tulis Berita Pertama</a>
                        </div>
                    <?php
else: ?>
                        <?php foreach ($recent_news as $n): ?>
                        <div class="news-row">
                            <div class="news-dot" style="background: #f59e0b;"></div>
                            <div class="news-info">
                                <div class="news-title"><?php echo htmlspecialchars($n['title']); ?></div>
                                <div class="news-date"><?php echo date('d M Y', strtotime($n['created_at'])); ?></div>
                            </div>
                        </div>
                        <?php
    endforeach; ?>
                    <?php
endif; ?>
                </div>
            </div>

        </div>
    </main>
</div>

</body>
</html>
