<?php
// src/bk/pengaduan.php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'bk') {
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
    $id = $_GET['delete'];
    try {
        $stmt = $pdo->prepare("DELETE FROM pengaduan WHERE id = ?");
        $stmt->execute([$id]);
        header("Location: pengaduan.php?msg=deleted");
        exit;
    } catch (PDOException $e) {
        $error = "Error delete ticket: " . $e->getMessage();
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
    <title>E-Counseling & Layanan BK - Guru BK</title>
    <link rel="stylesheet" href="/public/assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .ticket-card {
            background: white;
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            margin-bottom: 1rem;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border);
            border-left: 4px solid var(--primary);
        }
        .urgent { border-left-color: var(--danger); }
        .ticket-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }
        .ticket-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--secondary);
            margin-bottom: 0.25rem;
        }
        .ticket-meta {
            color: var(--text-muted);
            font-size: 0.85rem;
            display: flex;
            gap: 15px;
        }
        .badge-kategori {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 700;
            background: #e0e7ff;
            color: #4338ca;
        }
        .badge-bullying { background: #fee2e2; color: #991b1b; }
        
        .ticket-body {
            background: #f8fafc;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            font-size: 0.95rem;
            line-height: 1.6;
        }
        .status-form {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .status-form select {
            width: auto;
            min-width: 150px;
            padding: 0.5rem;
        }
        .status-form button {
            padding: 0.5rem 1rem;
        }
    </style>
</head>
<body>

<div class="app-container">
    <?php include '../templates/sidebar.php'; ?>
    
    <main class="main-content">
        <!-- Dashboard Hero -->
        <div class="dashboard-hero">
            <div style="position: relative; z-index: 2;">
                <h1 style="color: white; margin-bottom: 0.5rem;">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:middle;"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                    Layanan E-Counseling BK
                </h1>
                <p style="color: rgba(255,255,255,0.8);">Kelola laporan dan pengaduan siswa</p>
            </div>
            <div style="position: absolute; right: -50px; top: -50px; width: 200px; height: 200px; background: rgba(255,255,255,0.08); border-radius: 50%;"></div>
        </div>

        <div class="content-overlap">
            <?php if (isset($_GET['msg'])): ?>
                <?php if ($_GET['msg'] === 'updated'): ?>
                    <div style="background: var(--primary-light); color: var(--primary); padding: 1rem; border-radius: 8px; margin-bottom: 2rem;">
                        Status tiket berhasil diperbarui.
                    </div>
                <?php elseif ($_GET['msg'] === 'deleted'): ?>
                    <div style="background: #fee2e2; color: #991b1b; padding: 1rem; border-radius: 8px; margin-bottom: 2rem;">
                        Tiket pengaduan berhasil dihapus.
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div style="background: #fee2e2; color: #991b1b; padding: 1rem; border-radius: 8px; margin-bottom: 2rem;">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if (empty($tickets)): ?>
                <div class="card" style="text-align: center; padding: 3rem;">
                    <p style="color: var(--text-muted); font-size: 1.1rem;">Belum ada tiket pengaduan yang masuk.</p>
                </div>
            <?php else: ?>
                <?php foreach ($tickets as $t): ?>
                    <?php 
                        $isBullying = ($t['kategori'] === 'Bullying');
                        $statusColor = '';
                        if ($t['status'] === 'Pending') $statusColor = '#f59e0b';
                        if ($t['status'] === 'Diproses') $statusColor = '#3b82f6';
                        if ($t['status'] === 'Selesai') $statusColor = '#10b981';
                    ?>
                    <div class="ticket-card <?php echo $isBullying ? 'urgent' : ''; ?>">
                        <div class="ticket-header">
                            <div>
                                <div class="ticket-title">
                                    <?php echo htmlspecialchars($t['nama_siswa']); ?> 
                                    <span style="color: #cbd5e1;">(<?php echo htmlspecialchars($t['kelas']); ?>)</span>
                                </div>
                                <div class="ticket-meta">
                                    <span><?php echo date('d M Y, H:i', strtotime($t['created_at'])); ?></span>
                                    <span class="badge-kategori <?php echo $isBullying ? 'badge-bullying' : ''; ?>"><?php echo htmlspecialchars($t['kategori']); ?></span>
                                </div>
                            </div>
                            <div>
                                <span style="font-size: 0.85rem; font-weight: 700; color: <?php echo $statusColor; ?>; display: flex; align-items: center; gap: 5px;">
                                    <span style="display:inline-block; width:8px; height:8px; border-radius:50%; background:<?php echo $statusColor; ?>;"></span>
                                    <?php echo htmlspecialchars($t['status']); ?>
                                </span>
                            </div>
                        </div>
                        
                        <div class="ticket-body">
                            <?php echo nl2br(htmlspecialchars($t['pesan'])); ?>
                        </div>
                        
                        <form method="POST" action="" class="status-form" style="display:flex; flex-wrap:wrap;">
                            <input type="hidden" name="id" value="<?php echo $t['id']; ?>">
                            <input type="hidden" name="update_status" value="1">
                            <select name="status">
                                <option value="Pending" <?php echo $t['status'] === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="Diproses" <?php echo $t['status'] === 'Diproses' ? 'selected' : ''; ?>>Diproses</option>
                                <option value="Selesai" <?php echo $t['status'] === 'Selesai' ? 'selected' : ''; ?>>Selesai</option>
                            </select>
                            <button type="submit" class="btn btn-secondary">Simpan Status</button>
                            <a href="?delete=<?php echo $t['id']; ?>" class="btn btn-danger" onclick="return confirm('Hapus tiket pengaduan ini secara permanen?');" style="margin-left:auto;">Hapus Tiket</a>
                        </form>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>
</div>

</body>
</html>

