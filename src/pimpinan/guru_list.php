<?php
// src/pimpinan/guru_list.php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['kepsek', 'wakasek'])) {
    header("Location: ../../login.php");
    exit;
}

$search_query = $_GET['search'] ?? '';

$sql = "SELECT users.*, subjects.name as subject_name 
        FROM users 
        LEFT JOIN subjects ON users.subject_id = subjects.id 
        WHERE users.role = 'guru' ";

$params = [];

if ($search_query) {
    $sql .= " AND (users.full_name LIKE ? OR users.username LIKE ? OR users.nip LIKE ?) ";
    $params[] = "%$search_query%";
    $params[] = "%$search_query%";
    $params[] = "%$search_query%";
}

$sql .= " ORDER BY users.full_name ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$guruList = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Direktori Guru - Pimpinan</title>
    <link rel="stylesheet" href="/public/assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .role-badge { padding: 4px 10px; border-radius: 20px; font-weight: 700; font-size: 0.75rem; text-transform: uppercase; }
        .role-guru { background: #e0e7ff; color: #3730a3; }
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
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 1rem; border-bottom: 2px solid #f1f5f9; color: #64748b; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.05em; }
        td { padding: 1rem; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
        .user-info { display: flex; align-items: center; gap: 1rem; }
        .user-avatar-placeholder { width: 40px; height: 40px; border-radius: 50%; background: #f1f5f9; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; }
        .user-avatar { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; }
    </style>
</head>
<body style="background-color: #f8fafc;">

<div class="app-container">
    <?php include '../templates/sidebar.php'; ?>
    
    <main class="main-content">
        <!-- Unified Hero Header -->
        <div class="dashboard-hero">
            <div style="position: relative; z-index: 2;">
                <h1 style="color: white; margin-bottom: 0.5rem;"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block; vertical-align:middle; line-height:1;"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/><path d="M12 14h.01"/><path d="M16 14h.01"/><path d="M8 14h.01"/></svg> Direktori Guru</h1>
                <p style="color: rgba(255,255,255,0.8);">Pantau data seluruh guru pengajar di sekolah.</p>
            </div>
            <!-- Decorative circle -->
            <div style="position: absolute; right: -50px; top: -50px; width: 200px; height: 200px; background: rgba(255,255,255,0.1); border-radius: 50%;"></div>
        </div>
        
        <div style="background: white; border-radius: 12px; padding: 1.5rem; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
            <div style="margin-bottom: 1.5rem;">
                <form method="GET" style="display: flex; gap: 10px;">
                    <input type="text" name="search" placeholder="Cari nama, NIP, username..." value="<?php echo htmlspecialchars($search_query); ?>" style="flex: 1; padding: 0.75rem 1rem; border: 1px solid #e2e8f0; border-radius: 8px; font-family: inherit;">
                    
                    <button type="submit" style="background: #0ea5e9; color: white; border: none; padding: 0.75rem 1.5rem; border-radius: 8px; font-weight: 600; cursor: pointer;">Cari</button>
                    <!-- Reset Button -->
                    <?php if ($search_query): ?>
                        <a href="guru_list.php" style="background: #f1f5f9; color: #475569; text-decoration: none; padding: 0.75rem 1.5rem; border-radius: 8px; font-weight: 600; display: inline-block;">Reset</a>
                    <?php
endif; ?>
                </form>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Guru</th>
                            <th>Mata Pelajaran</th>
                            <th>Gender & Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($guruList)): ?>
                            <tr>
                                <td colspan="3" style="text-align: center; color: #64748b; padding: 2rem;">Tidak ada data guru ditemukan.</td>
                            </tr>
                        <?php
else: ?>
                            <?php foreach ($guruList as $u): ?>
                            <tr>
                                <td>
                                    <div class="user-info">
                                        <?php if ($u['photo_path']): ?>
                                            <img src="/<?php echo htmlspecialchars($u['photo_path']); ?>" class="user-avatar">
                                        <?php
        else: ?>
                                            <div class="user-avatar-placeholder"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block; vertical-align:middle; line-height:1;"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></div>
                                        <?php
        endif; ?>
                                        <div>
                                            <strong style="color: #0f172a; font-size: 0.95rem;"><?php echo htmlspecialchars($u['full_name']); ?></strong><br>
                                            <span style="color: #64748b; font-size: 0.8rem;">@<?php echo htmlspecialchars($u['username']); ?></span>
                                            <?php if ($u['nip']): ?>
                                                <div style="font-size: 0.75rem; color: #475569;">NIP: <?php echo htmlspecialchars($u['nip']); ?></div>
                                            <?php
        endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($u['subject_name']): ?>
                                        <span style="background: #f1f5f9; color: #334155; padding: 4px 10px; border-radius: 6px; font-size: 0.85rem; font-weight: 500; display: inline-block;">
                                            <?php echo htmlspecialchars($u['subject_name']); ?>
                                        </span>
                                    <?php
        else: ?>
                                        <span style="color: #94a3b8; font-style: italic; font-size: 0.85rem;">Belum diatur</span>
                                    <?php
        endif; ?>
                                </td>
                                <td>
                                    <?php if ($u['gender'])
            echo($u['gender'] == 'L' ? '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block; vertical-align:middle; line-height:1;"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg> Laki-laki' : '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block; vertical-align:middle; line-height:1;"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg> Perempuan') . '<br>'; ?>
                                    <div style="margin-top: 6px;">
                                        <?php if ($u['status'] == 'active'): ?>
                                            <span style="color: #10b981; font-size: 0.8rem; font-weight: 600;"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block; vertical-align:middle; line-height:1;"><polyline points="20 6 9 17 4 12"/></svg> AKTIF</span>
                                        <?php
        elseif ($u['status'] == 'suspended'): ?>
                                            <span style="color: #ef4444; font-size: 0.8rem; font-weight: 600;"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block; vertical-align:middle; line-height:1;"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg> SUSPEND</span>
                                        <?php
        else: ?>
                                            <span style="color: #64748b; font-size: 0.8rem; font-weight: 600; text-transform: uppercase;"><?php echo htmlspecialchars($u['status']); ?></span>
                                        <?php
        endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php
    endforeach; ?>
                        <?php
endif; ?>
                    </tbody>
                </table>
            </div>
            
            <div style="margin-top: 1rem; font-size: 0.85rem; color: #64748b; text-align: right;">
                Total Guru: <?php echo count($guruList); ?>
            </div>
        </div>
    </main>
</div>

</body>
</html>

