<?php
// src/siswa/materials.php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'siswa') {
    header("Location: ../../login.php");
    exit;
}

// Get Student's Class
$stmt = $pdo->prepare("SELECT class_id FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user_data = $stmt->fetch();
$class_id = $user_data['class_id'];
$subject_id = null;
$current_subject = null;

// Fetch Materials for this Class OR Global
if ($class_id) {
    $subject_id = $_GET['subject_id'] ?? null;
    $is_general = isset($_GET['general']);
    $current_subject = null;

    if ($subject_id || $is_general) {
        $condition = $is_general ? "COALESCE(m.subject_id, u.subject_id) IS NULL" : "COALESCE(m.subject_id, u.subject_id) = ?";

        // Fetch Materials for specific Subject
        $sql = "SELECT m.*, u.full_name as teacher_name, 
                COALESCE(s_mat.name, s_user.name) as subject_name 
                FROM materials m 
                JOIN users u ON m.teacher_id = u.id 
                LEFT JOIN subjects s_user ON u.subject_id = s_user.id
                LEFT JOIN subjects s_mat ON m.subject_id = s_mat.id
                WHERE (m.class_id = ? OR m.class_id IS NULL)
                AND $condition
                ORDER BY m.created_at DESC";
        $stmt = $pdo->prepare($sql);
        if ($is_general) {
            $stmt->execute([$class_id]);
            $current_subject = "Umum / Lainnya";
        }
        else {
            $stmt->execute([$class_id, $subject_id]);
        }
        $materials = $stmt->fetchAll();

        if ($subject_id && !$current_subject) {
            // Get Subject Name fallback
            $stmt_sub = $pdo->prepare("SELECT name FROM subjects WHERE id = ?");
            $stmt_sub->execute([$subject_id]);
            $current_subject = $stmt_sub->fetchColumn();
        }

    }
    else {
        // Fetch Subjects that have materials for this class
        $sql = "SELECT 
                    COALESCE(m.subject_id, u.subject_id) as id, 
                    COALESCE(s_mat.name, s_user.name) as name, 
                    COUNT(m.id) as material_count
                FROM materials m
                JOIN users u ON m.teacher_id = u.id
                LEFT JOIN subjects s_user ON u.subject_id = s_user.id
                LEFT JOIN subjects s_mat ON m.subject_id = s_mat.id
                WHERE (m.class_id = ? OR m.class_id IS NULL)
                GROUP BY id, name
                ORDER BY name ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$class_id]);
        $subjects = $stmt->fetchAll();
        $materials = []; // Empty because we are showing subjects
    }
}

