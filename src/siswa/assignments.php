<?php
// src/siswa/assignments.php
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

// Handle Assignment Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_assignment'])) {
    $assignment_id = $_POST['assignment_id'];
    $student_id = $_SESSION['user_id'];

    $target_dir = "../../public/uploads/submissions/";
    if (!file_exists($target_dir))
        mkdir($target_dir, 0777, true);

    // Check for late submission
    $stmt = $pdo->prepare("SELECT deadline FROM assignments WHERE id = ?");
    $stmt->execute([$assignment_id]);
    $assign = $stmt->fetch();
    $is_late_submit = ($assign && $assign['deadline'] && time() > strtotime($assign['deadline']));
    $status = $is_late_submit ? 'terlambat' : 'menunggu_nilai';

    // Insert Submission Record FIRST
    $stmt = $pdo->prepare("INSERT INTO submissions (assignment_id, student_id, status, submitted_at) VALUES (?, ?, ?, NOW())");
    if ($stmt->execute([$assignment_id, $student_id, $status])) {
        $submission_id = $pdo->lastInsertId();

        // Handle Multiple Files
        if (isset($_FILES['files'])) {
            $files = $_FILES['files'];
            $file_count = count($files['name']);
            $uploaded_count = 0;

            for ($i = 0; $i < $file_count; $i++) {
                if ($files['error'][$i] == 0) {
                    $file_name = time() . "_" . $student_id . "_" . basename($files["name"][$i]);
                    $target_file = $target_dir . $file_name;

                    if (move_uploaded_file($files["tmp_name"][$i], $target_file)) {
                        $file_path = "public/uploads/submissions/" . $file_name;
                        $pdo->prepare("INSERT INTO submission_attachments (submission_id, file_path) VALUES (?, ?)")->execute([$submission_id, $file_path]);
                        $uploaded_count++;
                    }
                }
            }
        }

        $msg = "Tugas berhasil dikumpulkan!";
        if ($is_late_submit) {
            $msg .= " (Status: Terlambat)";
        }
        $_SESSION['flash'] = ['type' => 'success', 'message' => $msg];
    }
    else {
        $_SESSION['flash'] = ['type' => 'error', 'message' => "Gagal menyimpan data ke database."];
    }

    // REDIRECT (PRG Pattern)
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit;
}

// Handle Attendance Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_attendance'])) {
    $assignment_id = $_POST['assignment_id'];
    $student_id = $_SESSION['user_id'];
    $selected_status = $_POST['status']; // hadir, sakit, izin

    // Verify assignment still exists
    $verify = $pdo->prepare("SELECT id FROM assignments WHERE id = ?");
    $verify->execute([$assignment_id]);
    if (!$verify->fetch()) {
        $_SESSION['flash'] = ['type' => 'error', 'message' => "Absensi ini sudah tidak tersedia (mungkin sudah dihapus guru)."];
    }
    // Validate status
    elseif (!in_array($selected_status, ['hadir', 'sakit', 'izin'])) {
        $_SESSION['flash'] = ['type' => 'error', 'message' => "Status absensi tidak valid."];
    }
    else {
        // Check if already submitted
        $check = $pdo->prepare("SELECT id FROM submissions WHERE assignment_id = ? AND student_id = ?");
        $check->execute([$assignment_id, $student_id]);

        if (!$check->fetch()) {
            // Server-side guard: block submission if time window has closed
            $stmt_time = $pdo->prepare("SELECT deadline, jam_start FROM assignments WHERE id = ?");
            $stmt_time->execute([$assignment_id]);
            $assign_time = $stmt_time->fetch();
            if ($assign_time && !empty($assign_time['deadline']) && time() > strtotime($assign_time['deadline'])) {
                $_SESSION['flash'] = ['type' => 'error', 'message' => 'Waktu absensi sudah berakhir. Silakan hubungi guru untuk perubahan status.'];
                header("Location: " . $_SERVER['REQUEST_URI']);
                exit;
            }

            // Status is exactly what student selected (hadir / sakit / izin)
            $final_status = $selected_status;

            // Insert submission
            // Note: file_path is NULLABLE now
            $stmt = $pdo->prepare("INSERT INTO submissions (assignment_id, student_id, status, submitted_at) VALUES (?, ?, ?, NOW())");
            if ($stmt->execute([$assignment_id, $student_id, $final_status])) {
                $status_msg = ucfirst($final_status);
                $_SESSION['flash'] = ['type' => 'success', 'message' => "Berhasil absen! Status: <strong>$status_msg</strong>"];
            }
            else {
                $_SESSION['flash'] = ['type' => 'error', 'message' => "Gagal mencatat kehadiran."];
            }
        }
        else {
            // Cek apakah submission lama statusnya 'terlambat' dan sekarang masih dalam jam absensi
            $old_sub = $pdo->prepare("SELECT id, status FROM submissions WHERE assignment_id = ? AND student_id = ?");
            $old_sub->execute([$assignment_id, $student_id]);
            $existing = $old_sub->fetch();

            $stmt_time = $pdo->prepare("SELECT deadline FROM assignments WHERE id = ?");
            $stmt_time->execute([$assignment_id]);
            $assign_time = $stmt_time->fetch();
            $still_open  = $assign_time && !empty($assign_time['deadline']) && time() <= strtotime($assign_time['deadline']);

            if ($existing && $existing['status'] === 'terlambat' && $still_open) {
                // Update status dari terlambat ke pilihan siswa
                $upd = $pdo->prepare("UPDATE submissions SET status = ?, submitted_at = NOW() WHERE id = ?");
                if ($upd->execute([$selected_status, $existing['id']])) {
                    $status_msg = ucfirst($selected_status);
                    $_SESSION['flash'] = ['type' => 'success', 'message' => "Status absensi diperbarui: <strong>$status_msg</strong>"];
                }
                else {
                    $_SESSION['flash'] = ['type' => 'error', 'message' => "Gagal memperbarui status."];
                }
            }
            else {
                $_SESSION['flash'] = ['type' => 'warning', 'message' => "Anda sudah melakukan absensi sebelumnya."];
            }
        }
    }

    // REDIRECT (PRG Pattern)
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit;
}

