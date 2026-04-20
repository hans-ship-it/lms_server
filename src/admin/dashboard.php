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
        /* ── Stat bar (baris horizontal, tanpa card) ── */
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
        /* ── Section (bukan card, background transparan) ── */
        .db-section {
            background: #fff;
            border-radius: 14px;
            border: 1px solid #e8edf5;
            overflow: hidden;
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
        .qa-list { display: flex; flex-direction: column; gap: 0; }
        .qa-item {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 14px 20px;
            background: #fff;
            border-bottom: 1px solid #f8fafc;
            text-decoration: none;
            color: inherit;
            transition: all 0.15s;
        }
        .qa-item:last-child { border-bottom: none; }
        .qa-item:hover {
            background: #fafbff;
        }
        .qa-ico {
            width: 36px; height: 36px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.15rem;
            flex-shrink: 0;
            background: #f1f5f9;
            color: #64748b;
        }
        .qa-item:nth-child(1) .qa-ico { background: #e0f2fe; color: #0284c7; }
        .qa-item:nth-child(2) .qa-ico { background: #fef3c7; color: #d97706; }
        .qa-item:nth-child(3) .qa-ico { background: #ede9fe; color: #4f46e5; }
        .qa-item:nth-child(4) .qa-ico { background: #d1fae5; color: #059669; }
        .qa-item:nth-child(5) .qa-ico { background: #fee2e2; color: #dc2626; }
        .qa-title { font-size: 0.84rem; font-weight: 600; color: #1e293b; }
        .qa-desc { font-size: 0.71rem; color: #94a3b8; margin-top: 2px; }
        .qa-arrow {
            margin-left: auto;
            color: #cbd5e1;
            font-size: 1.2rem;
            transition: all 0.2s;
        }
        .qa-item:hover .qa-arrow { color: #0284c7; transform: translateX(3px); }

        @media (max-width: 900px) {
            .stat-bar { flex-wrap: wrap; }
            .stat-item + .stat-item::before { display: none; }
            .stat-item { border-bottom: 1px solid #f1f5f9; min-width: 45%; }
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

            <div class="stat-bar">
                <div class="stat-item c1">
                    <div class="stat-ico c1">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                    </div>
                    <div>
                        <div class="stat-num"><?php echo $stats['users']; ?></div>
                        <div class="stat-lbl">Total Pengguna</div>
                    </div>
                </div>
                <div class="stat-item c2">
                    <div class="stat-ico c2">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path></svg>
                    </div>
                    <div>
                        <div class="stat-num"><?php echo $stats['guru']; ?></div>
                        <div class="stat-lbl">Jumlah Guru</div>
                    </div>
                </div>
                <div class="stat-item c3">
                    <div class="stat-ico c3">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle></svg>
                    </div>
                    <div>
                        <div class="stat-num"><?php echo $stats['siswa']; ?></div>
                        <div class="stat-lbl">Jumlah Siswa</div>
                    </div>
                </div>
                <div class="stat-item c4">
                    <div class="stat-ico c4">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16c0 1.1.9 2 2 2h12a2 2 0 0 0 2-2V8l-6-6z"></path><path d="M14 3v5h5"></path><path d="M16 13H8"></path><path d="M16 17H8"></path><path d="M10 9H8"></path></svg>
                    </div>
                    <div>
                        <div class="stat-num"><?php echo $stats['news']; ?></div>
                        <div class="stat-lbl">Berita Terbit</div>
                    </div>
                </div>
            </div>

            <div class="db-grid">

                <!-- Recent Users -->
                <div class="db-section" style="padding:0;">
                    <div class="db-section-head">
                        <span class="db-section-title">Pengguna Terbaru</span>
                    </div>
                    <div style="padding: 0 20px;">
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
                </div>

                <!-- Quick Actions -->
                <div class="db-section" style="padding:0;">
                    <div class="db-section-head">
                        <span class="db-section-title">Menu Cepat</span>
                    </div>
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
                            <div class="qa-ico">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/></svg>
                            </div>
                            <div>
                                <div class="qa-title">Pengaturan Profil</div>
                                <div class="qa-desc">Ubah data diri dan password</div>
                            </div>
                            <span class="qa-arrow">›</span>
                        </a>
                        <a href="manage_classes.php" class="qa-item">
                            <div class="qa-ico">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                            </div>
                            <div>
                                <div class="qa-title">Kelola Kelas</div>
                                <div class="qa-desc">Buat dan atur kelas untuk guru</div>
                            </div>
                            <span class="qa-arrow">›</span>
                        </a>
                        <a href="manage_schedules.php" class="qa-item">
                            <div class="qa-ico"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block; vertical-align:middle; line-height:1;"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg></div>
                            <div>
                                <div class="qa-title">Kelola Jadwal</div>
                                <div class="qa-desc">Upload jadwal pelajaran via Excel</div>
                            </div>
                            <span class="qa-arrow">›</span>
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
