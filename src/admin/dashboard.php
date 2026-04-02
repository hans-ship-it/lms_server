<?php
// src/admin/dashboard.php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

$stats = [];
$stats['users']       = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$stats['guru']        = $pdo->query("SELECT COUNT(*) FROM users WHERE role='guru'")->fetchColumn();
$stats['siswa']       = $pdo->query("SELECT COUNT(*) FROM users WHERE role='siswa'")->fetchColumn();
$stats['news']        = $pdo->query("SELECT COUNT(*) FROM news")->fetchColumn();
$stats['materials']   = $pdo->query("SELECT COUNT(*) FROM materials")->fetchColumn();
$stats['assignments'] = $pdo->query("SELECT COUNT(*) FROM assignments")->fetchColumn();

// Analytics: Distribusi pengaduan per kategori
$recent_stmt = $pdo->query("SELECT full_name, role, created_at FROM users ORDER BY created_at DESC LIMIT 5");
$recent_users = $recent_stmt->fetchAll();

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
    <title>Dashboard Admin</title>
    <link rel="stylesheet" href="/public/assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* * { font-family: 'Inter', system-ui, -apple-system, sans-serif; } */
        
        /* RESTORED: Full width layout for Admin Dashboard as requested */
        .main-content {
            max-width: 100% !important;
            background: #f5f7fb !important;
            padding: 0 !important;
        }
        
        /* Simplified Hero to fit the full-width container naturally */
        /* Hero admin: slate / authority (tidak memakai --primary-gradient agar beda dari role lain) */
        .db-hero {
            position: relative;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 42%, #334155 100%);
            color: white;
            padding: 2.5rem 3rem 5.5rem 5rem; /* increased left padding to clear toggle button */
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
            background: radial-gradient(circle, rgba(56, 189, 248, 0.22) 0%, transparent 60%);
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
            padding: 0 3rem 3rem; /* Original padding restored */
            z-index: 10;
        }
        .db-stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
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
        .db-stat:nth-child(3) { animation-delay: 0.15s; }
        .db-stat:nth-child(4) { animation-delay: 0.2s; }
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
        .db-stat.c1::after { background: linear-gradient(90deg, #0ea5e9, #7dd3fc); }
        .db-stat.c2::after { background: linear-gradient(90deg, #6366f1, #a5b4fc); }
        .db-stat.c3::after { background: linear-gradient(90deg, #10b981, #6ee7b7); }
        .db-stat.c4::after { background: linear-gradient(90deg, #f59e0b, #fde68a); }
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
        .db-stat.c1 .num { color: #0284c7; }
        .db-stat.c2 .num { color: #4f46e5; }
        .db-stat.c3 .num { color: #059669; }
        .db-stat.c4 .num { color: #d97706; }
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
            animation: fade-up 0.4s ease-out 0.3s both;
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

        /* Table */
        .user-table { width: 100%; border-collapse: collapse; }
        .user-table th {
            text-align: left;
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #94a3b8;
            padding: 0 0 12px;
        }
        .user-table td {
            padding: 12px 0;
            border-top: 1px solid #f1f5f9;
            font-size: 0.88rem;
        }
        .user-table .name { font-weight: 600; color: #1e293b; }
        .role-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 6px;
            font-size: 0.72rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .role-guru { background: #ede9fe; color: #5b21b6; }
        .role-siswa { background: #d1fae5; color: #065f46; }
        .role-admin { background: #e0f2fe; color: #0c4a6e; }
        .role-osis { background: #fef3c7; color: #92400e; }
        .date-muted { color: #94a3b8; font-size: 0.82rem; }

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
            background: #e0f2fe;
            border-color: #bae6fd;
            transform: translateX(4px);
            box-shadow: 0 2px 12px rgba(14,165,233,0.06);
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
        .qa-item:nth-child(1) .qa-ico { background: #dbeafe; }
        .qa-item:nth-child(2) .qa-ico { background: #fef3c7; }
        .qa-item:nth-child(3) .qa-ico { background: #ede9fe; }
        .qa-item:nth-child(4) .qa-ico { background: #d1fae5; }
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
            .db-stats { grid-template-columns: repeat(2, 1fr); }
            .db-grid { grid-template-columns: 1fr; }
            
            /* Fix Hero on Mobile */
            .db-hero { 
                margin: -5rem -1.5rem 2rem -1.5rem; /* Match mobile .main-content padding */
                padding: 5rem 1.5rem 4rem 1.5rem;
                width: calc(100% + 3rem); /* Match negative margins (1.5 + 1.5) */
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
                    <h1><?php echo $greeting; ?>, Administrator!</h1>
                    <p class="hero-sub">Overview sistem dan pengelolaan sekolah</p>
                </div>
                <div class="hero-date"><?php echo $dateStr; ?></div>
            </div>
        </div>

        <div class="db-content">

            <div class="db-stats">
                <div class="db-stat c1">
                    <div class="num"><?php echo $stats['users']; ?></div>
                    <div class="lbl">Total Pengguna</div>
                </div>
                <div class="db-stat c2">
                    <div class="num"><?php echo $stats['guru']; ?></div>
                    <div class="lbl">Jumlah Guru</div>
                </div>
                <div class="db-stat c3">
                    <div class="num"><?php echo $stats['siswa']; ?></div>
                    <div class="lbl">Jumlah Siswa</div>
                </div>
                <div class="db-stat c4">
                    <div class="num"><?php echo $stats['news']; ?></div>
                    <div class="lbl">Berita Terbit</div>
                </div>
            </div>

            <div class="db-grid">

                <!-- Recent Users -->
                <div class="db-panel">
                    <h3>Pengguna Terbaru</h3>
                    <table class="user-table">
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Role</th>
                                <th>Bergabung</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_users as $u): ?>
                            <tr>
                                <td class="name"><?php echo htmlspecialchars($u['full_name']); ?></td>
                                <td>
                                    <span class="role-badge role-<?php echo $u['role']; ?>">
                                        <?php echo $u['role']; ?>
                                    </span>
                                </td>
                                <td class="date-muted"><?php echo date('d M Y', strtotime($u['created_at'])); ?></td>
                            </tr>
                            <?php
endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Quick Actions -->
                <div class="db-panel">
                    <h3>Menu Cepat</h3>
                    <div class="qa-list">
                        <a href="manage_users.php" class="qa-item">
                            <div class="qa-ico"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block; vertical-align:middle; line-height:1;"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></div>
                            <div>
                                <div class="qa-title">Kelola Pengguna</div>
                                <div class="qa-desc">Tambah, edit, atau hapus akun</div>
                            </div>
                            <span class="qa-arrow">›</span>
                        </a>
                        <a href="manage_news.php" class="qa-item">
                            <div class="qa-ico"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block; vertical-align:middle; line-height:1;"><path d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 1-2 2Zm0 0a2 2 0 0 1-2-2v-9c0-1.1.9-2 2-2h2"/><path d="M18 14h-8"/><path d="M15 18h-5"/><path d="M10 6h8v4h-8V6Z"/></svg></div>
                            <div>
                                <div class="qa-title">Kelola Berita</div>
                                <div class="qa-desc">Tulis dan atur berita sekolah</div>
                            </div>
                            <span class="qa-arrow">›</span>
                        </a>
                        <a href="../profile.php" class="qa-item">
                            <div class="qa-ico">&#9881;&#65039;</div>
                            <div>
                                <div class="qa-title">Pengaturan Profil</div>
                                <div class="qa-desc">Ubah data diri dan password</div>
                            </div>
                            <span class="qa-arrow">&#8250;</span>
                        </a>
                        <a href="manage_classes.php" class="qa-item">
                            <div class="qa-ico">&#127979;</div>
                            <div>
                                <div class="qa-title">Kelola Kelas</div>
                                <div class="qa-desc">Buat dan atur kelas untuk guru</div>
                            </div>
                            <span class="qa-arrow">&#8250;</span>
                        </a>
                        <a href="manage_schedules.php" class="qa-item">
                            <div class="qa-ico"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block; vertical-align:middle; line-height:1;"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg></div>
                            <div>
                                <div class="qa-title">Kelola Jadwal</div>
                                <div class="qa-desc">Upload jadwal pelajaran via Excel</div>
                            </div>
                            <span class="qa-arrow">&#8250;</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        </div>
    </main>
</div>


</body>
</html>