else {
    $materials = [];
    $warning = "Anda belum dimasukkan ke dalam kelas. Hubungi Admin.";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Materi Pelajaran</title>
    <link rel="stylesheet" href="/public/assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="admin-full-layout">

<div class="app-container">
    <?php include '../templates/sidebar.php'; ?>
    
    <main class="main-content">
        <!-- Dashboard Hero -->
        <div class="dashboard-hero">
            <div style="position: relative; z-index: 2;">
                <h1 style="color: white; margin-bottom: 0.5rem;">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:middle;"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/></svg>
                    Materi Pelajaran
                </h1>
                <p style="color: rgba(255,255,255,0.8);">Akses materi yang dibagikan guru Anda.</p>
            </div>
            <div style="position: absolute; right: -50px; top: -50px; width: 200px; height: 200px; background: rgba(255,255,255,0.08); border-radius: 50%;"></div>
        </div>

        <div class="content-overlap">

        <?php if (isset($warning))
    echo "<div style='background:#fffbeb; color:#b45309; padding:15px; border-radius:8px; margin-bottom:15px; font-weight: 600; border: 1px solid #fcd34d;'><svg width='20' height='20' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' style='display:inline-block; vertical-align:middle; line-height:1;'><path d='m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z'/><line x1='12' y1='9' x2='12' y2='13'/><line x1='12' y1='17' x2='12.01' y2='17'/></svg> $warning</div>"; ?>

        <?php if ($subject_id && $current_subject): ?>
            <div style="margin-bottom: 20px;">
                <a href="materials.php" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 8px;">
                    &larr; Kembali ke Mapel
                </a>
                <h2 style="margin-top: 15px; font-size: 1.5rem; color: #1e293b;"><?php echo htmlspecialchars($current_subject); ?></h2>
            </div>
        <?php
endif; ?>

        <?php if (!$subject_id): ?>
            <!-- SUBJECTS GRID -->
             <?php if (empty($subjects)): ?>
                <div class="card" style="text-align: center; color: var(--text-muted); padding: 3rem;">
                    <p>Belum ada materi pelajaran untuk kelas Anda.</p>
                </div>
            <?php
    else: ?>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px;">
                    <?php foreach ($subjects as $sub): ?>
                        <a href="?subject_id=<?php echo $sub['id']; ?>" class="card" style="text-decoration: none; color: inherit; transition: transform 0.2s, box-shadow 0.2s; display: flex; flex-direction: column; align-items: center; text-align: center; padding: 2rem;">
                            <div style="width: 60px; height: 60px; background: #e0e7ff; color: #4338ca; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; margin-bottom: 1rem;">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block; vertical-align:middle; line-height:1;"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/></svg>
                            </div>
                            <h3 style="margin: 0; font-size: 1.1rem; color: #1e293b;"><?php echo htmlspecialchars($sub['name']); ?></h3>
                            <span style="background: #f1f5f9; color: #64748b; padding: 2px 10px; border-radius: 20px; font-size: 0.8rem; margin-top: 10px;">
                                <?php echo $sub['material_count']; ?> Materi
                            </span>
                        </a>
                    <?php
        endforeach; ?>
                </div>
            <?php
    endif; ?>

        <?php
else: ?>
            <!-- MATERIALS LIST (Filtered by Subject) -->
            <?php if (empty($materials)): ?>
                <div class="card" style="text-align: center; color: var(--text-muted); padding: 3rem;">
                    <p>Belum ada materi untuk mata pelajaran ini.</p>
                </div>
            <?php
    else: ?>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px;">
                    <?php foreach ($materials as $m): ?>
                        <div class="card" style="margin-bottom: 0; display: flex; flex-direction: column;">
                            <div style="margin-bottom: 1rem;">
                                <span style="background: #f1f5f9; color: #64748b; padding: 2px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase;">
                                    <?php echo htmlspecialchars($m['type']); ?>
                                </span>
                            </div>
                            
                            <h3 style="font-size: 1.25rem; margin-bottom: 0.5rem; line-height: 1.4;">
                                <?php echo htmlspecialchars($m['title']); ?>
                            </h3>
                            
                            <p style="color: #64748b; font-size: 0.9rem; margin-bottom: 1.5rem; flex: 1;">
                                <?php echo htmlspecialchars($m['description']); ?>
                            </p>
                            
                            <div style="border-top: 1px solid #e2e8f0; padding-top: 1rem; margin-top: auto;">
                                <p style="font-size: 0.8rem; color: #94a3b8; margin-bottom: 1rem;">
                                    Oleh: <strong><?php echo htmlspecialchars($m['teacher_name']); ?></strong><br>
                                    <?php echo date('d M Y', strtotime($m['created_at'])); ?>
                                </p>
                                <?php
            $href = $m['file_path'];
            // If it's NOT a link type and doesn't start with http, assume it's a local file
            if ($m['type'] !== 'link' && strpos($href, 'http') !== 0) {
                $href = '../../' . $href;
            }
?>
                                <a href="<?php echo htmlspecialchars($href); ?>" target="_blank" class="btn" style="width: 100%;">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block; vertical-align:middle; line-height:1;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg> Lihat Materi
                                </a>
                            </div>
                        </div>
                    <?php
        endforeach; ?>
                </div>
            <?php
    endif; ?>
        <?php
endif; ?>
        </div><!-- /.content-overlap -->
    </main>
</div>

</body>
</html>

