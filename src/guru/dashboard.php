<?php
// src/guru/dashboard.php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'guru') {
    header("Location: ../../login.php");
    exit;
}

$teacher_id = $_SESSION['user_id'];
$success = "";
$error = "";

// ─── Handle Add Assignment (Quick Action) ───
// ─── Handle Add Assignment (Quick Action) ───
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['quick_add_assignment'])) {
    $target_class_id = $_POST['class_id']; // This is teacher_classes.id
    $title = trim($_POST['a_title']);
    $description = trim($_POST['a_description'] ?? '');
    $deadline = $_POST['a_deadline'];

    // Fetch class details to get real class_id and subject_id
    $stmt = $pdo->prepare("SELECT class_id, subject_id FROM teacher_classes WHERE id = ? AND teacher_id = ?");
    $stmt->execute([$target_class_id, $teacher_id]);
    $class_info = $stmt->fetch();

    if ($class_info) {
        $attachment_path = null;
        if (isset($_FILES['a_attachment']) && $_FILES['a_attachment']['error'] == 0) {
            $upload_dir = '../../public/uploads/assignments/';
            if (!file_exists($upload_dir))
                mkdir($upload_dir, 0777, true);
            $file_name = time() . '_' . basename($_FILES['a_attachment']['name']);
            if (move_uploaded_file($_FILES['a_attachment']['tmp_name'], $upload_dir . $file_name)) {
                $attachment_path = 'public/uploads/assignments/' . $file_name;
            }
        }

        $stmt = $pdo->prepare("INSERT INTO assignments (teacher_id, title, description, deadline, attachment_path, status, assignment_type, teacher_class_id, subject_id) VALUES (?, ?, ?, ?, ?, 'active', 'tugas', ?, ?)");
        if ($stmt->execute([$teacher_id, $title, $description, $deadline, $attachment_path, $target_class_id, $class_info['subject_id']])) {
            $assignment_id = $pdo->lastInsertId();
            $pdo->prepare("INSERT INTO assignment_classes (assignment_id, class_id) VALUES (?, ?)")->execute([$assignment_id, $class_info['class_id']]);
            $_SESSION['flash'] = ['type' => 'success', 'message' => "Tugas berhasil dibuat untuk kelas terpilih!"];
        }
        else {
            $_SESSION['flash'] = ['type' => 'error', 'message' => "Gagal membuat tugas."];
        }
    }
    else {
        $_SESSION['flash'] = ['type' => 'error', 'message' => "Kelas tidak valid."];
    }
    header("Location: dashboard.php");
    exit;
}

// ─── Handle Add Material (Quick Action) ───
// ─── Handle Add Material (Quick Action) ───
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['quick_add_material'])) {
    $target_class_id = $_POST['class_id'];
    $title = trim($_POST['m_title']);
    $description = trim($_POST['m_description']);
    $type = $_POST['m_type'];

    // Fetch class details
    $stmt = $pdo->prepare("SELECT class_id, subject_id FROM teacher_classes WHERE id = ? AND teacher_id = ?");
    $stmt->execute([$target_class_id, $teacher_id]);
    $class_info = $stmt->fetch();

    if ($class_info) {
        $file_path = null;
        $material_type = 'pdf'; // Default
        $error_msg = "";

        if ($type === 'file') {
            if (isset($_FILES['m_file']) && $_FILES['m_file']['error'] == 0) {
                if ($_FILES['m_file']['size'] > 20 * 1024 * 1024) {
                    $error_msg = "Ukuran file terlalu besar (Max 20MB).";
                }
                else {
                    $target_dir = "../../public/uploads/materials/";
                    if (!file_exists($target_dir))
                        mkdir($target_dir, 0777, true);
                    $file_name = time() . '_' . basename($_FILES["m_file"]["name"]);
                    $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

                    if (in_array($ext, ['mp4', 'avi', 'mov']))
                        $material_type = 'video';
                    elseif (in_array($ext, ['doc', 'docx']))
                        $material_type = 'word';
                    elseif (in_array($ext, ['ppt', 'pptx']))
                        $material_type = 'ppt';
                    elseif ($ext === 'epub')
                        $material_type = 'epub';

                    if (move_uploaded_file($_FILES["m_file"]["tmp_name"], $target_dir . $file_name)) {
                        $file_path = "public/uploads/materials/" . $file_name;
                    }
                    else {
                        $error_msg = "Gagal upload file.";
                    }
                }
            }
            else {
                $error_msg = "File wajib diupload.";
            }
        }
        elseif ($type === 'link') {
            $link_url = trim($_POST['m_link_url']);
            if (!empty($link_url)) {
                $file_path = $link_url;
                $material_type = 'link';
            }
            else {
                $error_msg = "Link URL wajib diisi.";
            }
        }

        if (empty($error_msg) && $file_path) {
            $stmt = $pdo->prepare("INSERT INTO materials (title, description, type, file_path, teacher_id, teacher_class_id, class_id, subject_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            if ($stmt->execute([$title, $description, $material_type, $file_path, $teacher_id, $target_class_id, $class_info['class_id'], $class_info['subject_id']])) {
                $_SESSION['flash'] = ['type' => 'success', 'message' => "Materi berhasil ditambahkan!"];
            }
            else {
                $_SESSION['flash'] = ['type' => 'error', 'message' => "Database error."];
            }
        }
        else {
            if (!empty($error_msg)) {
                $_SESSION['flash'] = ['type' => 'error', 'message' => $error_msg];
            }
        }
    }
    else {
        $_SESSION['flash'] = ['type' => 'error', 'message' => "Kelas tidak valid."];
    }
    header("Location: dashboard.php");
    exit;
}