// Fetch Assignments Logic (MULTI-CLASS + STATUS FILTER)
if ($class_id) {
    // Only fetch ACTIVE assignments
    // Fetch jam_start, jam_end, assignment_type
    $subject_id = $_GET['subject_id'] ?? null;
    $is_general = isset($_GET['general']);
    $current_subject = null;

    if ($subject_id || $is_general) {
        $condition = $is_general ? "COALESCE(a.subject_id, u.subject_id) IS NULL" : "COALESCE(a.subject_id, u.subject_id) = ?";

        // Filter Type Logic
        $filter_type = $_GET['type'] ?? 'all';
        $type_condition = "";
        $params = [$class_id];

        if ($filter_type === 'tugas') {
            $type_condition = "AND a.assignment_type = 'tugas'";
        }
        elseif ($filter_type === 'absensi') {
            $type_condition = "AND a.assignment_type = 'absensi'";
        }

        // Fetch Assignments for specific Subject (or General)
        $sql = "SELECT DISTINCT a.*, u.full_name as teacher_name, 
                COALESCE(s_assign.name, s_user.name) as subject_name,
                (SELECT GROUP_CONCAT(file_path SEPARATOR ',') FROM assignment_attachments WHERE assignment_id = a.id) as attachments
                FROM assignments a 
                JOIN assignment_classes ac ON a.id = ac.assignment_id 
                JOIN users u ON a.teacher_id = u.id 
                LEFT JOIN subjects s_user ON u.subject_id = s_user.id
                LEFT JOIN subjects s_assign ON a.subject_id = s_assign.id
                WHERE ac.class_id = ? 
                AND a.status = 'active'
                AND $condition
                $type_condition
                ORDER BY a.deadline ASC";

        $stmt = $pdo->prepare($sql);
        if ($is_general) {
        // params already has class_id
        // no subject_id to add
        }
        else {
            $params[] = $subject_id;
        }

        $stmt->execute($params);
        $raw_assignments = $stmt->fetchAll();

        // Deduplicate Absensi by meeting_number (Keep Latest)
        $assignments = [];
        $seen_meetings = [];

        // Sort by ID DESC first to process latest first
        usort($raw_assignments, function ($a, $b) {
            return $b['id'] - $a['id'];
        });

        foreach ($raw_assignments as $asm) {
            if ($asm['assignment_type'] === 'absensi') {
                $m_num = $asm['meeting_number'];
                if (in_array($m_num, $seen_meetings)) {
                    continue; // Skip older duplicates
                }
                $seen_meetings[] = $m_num;
            }
            $assignments[] = $asm;
        }

        // Restore chronological order (Deadline ASC)
        usort($assignments, function ($a, $b) {
            return strtotime($a['deadline']) - strtotime($b['deadline']);
        });

        if ($subject_id && !$current_subject) {
            // Get Subject Name fallback
            $stmt_sub = $pdo->prepare("SELECT name FROM subjects WHERE id = ?");
            $stmt_sub->execute([$subject_id]);
            $current_subject = $stmt_sub->fetchColumn();
        }

    }
    else {
        // Fetch Subjects that have assignments OR meet links for this class
        $sql = "SELECT id, name, SUM(a_count) as assignment_count, SUM(m_count) as meet_count
                FROM (
                    SELECT 
                        COALESCE(a.subject_id, u.subject_id) as id, 
                        COALESCE(s_assign.name, s_user.name) as name, 
                        1 as a_count,
                        0 as m_count
                    FROM assignments a
                    JOIN assignment_classes ac ON a.id = ac.assignment_id
                    JOIN users u ON a.teacher_id = u.id
                    LEFT JOIN subjects s_user ON u.subject_id = s_user.id
                    LEFT JOIN subjects s_assign ON a.subject_id = s_assign.id
                    WHERE ac.class_id = ? AND a.status = 'active'
                    
                    UNION ALL
                    
                    SELECT 
                        COALESCE(m.subject_id, u.subject_id) as id, 
                        COALESCE(s_meet.name, s_user.name) as name, 
                        0 as a_count,
                        1 as m_count
                    FROM meet_links m
                    JOIN users u ON m.teacher_id = u.id
                    LEFT JOIN subjects s_user ON u.subject_id = s_user.id
                    LEFT JOIN subjects s_meet ON m.subject_id = s_meet.id
                    WHERE m.class_id = ?
                ) AS combined
                GROUP BY id, name
                ORDER BY name ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$class_id, $class_id]);
        $subjects = $stmt->fetchAll();
        $assignments = [];
    }

    // Fetch Google Meet links (selalu diambil untuk ditampilkan di page utama)
    if ($subject_id) {
        $stmt = $pdo->prepare("SELECT m.*, u.full_name as teacher_name, NULL as subject_name FROM meet_links m JOIN users u ON m.teacher_id = u.id WHERE m.class_id = ? AND m.subject_id = ? ORDER BY m.created_at DESC");
        $stmt->execute([$class_id, $subject_id]);
    }
    else {
        $stmt = $pdo->prepare("SELECT m.*, u.full_name as teacher_name, COALESCE(s.name, 'Umum') as subject_name FROM meet_links m JOIN users u ON m.teacher_id = u.id LEFT JOIN subjects s ON m.subject_id = s.id WHERE m.class_id = ? ORDER BY m.created_at DESC");
        $stmt->execute([$class_id]);
    }
    $meet_links = $stmt->fetchAll();
}
else {
    $assignments = [];
    $meet_links = [];
    $warning = "Anda belum dimasukkan ke dalam kelas. Hubungi Admin.";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tugas Saya</title>
    <link rel="stylesheet" href="/public/assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .main-content { background: #f5f7fb !important; padding: 0 !important; max-width: 100% !important; }
        .page-hero { background: linear-gradient(135deg, #7c2d12 0%, #c2410c 50%, #f97316 100%); padding: 2.5rem 3rem 4rem; position: relative; overflow: hidden; }
        .page-hero::after { content: ''; position: absolute; width: 400px; height: 400px; top:-200px; right:-100px; background: radial-gradient(circle, rgba(253,186,116,.25) 0%, transparent 60%); border-radius: 50%; pointer-events: none; }
        .hero-inner { position: relative; z-index: 2; }
        .hero-inner h1 { font-size: 1.6rem; font-weight: 800; color: #fff; margin-bottom: 0.3rem; }
        .hero-sub { color: rgba(255,255,255,0.65); font-size: 0.9rem; }
        .page-content { position: relative; margin-top: -2rem; padding: 0 3rem 3rem; z-index: 10; }
        .page-nav { display: flex; align-items: center; gap: 8px; margin-bottom: 18px; font-size: 0.82rem; }
        .page-nav a { color: #4f46e5; text-decoration: none; font-weight: 600; }
        .page-nav a:hover { text-decoration: underline; }
        .page-nav-sep { color: #cbd5e1; }
        .page-nav-cur { color: #1e293b; font-weight: 600; }
        .filter-tabs { display: flex; gap: 4px; background: #f1f5f9; padding: 4px; border-radius: 9px; width: fit-content; margin-bottom: 20px; }
        .filter-tab { padding: 6px 14px; border-radius: 6px; font-size: 0.82rem; font-weight: 700; text-decoration: none; color: #64748b; transition: background 0.15s, color 0.15s; }
        .filter-tab.active { background: #fff; color: #1e293b; box-shadow: 0 1px 4px rgba(0,0,0,0.08); }
        .filter-tab:hover:not(.active) { color: #334155; }
        .subject-list { display: flex; flex-direction: column; gap: 0; }
        .subject-item { display: flex; align-items: center; gap: 14px; padding: 14px 20px; text-decoration: none; color: inherit; background: #fff; border-bottom: 1px solid #f1f5f9; transition: background 0.15s; }
        .subject-item:first-child { border-radius: 12px 12px 0 0; }
        .subject-item:last-child { border-bottom: none; border-radius: 0 0 12px 12px; }
        .subject-item:only-child { border-radius: 12px; }
        .subject-item:hover { background: #fafbff; }
        .subj-ico { width: 40px; height: 40px; border-radius: 10px; background: #fee2e2; color: #dc2626; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .subj-info { flex: 1; min-width: 0; }
        .subj-name { font-size: 0.92rem; font-weight: 700; color: #1e293b; }
        .subj-count { font-size: 0.75rem; color: #94a3b8; margin-top: 2px; }
        .subj-arrow { color: #cbd5e1; }
        .assign-list { display: flex; flex-direction: column; gap: 0; }
        .assign-item { background: #fff; border-bottom: 1px solid #f1f5f9; padding: 16px 20px; border-left: 3px solid transparent; }
        .assign-item:first-child { border-radius: 12px 12px 0 0; }
        .assign-item:last-child { border-bottom: none; border-radius: 0 0 12px 12px; }
        .assign-item:only-child { border-radius: 12px; }
        .assign-item.accent-green { border-left-color: #10b981; }
        .assign-item.accent-red   { border-left-color: #ef4444; }
        .assign-item.accent-amber { border-left-color: #f59e0b; }
        .assign-item.accent-blue  { border-left-color: #3b82f6; }
        .assign-top { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; margin-bottom: 8px; }
        .assign-title-area { flex: 1; min-width: 0; }
        .assign-type-badge { display: inline-block; font-size: 0.62rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; padding: 2px 7px; border-radius: 4px; margin-bottom: 4px; }
        .assign-title { font-size: 0.95rem; font-weight: 700; color: #1e293b; line-height: 1.35; }
        .assign-meta { font-size: 0.76rem; color: #94a3b8; margin-top: 3px; }
        .assign-status-badge { font-size: 0.7rem; font-weight: 800; padding: 4px 11px; border-radius: 6px; text-transform: uppercase; white-space: nowrap; flex-shrink: 0; }
        .badge-pending { background: #fef3c7; color: #92400e; }
        .badge-late    { background: #fee2e2; color: #991b1b; }
        .badge-graded  { background: #dbeafe; color: #1e40af; }
        .badge-done    { background: #d1fae5; color: #065f46; }
        .badge-waiting { background: #f1f5f9; color: #475569; }
        .assign-desc { background: #f8fafc; border-radius: 8px; padding: 10px 14px; margin-bottom: 12px; font-size: 0.84rem; color: #334155; line-height: 1.6; border: 1px dashed #e2e8f0; }
        .upload-area { display: flex; gap: 10px; flex-wrap: wrap; align-items: center; padding-top: 12px; border-top: 1px solid #f1f5f9; margin-top: 8px; }
        .upload-area input[type=file] { flex: 1; border: 1px solid #e2e8f0; border-radius: 7px; padding: 7px 10px; font-size: 0.82rem; background: #fff; }
        .upload-submit-btn { background: #4f46e5; color: #fff; border: none; padding: 9px 18px; border-radius: 7px; font-size: 0.84rem; font-weight: 700; cursor: pointer; white-space: nowrap; transition: background 0.15s; }
        .upload-submit-btn:hover { background: #4338ca; }
        .attendance-form { background: #f8fafc; border-radius: 9px; padding: 12px 14px; border: 1px solid #e2e8f0; }
        .status-option { display: inline-flex; align-items: center; margin-right: 14px; cursor: pointer; font-size: 0.86rem; font-weight: 500; }
        .status-option input { margin-right: 5px; width: 15px; height: 15px; cursor: pointer; }
        .meet-item { display: flex; align-items: center; justify-content: space-between; gap: 14px; flex-wrap: wrap; background: #fff; padding: 14px 20px; border-bottom: 1px solid #f1f5f9; border-left: 3px solid transparent; }
        .meet-item:first-child { border-radius: 12px 12px 0 0; }
        .meet-item:last-child { border-bottom: none; border-radius: 0 0 12px 12px; }
        .meet-item:only-child { border-radius: 12px; }
        .meet-info { flex: 1; min-width: 0; }
        .meet-title { font-size: 0.9rem; font-weight: 700; color: #1e293b; }
        .meet-meta  { font-size: 0.75rem; color: #94a3b8; margin-top: 3px; }
        .meet-btn { padding: 7px 16px; border-radius: 8px; font-size: 0.8rem; font-weight: 700; text-decoration: none; white-space: nowrap; flex-shrink: 0; display: inline-flex; align-items: center; gap: 5px; transition: opacity 0.15s; }
        .meet-btn:hover { opacity: 0.85; }
        .feedback-box { background: #fffbeb; border-radius: 7px; padding: 9px 12px; border-left: 3px solid #f59e0b; font-size: 0.82rem; margin-top: 8px; }
        .submitted-notice { display: flex; align-items: center; gap: 7px; font-size: 0.82rem; color: #059669; font-weight: 600; margin-top: 6px; }
        .grade-display { text-align: center; background: #f0fdf4; border-radius: 9px; border: 1px solid #bbf7d0; padding: 8px 18px; }
        .grade-num { font-size: 2rem; font-weight: 900; color: #166534; line-height: 1; }
        .grade-lbl { font-size: 0.68rem; color: #166534; font-weight: 700; }
        .empty-state { text-align: center; padding: 3rem 1rem; color: #94a3b8; font-size: 0.88rem; background: #fff; border-radius: 12px; }
        .empty-state svg { opacity: 0.35; margin-bottom: 10px; }
        .flash-msg { padding: 10px 14px; border-radius: 8px; margin-bottom: 16px; font-weight: 600; font-size: 0.88rem; }
        @media (max-width: 900px) { .page-hero { padding: 2rem 1.5rem 3.5rem; } .page-content { padding: 0 1.2rem 2rem; } }
    </style>
</head>
<body class="admin-full-layout">

<div class="app-container">
    <?php include '../templates/sidebar.php'; ?>
    
    <main class="main-content">
        <!-- Hero -->
        <div class="page-hero">
            <div class="hero-inner">
                <h1>
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:middle;margin-right:8px;"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                    Tugas Saya
                </h1>
                <p class="hero-sub">Kerjakan tugas tepat waktu untuk mendapatkan nilai maksimal.</p>
            </div>
        </div>

        <div class="page-content">

        <?php
if (isset($_SESSION['flash'])) {
    $flash = $_SESSION['flash'];
    $cls = 'flash-msg';
    $bg = '#dcfce7'; $color = '#166534';
    if ($flash['type'] === 'error')   { $bg = '#fee2e2'; $color = '#991b1b'; }
    if ($flash['type'] === 'warning') { $bg = '#fffbeb'; $color = '#b45309'; }
    echo "<div class='flash-msg' style='background:$bg; color:$color;'>" . $flash['message'] . "</div>";
    unset($_SESSION['flash']);
}
?>

        <?php if ($subject_id || isset($_GET['general'])): ?>
            <!-- Breadcrumb -->
            <div class="page-nav">
                <a href="assignments.php">Semua Mapel</a>
                <span class="page-nav-sep">›</span>
                <span class="page-nav-cur"><?php echo htmlspecialchars($current_subject ?? ''); ?></span>
            </div>
            <?php
    $base_link = $is_general ? "?general=1" : "?subject_id=$subject_id";
    $curr_type = $_GET['type'] ?? 'all';
?>
            <div class="filter-tabs">
                <a href="<?php echo $base_link; ?>&type=all" class="filter-tab <?php echo $curr_type == 'all' ? 'active' : ''; ?>">Semua</a>
                <a href="<?php echo $base_link; ?>&type=tugas" class="filter-tab <?php echo $curr_type == 'tugas' ? 'active' : ''; ?>">Tugas</a>
                <a href="<?php echo $base_link; ?>&type=absensi" class="filter-tab <?php echo $curr_type == 'absensi' ? 'active' : ''; ?>">Absensi</a>
            </div>
        <?php
endif; ?>

        <?php if (!$subject_id && !isset($_GET['general'])): ?>
            <!-- DAFTAR MATA PELAJARAN -->
            <?php if (empty($subjects)): ?>
                <div class="empty-state">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    <p>Tidak ada tugas aktif atau belum ada tugas baru.</p>
                </div>
            <?php else: ?>
                <div class="subject-list">
                    <?php foreach ($subjects as $sub):
            $link = $sub['id'] ? "?subject_id=" . $sub['id'] : "?general=1";
            $name = $sub['name'] ?? "Umum / Lainnya";
            ?>
                    <a href="<?php echo $link; ?>" class="subject-item">
                        <div class="subj-ico">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                        </div>
                        <div class="subj-info">
                            <div class="subj-name"><?php echo htmlspecialchars($name); ?></div>
                            <div class="subj-count">
                                <?php if (!empty($sub['assignment_count'])): ?><?php echo $sub['assignment_count']; ?> tugas aktif<?php endif; ?>
                                <?php if (!empty($sub['assignment_count']) && !empty($sub['meet_count'])): ?> · <?php endif; ?>
                                <?php if (!empty($sub['meet_count'])): ?><?php echo $sub['meet_count']; ?> Google Meet<?php endif; ?>
                                <?php if (empty($sub['assignment_count']) && empty($sub['meet_count'])): ?>Buka mata pelajaran<?php endif; ?>
                            </div>
                        </div>
                        <div class="subj-arrow"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"/></svg></div>
                    </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php else: ?>

        <div class="assign-list">
            <?php if (empty($assignments) && empty($meet_links) && !isset($warning)): ?>
                <div class="empty-state">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    <p>Tidak ada tugas atau Google Meet aktif untuk mata pelajaran ini.</p>
                </div>
            <?php endif; ?>

            <?php foreach ($meet_links as $ml): ?>
                <?php
                $now = time();
                $is_started = true;
                $is_ended = false;
                $status_msg = "Pertemuan Google Meet";
                $btn_text = "Buka Google Meet";
                $btn_style = "background: #10b981; color: white;";
                $can_access = true;

                if ($ml['start_time'] && $ml['end_time']) {
                    $start_ts = strtotime($ml['start_time']);
                    $end_ts = strtotime($ml['end_time']);

                    if ($now < $start_ts) {
                        $is_started = false;
                        $can_access = false;
                        $status_msg = "Meet Belum Dimulai";
                        $btn_text = "Belum Dimulai";
                        $btn_style = "background: #e2e8f0; color: #94a3b8; cursor: not-allowed;";
                    } elseif ($now > $end_ts) {
                        $is_ended = true;
                        $can_access = false;
                        $status_msg = "Meet Telah Berakhir";
                        $btn_text = "Telah Berakhir";
                        $btn_style = "background: #fee2e2; color: #991b1b; cursor: not-allowed;";
                    }
                }
                ?>
                <div class="meet-item" style="border-left: 3px solid <?php echo $is_ended ? '#ef4444' : (!$is_started ? '#f59e0b' : '#10b981'); ?>;">
                    <div class="meet-info">
                        <span style="background: <?php echo $is_ended ? '#fee2e2' : (!$is_started ? '#fef3c7' : '#d1fae5'); ?>; color: <?php echo $is_ended ? '#991b1b' : (!$is_started ? '#b45309' : '#059669'); ?>; font-size:0.62rem; font-weight:800; padding:2px 7px; border-radius:4px; text-transform:uppercase; letter-spacing:0.05em;">GOOGLE MEET</span>
                        <div class="meet-title" style="margin-top:4px;"><?php echo htmlspecialchars($status_msg); ?> — <?php echo htmlspecialchars($current_subject ?: 'Umum'); ?></div>
                        <div class="meet-meta">
                            <?php echo htmlspecialchars($ml['teacher_name']); ?>
                            <?php if ($ml['start_time'] && $ml['end_time']): ?>
                             · <?php echo date('d M Y, H:i', strtotime($ml['start_time'])); ?> – <?php echo date('H:i', strtotime($ml['end_time'])); ?>
                            <?php else: ?>
                             · Dibuat <?php echo date('d M Y, H:i', strtotime($ml['created_at'])); ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <a href="<?php echo htmlspecialchars($ml['meet_link']); ?>" <?php echo $can_access ? 'target="_blank"' : 'onclick="event.preventDefault();"'; ?> class="meet-btn" style="<?php echo $btn_style; ?>">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                        <?php echo $btn_text; ?>
                    </a>
                </div>
            <?php endforeach; ?>

            <?php foreach ($assignments as $a): ?>
                <?php
        // Check submission
        $check = $pdo->prepare("
            SELECT s.*, 
            (SELECT GROUP_CONCAT(file_path SEPARATOR ',') FROM submission_attachments WHERE submission_id = s.id) as submitted_files 
            FROM submissions s 
            WHERE assignment_id = ? AND student_id = ?
        ");
        $check->execute([$a['id'], $_SESSION['user_id']]);
        $submission = $check->fetch();
        $is_done = $submission ? true : false;
        $is_late = (!$is_done && strtotime($a['deadline']) < time());

        // Attendance Logic
        if ($a['assignment_type'] === 'absensi'):
            // For attendance, check time range
            $now_str = date('H:i:s');
            $can_absen = true;
            $time_msg = "";
            $is_late_period = false;

            if (isset($a['jam_start']) && $a['jam_start']) {
                if ($now_str < $a['jam_start']) {
                    $can_absen = false;
                    $time_msg = "Absensi belum dibuka (Mulai: " . date('H:i', strtotime($a['jam_start'])) . ")";
                }
            }

            // Check Deadline (saved in deadline column)
            if ($a['deadline']) {
                $deadline_ts = strtotime($a['deadline']);
                if (time() > $deadline_ts) {
                    // Absensi DITUTUP â€” siswa tidak bisa lagi mengisi
                    $can_absen = false;
                    $is_late_period = true; // border merah/oranye
                    $time_msg = "Absensi sudah ditutup sejak " . date('H:i', $deadline_ts) . ". Hubungi guru untuk perubahan status.";
                }
                else {
                    if ($can_absen) // Only say opened if not blocked by start time
                        $time_msg = "Absensi DIBUKA sampai " . date('H:i', $deadline_ts);
                }
            }
?>
                    <!-- ATTENDANCE ITEM -->
                    <div class="assign-item <?php echo $is_done ? 'accent-green' : ($is_late_period ? 'accent-amber' : 'accent-blue'); ?>">
                        <div class="assign-top">
                            <div class="assign-title-area">
                                <span class="assign-type-badge" style="background:#eff6ff; color:#1d4ed8;">ABSENSI</span>
                                <div class="assign-title"><?php echo htmlspecialchars($a['title']); ?></div>
                                <?php if ($a['description']): ?>
                                <div class="assign-meta"><?php echo htmlspecialchars($a['description']); ?></div>
                                <?php endif; ?>
                                <?php if ($time_msg): ?>
                                <div style="font-size:0.76rem; color:<?php echo $is_late_period ? '#b45309' : ($can_absen ? '#059669' : '#b45309'); ?>; font-weight:600; margin-top:4px;">
                                    <?php echo $time_msg; ?>
                                </div>
                                <?php endif; ?>
                            </div>
                            <?php if ($is_done): ?>
                                <span class="assign-status-badge badge-done" style="text-transform:capitalize;"><?php echo htmlspecialchars($submission['status']); ?> · <?php echo date('H:i', strtotime($submission['submitted_at'])); ?></span>
                            <?php endif; ?>
                        </div>
                        <?php if (!$is_done): ?>
                            <?php if ($can_absen): ?>
                                <form method="POST" class="attendance-form">
                                    <input type="hidden" name="assignment_id" value="<?php echo $a['id']; ?>">
                                    <input type="hidden" name="submit_attendance" value="1">
                                    <label style="display:block; font-size:0.76rem; font-weight:700; color:#64748b; margin-bottom:8px;">Pilih Status Kehadiran:</label>
                                    <div style="display:flex; gap:8px; flex-wrap:wrap; margin-bottom:10px;">
                                        <label class="status-option"><input type="radio" name="status" value="hadir" checked> Hadir</label>
                                        <label class="status-option"><input type="radio" name="status" value="sakit"> Sakit</label>
                                        <label class="status-option"><input type="radio" name="status" value="izin"> Izin</label>
                                    </div>
                                    <button type="submit" class="upload-submit-btn" style="width:100%;">Kirim Absensi</button>
                                </form>
                            <?php else: ?>
                                <button disabled style="width:100%; background:#e2e8f0; color:#94a3b8; border:none; padding:9px; font-weight:600; cursor:not-allowed; border-radius:7px; font-size:0.84rem;">
                                    <?php echo (isset($a['jam_start']) && $now_str < $a['jam_start']) ? 'Belum Dibuka' : 'Absensi Ditutup'; ?>
                                </button>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>

                <?php
        else:
            // REGULAR ASSIGNMENT (Original Layout)
?>
                
                <div class="assign-item <?php echo $is_done ? 'accent-green' : ($is_late ? 'accent-red' : 'accent-amber'); ?>">
                    <div style="display: flex; justify-content: space-between; align-items: start; flex-wrap: wrap; gap: 1rem;">
                        <div style="flex: 1;">
                            <div style="display: flex; gap: 10px; align-items: center; margin-bottom: 0.5rem;">
                                <?php if ($a['subject_name']): ?>
                                    <span style="background: #e0e7ff; color: #3730a3; padding: 2px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase;">
                                        <?php echo htmlspecialchars($a['subject_name']); ?>
                                    </span>
                                <?php
            endif; ?>
                                <h3 style="font-size: 1.35rem; margin: 0;"><?php echo htmlspecialchars($a['title']); ?></h3>
                            </div>
                            
                            <p style="color: #64748b; font-size: 0.9rem; margin-bottom: 1rem;">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block; vertical-align:middle; line-height:1;"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg> <strong><?php echo htmlspecialchars($a['teacher_name']); ?></strong> &nbsp;|&nbsp; 
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block; vertical-align:middle; line-height:1;"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg> Deadline: <?php echo date('d F Y, H:i', strtotime($a['deadline'])); ?>
                            </p>
                            
                            <div style="background: #f8fafc; padding: 1rem; border-radius: 8px; border: 1px dashed #cbd5e1; margin-bottom: 1rem;">
                                <p style="color: #334155; line-height: 1.6;"><?php echo nl2br(htmlspecialchars($a['description'])); ?></p>
                                
                                <?php if ($a['attachments']):
                $files = explode(',', $a['attachments']);
                foreach ($files as $f):
                    $is_link = (strpos(trim($f), 'http') === 0);
                    $href    = $is_link ? trim($f) : '../../' . trim($f);
                    $label   = $is_link ? 'Buka Link Tugas / Google Form' : 'Download Lampiran';
                    $icon    = $is_link
                        ? '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:middle;"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>'
                        : '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:middle;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>';
                    $btn_style = $is_link
                        ? 'background:#3b82f6; color:white; border:none;'
                        : '';
?>
                                    <div style="margin-top: 10px; padding-top: 10px; border-top: 1px dotted #cbd5e1;">
                                        <a href="<?php echo htmlspecialchars($href); ?>" target="_blank" class="btn btn-secondary" style="font-size: 0.85rem; padding: 6px 12px; display: inline-flex; align-items: center; gap: 5px; <?php echo $btn_style; ?>">
                                            <?php echo $icon; ?> <?php echo $label; ?>
                                        </a>
                                    </div>
                                <?php
                endforeach;
            endif; ?>
                            </div>
                        </div>

                        <!-- Status & Grading Column -->
                        <div style="min-width: 200px; text-align: right;">
                            <?php if ($is_done): ?>
                                <?php if ($submission['grade']): ?>
                                    <div style="text-align: center; background: #f0fdf4; padding: 1rem; border-radius: 12px; border: 1px solid #bbf7d0;">
                                        <div style="font-size: 2.5rem; font-weight: 800; color: #166534; line-height: 1;">
                                            <?php echo $submission['grade']; ?>
                                        </div>
                                        <div style="font-size: 0.8rem; color: #166534; font-weight: 600;">NILAI ANDA</div>
                                    </div>
                                <?php
                else: ?>
                                    <div style="text-align: center; background: #f1f5f9; padding: 1rem; border-radius: 12px; color: #64748b;">
                                        <div style="font-size: 1.5rem;">â³</div>
                                        <small>Menunggu Penilaian</small>
                                    </div>
                                <?php
                endif; ?>
                            <?php
            else: ?>
                                <?php if ($is_late): ?>
                                    <span style="background: #fee2e2; color: #991b1b; padding: 5px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: 700;">TERLAMBAT</span>
                                <?php
                else: ?>
                                    <span style="background: #fef3c7; color: #b45309; padding: 5px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: 700;">BELUM DIKERJAKAN</span>
                                <?php
                endif; ?>
                            <?php
            endif; ?>
                        </div>
                    </div>
                    
                    <!-- submitted info -->
                    <?php if ($is_done): ?>
                        <div class="submitted-notice">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                            Sudah dikumpulkan.
                            <?php if ($submission['submitted_files']):
                    $s_files = explode(',', $submission['submitted_files']);
                    foreach ($s_files as $idx => $sf):
                    ?>
                            &nbsp;<a href="../../<?php echo htmlspecialchars($sf); ?>" target="_blank" style="color:#4f46e5; font-weight:700;">File <?php echo $idx+1; ?></a>
                            <?php endforeach; endif; ?>
                        </div>
                        <?php if (!empty($submission['feedback'])): ?>
                        <div class="feedback-box"><strong>Komentar Guru:</strong> <?php echo htmlspecialchars($submission['feedback']); ?></div>
                        <?php endif; ?>
                    <?php else: ?>
                        <form method="POST" enctype="multipart/form-data" class="upload-area">
                            <input type="hidden" name="assignment_id" value="<?php echo $a['id']; ?>">
                            <input type="hidden" name="submit_assignment" value="1">
                            <div style="flex:1; min-width:200px;">
                                <label style="display:block; font-size:0.76rem; font-weight:700; color:#64748b; margin-bottom:5px;">Upload Jawaban (bisa banyak file):</label>
                                <input type="file" name="files[]" multiple required>
                            </div>
                            <button type="submit" class="upload-submit-btn" style="margin-top:0;">Kirim Tugas</button>
                        </form>
                    <?php endif; ?>
                </div>

                <?php
        endif; // End check assignment type ?>
            <?php
    endforeach; ?>
        </div>
        <?php endif; ?>
        </div><!-- end page-content -->
    </main>
</div>

</body>

