<?php
// src/admin/pengaduan.php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

// Update status if requested
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $id = $_POST['id'];
    $status = $_POST['status'];
    try {
        $stmt = $pdo->prepare("UPDATE pengaduan SET status = ? WHERE id = ?");
        $stmt->execute([$status, $id]);
        header("Location: pengaduan.php?msg=updated");
        exit;
    } catch (PDOException $e) {
        $error = "Error updating status: " . $e->getMessage();
    }
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    try {
        $stmt = $pdo->prepare("DELETE FROM pengaduan WHERE id = ?");
        $stmt->execute([$id]);
        header("Location: pengaduan.php?msg=deleted");
        exit;
    } catch (PDOException $e) {
        $error = "Gagal menghapus tiket: " . $e->getMessage();
    }
}

// Fetch all tickets
try {
    $stmt = $pdo->query("SELECT * FROM pengaduan ORDER BY created_at DESC");
    $tickets = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Counseling & Layanan BK - Admin</title>
    <link rel="stylesheet" href="/public/assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .main-content { background: #f5f7fb !important; padding: 0 !important; }
        .page-hero {
            background: linear-gradient(135deg, #1e1b4b 0%, #312e81 50%, #4338ca 100%);
            padding: 2.5rem 3rem 5rem;
            position: relative;
            overflow: hidden;
        }
        .page-hero::before {
            content: '';
            position: absolute;
            right: -60px; top: -60px;
            width: 250px; height: 250px;
            background: rgba(255,255,255,0.07);
            border-radius: 50%;
        }
        .page-hero h1 { color: #fff; font-size: 1.6rem; font-weight: 700; margin: 0 0 0.4rem; }
        .page-hero p  { color: rgba(255,255,255,0.8); margin: 0; font-size: 0.95rem; }
        .page-content {
            position: relative;
            margin-top: -2.5rem;
            padding: 0 3rem 3rem;
            z-index: 10;
        }
        .alert-success {
            background: #dcfce7; color: #166534;
            padding: 12px 18px; border-radius: 10px;
            margin-bottom: 1.5rem; font-weight: 500;
            border: 1px solid #bbf7d0;
        }
        .alert-error {
            background: #fee2e2; color: #991b1b;
            padding: 12px 18px; border-radius: 10px;
            margin-bottom: 1.5rem; font-weight: 500;
            border: 1px solid #fecaca;
        }
        .db-section {
            background: #fff;
            border-radius: 14px;
            border: 1px solid #e8edf5;
            overflow: hidden;
            margin-bottom: 1.2rem;
        }
        .ticket-row { padding: 18px 22px; }
        .ticket-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 10px;
        }
        .ticket-name { font-size: 1rem; font-weight: 700; color: #0f172a; }
        .ticket-kelas { color: #94a3b8; font-weight: 400; font-size: 0.9rem; margin-left: 6px; }
        .ticket-meta {
            color: #94a3b8;
            font-size: 0.82rem;
            display: flex;
            gap: 14px;
            margin-top: 3px;
            flex-wrap: wrap;
        }
        .badge-kategori {
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 700;
            background: #e0e7ff;
            color: #4338ca;
        }
        .badge-bullying { background: #fee2e2; color: #991b1b; }
        .status-dot {
            display: inline-block;
            width: 8px; height: 8px;
            border-radius: 50%;
            margin-right: 5px;
        }
        .ticket-body {
            background: #f8fafc;
            padding: 12px 16px;
            border-radius: 10px;
            margin: 10px 0;
            font-size: 0.92rem;
            line-height: 1.65;
            color: #334155;
            border-left: 3px solid #e8edf5;
        }
        .ticket-actions {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 10px;
        }
        .ticket-actions select {
            padding: 7px 12px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-family: inherit;
            font-size: 0.88rem;
            background: #fff;
        }
        .btn-save {
            padding: 7px 16px;
            background: #4338ca;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.88rem;
            cursor: pointer;
            font-family: inherit;
        }
        .btn-save:hover { background: #3730a3; }
        .btn-del {
            padding: 7px 16px;
            background: #fee2e2;
            color: #991b1b;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.88rem;
            cursor: pointer;
            font-family: inherit;
            margin-left: auto;
            text-decoration: none;
        }
        .btn-del:hover { background: #fecaca; }
        .empty-state { text-align: center; padding: 4rem 2rem; color: #94a3b8; }
        .urgent-border { border-left: 4px solid #ef4444 !important; }
        @media (max-width: 768px) {
            .page-content { padding: 0 1rem 2rem; }
            .page-hero { padding: 2rem 1.5rem 4.5rem; }
            .ticket-actions { flex-direction: column; align-items: flex-start; }
            .btn-del { margin-left: 0; }
        }
    </style>
</head>
<body>

<div class="app-container">
    <?php include '../templates/sidebar.php'; ?>
    
    <main class="main-content">
        <div class="page-hero">
            <h1>
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:middle;margin-right:8px;"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                Layanan E-Counseling BK
            </h1>
            <p>Kelola laporan dan pengaduan siswa</p>
        </div>

        <div class="page-content">
            <?php if (isset($_GET['msg'])): ?>
                <?php if ($_GET['msg'] === 'updated'): ?>
                    <div class="alert-success">&#10003; Status tiket berhasil diperbarui.</div>
                <?php elseif ($_GET['msg'] === 'deleted'): ?>
                    <div class="alert-error">Tiket pengaduan berhasil dihapus.</div>
                <?php endif; ?>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if (empty($tickets)): ?>
                <div class="db-section">
                    <div class="empty-state">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin-bottom:1rem;opacity:0.4;"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                        <p style="font-size:1.05rem; font-weight:600; color:#64748b;">Belum ada tiket pengaduan yang masuk.</p>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($tickets as $t): ?>
                    <?php 
                        $isBullying = ($t['kategori'] === 'Bullying');
                        $statusColor = '#f59e0b';
                        if ($t['status'] === 'Diproses') $statusColor = '#3b82f6';
                        if ($t['status'] === 'Selesai')  $statusColor = '#10b981';
                    ?>
                    <div class="db-section <?php echo $isBullying ? 'urgent-border' : ''; ?>">
                        <div class="ticket-row">
                            <div class="ticket-top">
                                <div>
                                    <div class="ticket-name">
                                        <?php echo htmlspecialchars($t['nama_siswa']); ?>
                                        <span class="ticket-kelas">(<?php echo htmlspecialchars($t['kelas']); ?>)</span>
                                    </div>
                                    <div class="ticket-meta">
                                        <span><?php echo date('d M Y, H:i', strtotime($t['created_at'])); ?></span>
                                        <span class="badge-kategori <?php echo $isBullying ? 'badge-bullying' : ''; ?>"><?php echo htmlspecialchars($t['kategori']); ?></span>
                                    </div>
                                </div>
                                <span style="font-size: 0.85rem; font-weight: 700; color: <?php echo $statusColor; ?>; display:flex; align-items:center;">
                                    <span class="status-dot" style="background:<?php echo $statusColor; ?>;"></span>
                                    <?php echo htmlspecialchars($t['status']); ?>
                                </span>
                            </div>
                            
                            <div class="ticket-body">
                                <?php echo nl2br(htmlspecialchars($t['pesan'])); ?>
                            </div>
                            
                            <form method="POST" action="" class="ticket-actions">
                                <input type="hidden" name="id" value="<?php echo $t['id']; ?>">
                                <input type="hidden" name="update_status" value="1">
                                <select name="status">
                                    <option value="Pending"  <?php echo $t['status'] === 'Pending'  ? 'selected' : ''; ?>>Pending</option>
                                    <option value="Diproses" <?php echo $t['status'] === 'Diproses' ? 'selected' : ''; ?>>Diproses</option>
                                    <option value="Selesai"  <?php echo $t['status'] === 'Selesai'  ? 'selected' : ''; ?>>Selesai</option>
                                </select>
                                <button type="submit" class="btn-save">Simpan Status</button>
                                <a href="?delete=<?php echo $t['id']; ?>" class="btn-del" onclick="return confirm('Hapus tiket ini secara permanen?');">Hapus</a>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>
</div>

</body>
</html>