$gender = $_SESSION['gender'] ?? '';
$sapaan = "Bapak/Ibu";
if ($gender === 'L')
    $sapaan = "Bapak";
elseif ($gender === 'P')
    $sapaan = "Ibu";

$hour = (int)date('H');
if ($hour < 11)
    $greeting = "Selamat Pagi";
elseif ($hour < 15)
    $greeting = "Selamat Siang";
elseif ($hour < 18)
    $greeting = "Selamat Sore";
else
    $greeting = "Selamat Malam";

// Determine assignment with most ungraded (or just the oldest one) — exclude attendance
$stmt = $pdo->prepare("
    SELECT a.id, COUNT(*) as count 
    FROM submissions s 
    JOIN assignments a ON s.assignment_id = a.id 
    WHERE a.teacher_id = ? AND s.grade IS NULL AND a.assignment_type != 'absensi'
    GROUP BY a.id, a.created_at
    ORDER BY count DESC, a.created_at ASC 
    LIMIT 1
");
$stmt->execute([$teacher_id]);
$priority_assignment = $stmt->fetch();
$priority_assignment_id = $priority_assignment ? $priority_assignment['id'] : null;

// Counts
// Counts - Only count materials from existing teacher classes
$stmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM materials m
    JOIN teacher_classes tc ON m.teacher_class_id = tc.id
    WHERE m.teacher_id = ?
");
$stmt->execute([$teacher_id]);
$my_materials = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM assignments WHERE teacher_id = ? AND deadline > NOW() AND assignment_type != 'absensi'");
$stmt->execute([$teacher_id]);
$active_assignments = $stmt->fetchColumn();

$stmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM submissions s 
    JOIN assignments a ON s.assignment_id = a.id 
    JOIN teacher_classes tc ON a.teacher_class_id = tc.id
    WHERE a.teacher_id = ? AND a.assignment_type != 'absensi'
");
$stmt->execute([$teacher_id]);
$total_submissions = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM teacher_classes WHERE teacher_id = ?");
$stmt->execute([$teacher_id]);
$my_classes_count = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM submissions s JOIN assignments a ON s.assignment_id = a.id WHERE a.teacher_id = ? AND s.grade IS NULL AND a.assignment_type != 'absensi'");
$stmt->execute([$teacher_id]);
$ungraded = $stmt->fetchColumn();

// Fetch Recent Classes
$stmt = $pdo->prepare("
    SELECT tc.*, c.name as school_class_name,
    (SELECT COUNT(*) FROM users u WHERE u.class_id = tc.class_id AND u.role = 'siswa') as student_count
    FROM teacher_classes tc JOIN classes c ON tc.class_id = c.id
    WHERE tc.teacher_id = ? ORDER BY tc.created_at DESC LIMIT 5
");
$stmt->execute([$teacher_id]);
$recent_classes = $stmt->fetchAll();

// Fetch All Classes for Dropdowns
$stmt = $pdo->prepare("SELECT id, name, subject FROM teacher_classes WHERE teacher_id = ? ORDER BY created_at DESC");
$stmt->execute([$teacher_id]);
$dropdown_classes = $stmt->fetchAll();

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
    <title>Dashboard Guru</title>
    <link rel="stylesheet" href="/public/assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* * { font-family: 'Inter', system-ui, -apple-system, sans-serif; } */
        .main-content {
            max-width: 100% !important;
            background: #f5f7fb !important;

            padding: 0 !important;
        }

        /* ─── Hero guru: biru (selaras modul global, beda dari teal siswa) ─── */
        .db-hero {
            position: relative;
            background: linear-gradient(135deg, #1e3a8a 0%, #1d4ed8 45%, #3b82f6 100%);
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
            background: radial-gradient(circle, rgba(147, 197, 253, 0.35) 0%, transparent 60%);
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

        /* ─── Content Area ─── */
        .db-content {
            position: relative;
            margin-top: -3rem;
            padding: 0 3rem 3rem;
            z-index: 10;
        }

        /* ─── Stats ─── */
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
        .db-stat.c-blue::after   { background: linear-gradient(90deg, #3b82f6, #93c5fd); }
        .db-stat.c-violet::after { background: linear-gradient(90deg, #7c3aed, #c4b5fd); }
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
        .db-stat.c-blue .num   { color: #2563eb; }
        .db-stat.c-violet .num { color: #7c3aed; }
        .db-stat.c-amber .num  { color: #d97706; }
        .db-stat.c-green .num  { color: #059669; }
        .db-stat .lbl {
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #94a3b8;
        }

        /* ─── Alert ─── */
        .db-alert {
            background: linear-gradient(135deg, #fffbeb, #fef3c7);
            border: 1px solid #fde68a;
            border-radius: 14px;
            padding: 14px 22px;
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 20px;
            font-size: 0.88rem;
            color: #78350f;
            animation: fade-up 0.4s ease-out 0.25s both;
        }
        .db-alert strong { color: #92400e; }
        .db-alert a {
            margin-left: auto;
            color: #92400e;
            font-weight: 700;
            text-decoration: none;
            background: rgba(146,64,14,0.08);
            padding: 7px 18px;
            border-radius: 10px;
            font-size: 0.8rem;
            transition: background 0.15s;
            white-space: nowrap;
        }
        .db-alert a:hover { background: rgba(146,64,14,0.14); }

        /* ─── Grid ─── */
        .db-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            animation: fade-up 0.4s ease-out 0.3s both;
        }

        /* ─── Panel ─── */
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

        /* ─── Quick Actions ─── */
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
            cursor: pointer;
        }
        .qa-item:hover {
            background: #eef2ff;
            border-color: #c7d2fe;
            transform: translateX(4px);
            box-shadow: 0 2px 12px rgba(79,70,229,0.06);
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
        .qa-item:nth-child(2) .qa-ico { background: #ede9fe; }
        .qa-item:nth-child(3) .qa-ico { background: #fef3c7; }
        .qa-item:nth-child(4) .qa-ico { background: #d1fae5; }
        .qa-title {
            font-size: 0.88rem;
            font-weight: 600;
            color: #1e293b;
        }
        .qa-desc {
            font-size: 0.75rem;
            color: #94a3b8;
            margin-top: 2px;
        }
        .qa-arrow {
            margin-left: auto;
            color: #cbd5e1;
            font-size: 1.2rem;
            transition: all 0.2s;
        }
        .qa-item:hover .qa-arrow { color: #6366f1; transform: translateX(3px); }

        /* ─── Class List ─── */
        .cls-row {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 13px 0;
            border-bottom: 1px solid #f1f5f9;
            text-decoration: none;
            color: inherit;
            transition: all 0.2s;
        }
        .cls-row:last-child { border-bottom: none; }
        .cls-row:hover { padding-left: 6px; }
        .cls-av {
            width: 42px; height: 42px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 0.8rem;
            color: #fff;
            flex-shrink: 0;
        }
        .cls-name {
            font-size: 0.88rem;
            font-weight: 600;
            color: #1e293b;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .cls-subj {
            font-size: 0.75rem;
            color: #94a3b8;
            margin-top: 2px;
        }
        .cls-badge {
            font-size: 0.72rem;
            font-weight: 600;
            color: #64748b;
            background: #f1f5f9;
            padding: 5px 12px;
            border-radius: 20px;
            white-space: nowrap;
        }
        .cls-empty {
            text-align: center;
            padding: 2.5rem 1rem;
            color: #94a3b8;
        }
        .cls-empty a {
            color: #6366f1;
            font-weight: 600;
            text-decoration: none;
        }
        .view-all-link {
            display: block;
            text-align: center;
            margin-top: 14px;
            padding-top: 14px;
            border-top: 1px solid #f1f5f9;
            color: #6366f1;
            font-weight: 600;
            font-size: 0.8rem;
            text-decoration: none;
        }
        .view-all-link:hover { color: #4338ca; }

        /* Modal Styles */
        .modal {
            display: none; 
            position: fixed; 
            z-index: 1000; 
            left: 0;
            top: 0;
            width: 100%; 
            height: 100%; 
            overflow: auto; 
            background-color: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(8px);
        }
        .modal-content {
            background-color: #fff;
            margin: 5% auto; 
            padding: 2.5rem;
            border-radius: 20px;
            width: 90%;
            max-width: 600px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            animation: modalSlideIn 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            position: relative;
        }
        @keyframes modalSlideIn {
            from {transform: translateY(20px) scale(0.95); opacity: 0;}
            to {transform: translateY(0) scale(1); opacity: 1;}
        }
        .close {
            position: absolute;
            right: 24px;
            top: 24px;
            width: 32px;
            height: 32px;
            background: #f1f5f9;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #64748b;
            cursor: pointer;
            transition: all 0.2s;
            font-size: 20px;
            line-height: 1;
        }
        .close:hover { background: #e2e8f0; color: #1e293b; }

        /* Form styling */
        .form-group { margin-bottom: 20px; }
        .form-group label {
            font-weight: 600;
            color: #334155;
            margin-bottom: 8px;
            display: block;
            font-size: 0.9rem;
        }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 12px 14px;
            font-weight: 500;
            transition: all 0.2s;
            background: #fff;
            color: #1e293b;
            font-family: inherit;
        }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
            outline: none;
        }
        .btn {
            background: #4f46e5;
            color: white;
            border: none;
            padding: 14px 20px;
            border-radius: 12px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
            width: 100%;
            display: block;
            text-align: center;
        }
        .btn:hover { background: #4338ca; transform: translateY(-2px); }

        @media (max-width: 900px) {
            .db-stats { grid-template-columns: repeat(2, 1fr); }
            .db-grid { grid-template-columns: 1fr; }
            .db-hero { padding: 2rem 1.5rem 5rem; }
            .db-content { padding: 0 1.5rem 2rem; }
        }

        @media (max-width: 768px) {
            .db-hero {
                padding: 1.2rem 1rem 3.4rem;
                border-bottom-right-radius: 24px;
            }
            .hero-inner {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.55rem;
            }
            .hero-inner h1 {
                font-size: 1.15rem;
                line-height: 1.35;
                margin-bottom: 0.2rem;
            }
            .hero-sub {
                font-size: 0.78rem;
            }
            .hero-date {
                text-align: left;
                font-size: 0.72rem;
                opacity: 0.9;
            }

            .db-content {
                margin-top: -2rem;
                padding: 0 0.85rem 1rem;
            }
            .db-stats {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 0.65rem;
                margin-bottom: 0.8rem;
            }
            .db-stat {
                border-radius: 12px;
                padding: 0.85rem 0.75rem;
            }
            .db-stat .num {
                font-size: 1.35rem;
                margin-bottom: 0.3rem;
            }
            .db-stat .lbl {
                font-size: 0.62rem;
                letter-spacing: 0.05em;
            }

            .db-alert {
                padding: 0.7rem 0.8rem;
                border-radius: 10px;
                gap: 0.6rem;
                font-size: 0.74rem;
                align-items: flex-start;
                flex-wrap: wrap;
            }
            .db-alert a {
                margin-left: 0;
                width: 100%;
                text-align: center;
                font-size: 0.72rem;
                padding: 0.5rem 0.7rem;
            }

            .db-grid {
                gap: 0.75rem;
            }
            .db-panel {
                border-radius: 12px;
                padding: 0.85rem;
            }
            .db-panel h3 {
                font-size: 0.68rem;
                margin-bottom: 0.65rem;
                padding-bottom: 0.55rem;
            }

            .qa-item {
                gap: 0.65rem;
                padding: 0.62rem;
                border-radius: 10px;
            }
            .qa-ico {
                width: 32px;
                height: 32px;
                border-radius: 8px;
                font-size: 0.9rem;
            }
            .qa-title {
                font-size: 0.76rem;
            }
            .qa-desc {
                font-size: 0.66rem;
                line-height: 1.3;
            }
            .qa-arrow {
                font-size: 1rem;
            }

            .cls-row {
                gap: 0.55rem;
                padding: 0.55rem 0;
                align-items: flex-start;
            }
            .cls-av {
                width: 30px;
                height: 30px;
                border-radius: 8px;
                font-size: 0.63rem;
            }
            .cls-name {
                font-size: 0.72rem;
                white-space: normal;
                line-height: 1.3;
            }
            .cls-subj {
                font-size: 0.63rem;
            }
            .cls-badge {
                font-size: 0.58rem;
                padding: 0.25rem 0.45rem;
            }

            .modal-content {
                width: calc(100% - 1rem);
                margin: 0.5rem auto;
                max-height: calc(100dvh - 1rem);
                overflow-y: auto;
                border-radius: 14px;
                padding: 1rem;
            }
            .close {
                right: 12px;
                top: 12px;
                width: 28px;
                height: 28px;
            }
            .form-group {
                margin-bottom: 0.75rem;
            }
            .form-group input, .form-group select, .form-group textarea {
                padding: 10px 11px;
                font-size: 16px;
            }
        }
    </style>
</head>
<body>

<div class="app-container">
    <?php include '../templates/sidebar.php'; ?>
    
    <main class="main-content">

        <!-- Hero -->
        <!-- Hero -->
        <div class="db-hero">
            <div class="hero-inner">
                <div>
                    <h1><?php echo $greeting; ?>, <?php echo $sapaan . ' ' . htmlspecialchars($_SESSION['full_name']); ?>!</h1>
                    <?php if (!empty($_SESSION['nip'])): ?>
                        <div style="color: rgba(255,255,255,0.7); font-size: 0.9rem; margin-bottom: 4px; font-weight: 500;">NIP: <?php echo htmlspecialchars($_SESSION['nip']); ?></div>
                    <?php
endif; ?>
                    <p class="hero-sub">Ringkasan aktivitas mengajar Anda hari ini</p>
                </div>
                <div class="hero-date"><?php echo $dateStr; ?></div>
            </div>
        </div>

        <div class="db-content">
            <?php
if (isset($_SESSION['flash'])) {
    $flash = $_SESSION['flash'];
    $bg = '#f0fdf4';
    $color = '#166534';
    $border = '#bbf7d0'; // Success default
    if ($flash['type'] == 'error') {
        $bg = '#fef2f2';
        $color = '#991b1b';
        $border = '#fecaca';
    }
    echo "<div style='background:$bg; color:$color; padding:16px; border-radius:12px; margin-bottom:24px; border:1px solid $border; box-shadow:0 4px 6px -1px rgba(0,0,0,0.05);'>
                        " . ($flash['type'] == 'error' ? "<svg width='20' height='20' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' style='display:inline-block; vertical-align:middle; line-height:1;'><path d=\"m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z\"/><line x1=\"12\" y1=\"9\" x2=\"12\" y2=\"13\"/><line x1=\"12\" y1=\"17\" x2=\"12.01\" y2=\"17\"/></svg> " : "<svg width='20' height='20' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' style='display:inline-block; vertical-align:middle; line-height:1;'><polyline points=\"20 6 9 17 4 12\"/></svg> ") . htmlspecialchars($flash['message']) . "
                      </div>";
    unset($_SESSION['flash']);
}
?>

            <!-- Stats -->
            <div class="db-stats">
                <div class="db-stat c-blue">
                    <div class="num"><?php echo $my_classes_count; ?></div>
                    <div class="lbl">Kelas Saya</div>
                </div>
                <div class="db-stat c-violet">
                    <div class="num"><?php echo $my_materials; ?></div>
                    <div class="lbl">Materi</div>
                </div>
                <div class="db-stat c-amber">
                    <div class="num"><?php echo $active_assignments; ?></div>
                    <div class="lbl">Tugas Aktif</div>
                </div>
                <div class="db-stat c-green">
                    <div class="num"><?php echo $total_submissions; ?></div>
                    <div class="lbl">Tugas Masuk</div>
                </div>
            </div>

            <!-- Alert -->
            <?php if ($ungraded > 0): ?>
            <div class="db-alert">
                <span style="font-size:1.2rem;"><svg width='20' height='20' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' style='display:inline-block; vertical-align:middle; line-height:1;'><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg></span>
                <span>Ada <strong><?php echo $ungraded; ?> tugas</strong> siswa yang belum dinilai.</span>
                <?php if ($priority_assignment_id): ?>
                    <a href="view_submissions.php?assignment_id=<?php echo $priority_assignment_id; ?>">Nilai Sekarang →</a>
                <?php
    else: ?>
                    <a href="kelas.php">Lihat Kelas →</a>
                <?php
    endif; ?>
            </div>
            <?php
endif; ?>

            <!-- Grid -->
            <div class="db-grid">

                <!-- Quick Actions -->
                <div class="db-panel">
                    <h3>Menu Cepat</h3>
                    <div class="qa-list">
                        <a href="kelas.php" class="qa-item">
                            <div class="qa-ico"><svg width='20' height='20' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' style='display:inline-block; vertical-align:middle; line-height:1;'><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/></svg></div>
                            <div>
                                <div class="qa-title">Kelas Saya</div>
                                <div class="qa-desc">Kelola kelas dan lihat daftar siswa</div>
                            </div>
                            <span class="qa-arrow">›</span>
                        </a>
                        <!-- Upload Material Trigger -->
                        <div class="qa-item" onclick="openModal('addMaterialModal')">
                            <div class="qa-ico"><svg width='20' height='20' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' style='display:inline-block; vertical-align:middle; line-height:1;'><path d='M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z'/></svg></div>
                            <div>
                                <div class="qa-title">Upload Materi</div>
                                <div class="qa-desc">Bagikan modul atau video ke kelas</div>
                            </div>
                            <span class="qa-arrow">+</span>
                        </div>
                        <!-- Add Assignment Trigger -->
                        <div class="qa-item" onclick="openModal('addAssignmentModal')">
                            <div class="qa-ico"><svg width='20' height='20' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' style='display:inline-block; vertical-align:middle; line-height:1;'><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg></div>
                            <div>
                                <div class="qa-title">Buat Tugas</div>
                                <div class="qa-desc">Berikan tugas baru untuk siswa</div>
                            </div>
                            <span class="qa-arrow">+</span>
                        </div>
                        <a href="jadwal_mengajar.php" class="qa-item">
                            <div class="qa-ico"><svg width='20' height='20' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' style='display:inline-block; vertical-align:middle; line-height:1;'><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg></div>
                            <div>
                                <div class="qa-title">Jadwal Mengajar</div>
                                <div class="qa-desc">Lihat jadwal pelajaran Anda</div>
                            </div>
                            <span class="qa-arrow">›</span>
                        </a>
                        <a href="../profile.php" class="qa-item">
                            <div class="qa-ico"><svg width='20' height='20' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' style='display:inline-block; vertical-align:middle; line-height:1;'><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></div>
                            <div>
                                <div class="qa-title">Profil Saya</div>
                                <div class="qa-desc">Ubah data diri dan password</div>
                            </div>
                            <span class="qa-arrow">›</span>
                        </a>
                    </div>
                </div>

                <!-- Recent Classes -->
                <div class="db-panel">
                    <h3>Kelas Terakhir</h3>
                    <?php if (empty($recent_classes)): ?>
                        <div class="cls-empty">
                            <p style="font-size:2.5rem; margin-bottom:10px;"><svg width='20' height='20' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' style='display:inline-block; vertical-align:middle; line-height:1;'><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg></p>
                            <p style="margin-bottom:8px;">Belum ada kelas yang dibuat.</p>
                            <a href="kelas.php">+ Buat Kelas Pertama</a>
                        </div>
                    <?php
else: ?>
                        <?php
    $avatarColors = ['#4f46e5', '#0891b2', '#d97706', '#059669', '#db2777'];
    foreach ($recent_classes as $i => $rc):
        $initials = strtoupper(mb_substr($rc['name'], 0, 2));
        $bg = $avatarColors[$i % count($avatarColors)];
?>
                        <a href="view_class.php?id=<?php echo $rc['id']; ?>" class="cls-row">
                            <div class="cls-av" style="background:<?php echo $bg; ?>"><?php echo $initials; ?></div>
                            <div style="flex:1; min-width:0;">
                                <div class="cls-name"><?php echo htmlspecialchars($rc['name']); ?></div>
                                <div class="cls-subj"><?php echo htmlspecialchars($rc['subject']); ?></div>
                            </div>
                            <div class="cls-badge"><svg width='20' height='20' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' style='display:inline-block; vertical-align:middle; line-height:1;'><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg> <?php echo $rc['student_count']; ?> siswa</div>
                        </a>
                        <?php
    endforeach; ?>
                        <?php if ($my_classes_count > 5): ?>
                            <a href="kelas.php" class="view-all-link">Lihat Semua Kelas →</a>
                        <?php
    endif; ?>
                    <?php
endif; ?>
                </div>
            </div>

        </div>
        </div>
    </main>
</div>

<!-- ================= MODALS ================= -->

<!-- Add Assignment Modal -->
<div id="addAssignmentModal" class="modal">
    <div class="modal-content">
        <div class="close" onclick="closeModal('addAssignmentModal')">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:20px;height:20px;">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </div>
        <h2 style="margin-bottom: 0.5rem; color:#1e293b;"><svg width='20' height='20' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' style='display:inline-block; vertical-align:middle; line-height:1;'><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg> Buat Tugas Baru</h2>
        <p style="color:#64748b; margin-bottom: 1.5rem;">Tugas akan muncul di halaman siswa.</p>
        
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="quick_add_assignment" value="1">
            
            <div class="form-group">
                <label>Pilih Kelas</label>
                <select name="class_id" required>
                    <option value="">-- Pilih Kelas Tujuan --</option>
                    <?php foreach ($dropdown_classes as $dc): ?>
                        <option value="<?php echo $dc['id']; ?>"><?php echo htmlspecialchars($dc['name']) . ' - ' . htmlspecialchars($dc['subject']); ?></option>
                    <?php
endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Judul Tugas</label>
                <input type="text" name="a_title" required placeholder="Contoh: Analisis Ekosistem">
            </div>

            <div class="form-group">
                <label>Deskripsi / Instruksi</label>
                <textarea name="a_description" rows="4" placeholder="Jelaskan detail tugas di sini..."></textarea>
            </div>

            <div class="form-group">
                <label>Tenggat Waktu (Deadline)</label>
                <input type="datetime-local" name="a_deadline" required>
            </div>

            <div class="form-group">
                <label>Lampiran File (Opsional)</label>
                <input type="file" name="a_attachment">
                <small style="color:#64748b;">PDF, Word, Excel, Gambar (Max 10MB)</small>
            </div>

            <div style="display: flex; gap: 12px; margin-top: 1.5rem;">
                <button type="button" class="btn" onclick="closeModal('addAssignmentModal')" style="flex: 1; background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0;">Batal</button>
                <button type="submit" class="btn" style="flex: 2;"><svg width='20' height='20' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' style='display:inline-block; vertical-align:middle; line-height:1;'><path d='M4.5 16.5c-1.5 1.26-2 5-2 5s3.74-.5 5-2c.71-.84.7-2.13-.09-2.91a2.18 2.18 0 0 0-2.91-.09z'/><path d='m12 15-3-3a22 22 0 0 1 2-3.95A12.88 12.88 0 0 1 22 2c0 2.72-.78 7.5-6 11a22.35 22.35 0 0 1-4 2z'/><path d='M9 12H4s.55-3.03 2-4c1.62-1.08 5 0 5 0'/><path d='M12 15v5s3.03-.55 4-2c1.08-1.62 0-5 0-5'/></svg> Terbitkan Tugas</button>
            </div>
        </form>
    </div>
</div>

<!-- Add Material Modal -->
<div id="addMaterialModal" class="modal">
    <div class="modal-content">
        <div class="close" onclick="closeModal('addMaterialModal')">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:20px;height:20px;">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </div>
        <h2 style="margin-bottom: 0.5rem; color:#1e293b;"><svg width='20' height='20' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' style='display:inline-block; vertical-align:middle; line-height:1;'><path d='M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z'/></svg> Upload Materi Belajar</h2>
        <p style="color:#64748b; margin-bottom: 1.5rem;">Bagikan bahan ajar ke kelas Anda.</p>
        
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="quick_add_material" value="1">
            
            <div class="form-group">
                <label>Pilih Kelas</label>
                <select name="class_id" required>
                    <option value="">-- Pilih Kelas Tujuan --</option>
                    <?php foreach ($dropdown_classes as $dc): ?>
                        <option value="<?php echo $dc['id']; ?>"><?php echo htmlspecialchars($dc['name']) . ' - ' . htmlspecialchars($dc['subject']); ?></option>
                    <?php
endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Judul Materi</label>
                <input type="text" name="m_title" required placeholder="Contoh: Modul Bab 1 - Pengantar">
            </div>

            <div class="form-group">
                <label>Deskripsi Singkat</label>
                <textarea name="m_description" rows="3" placeholder="Deskripsi materi..."></textarea>
            </div>

            <div class="form-group">
                <label>Tipe Materi</label>
                <select name="m_type" onchange="toggleMaterialInput(this.value)">
                    <option value="file"><svg width='20' height='20' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' style='display:inline-block; vertical-align:middle; line-height:1;'><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg> Upload File (PDF/Word/PPT)</option>
                    <option value="link"><svg width='20' height='20' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' style='display:inline-block; vertical-align:middle; line-height:1;'><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg> Link (Youtube/Drive/Zoom)</option>
                </select>
            </div>

            <div id="material-file-input" class="form-group">
                <label>Upload File</label>
                <input type="file" name="m_file">
                <small style="color:#64748b;">Max 20MB</small>
            </div>

            <div id="material-link-input" class="form-group" style="display:none;">
                <label>Paste Link URL</label>
                <input type="url" name="m_link_url" placeholder="https://...">
            </div>

            <div style="display: flex; gap: 12px; margin-top: 1.5rem;">
                <button type="button" class="btn" onclick="closeModal('addMaterialModal')" style="flex: 1; background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0;">Batal</button>
                <button type="submit" class="btn" style="flex: 2;"><svg width='20' height='20' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' style='display:inline-block; vertical-align:middle; line-height:1;'><path d='M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4'/><polyline points='17 8 12 3 7 8'/><line x1='12' y1='3' x2='12' y2='15'/></svg> Upload Materi</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openModal(id) {
        document.getElementById(id).style.display = 'block';
    }
    function closeModal(id) {
        document.getElementById(id).style.display = 'none';
    }
    function toggleMaterialInput(val) {
        if(val === 'link') {
            document.getElementById('material-file-input').style.display = 'none';
            document.getElementById('material-link-input').style.display = 'block';
        } else {
            document.getElementById('material-file-input').style.display = 'block';
            document.getElementById('material-link-input').style.display = 'none';
        }
    }
    window.onclick = function(event) {
        if (event.target.classList.contains('modal')) {
            event.target.style.display = "none";
        }
    }
</script>

</body>
</html>
