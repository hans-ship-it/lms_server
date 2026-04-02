<?php
// src/guru/kelas.php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'guru') {
    header("Location: ../../login.php");
    exit;
}

$teacher_id = $_SESSION['user_id'];
$success = "";
$error = "";

// Handle Create Class
// Handle Create Class
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_class'])) {
    $class_id = $_POST['class_id'] ?? null;
    $subject_id = $_POST['subject_id'] ?? null;
    $class_name_custom = trim($_POST['class_name_custom'] ?? '');
    
    $special_grade_type = $_POST['special_grade_type'] ?? 'regular';
    $is_special_class = ($special_grade_type !== 'regular') ? 1 : 0;
    $special_grade_level = ($is_special_class && $special_grade_type !== 'lintas') ? $special_grade_type : null;

    if (empty($subject_id) || empty($class_name_custom)) {
        $_SESSION['flash'] = ['type' => 'error', 'message' => "Mata pelajaran dan nama kelas harus diisi."];
    }
    elseif ($is_special_class == 0 && empty($class_id)) {
        $_SESSION['flash'] = ['type' => 'error', 'message' => "Target kelas sekolah harus dipilih untuk Kelas Reguler."];
    }
    else {
        try {
            // Fetch subject name
            $stmt = $pdo->prepare("SELECT name FROM subjects WHERE id = ?");
            $stmt->execute([$subject_id]);
            $subject_name = $stmt->fetchColumn();

            // Generate Folder Name: YYYY-MM-DD ClassName
            $date_prefix = date('Y-m-d');
            $safe_name = preg_replace('/[^A-Za-z0-9_\-]/', '_', $class_name_custom);
            $folder_name = $date_prefix . ' ' . $safe_name;

            // Create Directory
            $base_dir = "../../public/uploads/classes/" . $folder_name;
            if (!file_exists($base_dir)) {
                mkdir($base_dir, 0777, true);
                mkdir($base_dir . "/materi", 0777, true);
                mkdir($base_dir . "/tugas", 0777, true);
            }

            $class_id_val = $is_special_class ? null : $class_id;

            $stmt = $pdo->prepare("INSERT INTO teacher_classes (teacher_id, class_id, name, subject, subject_id, folder_name, is_special_class, special_grade_level) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$teacher_id, $class_id_val, $class_name_custom, $subject_name, $subject_id, $folder_name, $is_special_class, $special_grade_level]);
            $_SESSION['flash'] = ['type' => 'success', 'message' => "Kelas berhasil dibuat!"];
        }
        catch (PDOException $e) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => "Gagal membuat kelas: " . $e->getMessage()];
        }
    }
    header("Location: kelas.php");
    exit;
}

// Fetch Teacher's Classes with Grade Level
$stmt = $pdo->prepare("
    SELECT tc.*, c.name as school_class_name, 
    COALESCE(c.grade_level, tc.special_grade_level) as computed_grade_level,
    (SELECT COUNT(*) FROM class_members cm WHERE cm.teacher_class_id = tc.id) as special_student_count,
    (SELECT COUNT(*) FROM users u WHERE u.class_id = tc.class_id AND u.role = 'siswa') as regular_student_count
    FROM teacher_classes tc
    LEFT JOIN classes c ON tc.class_id = c.id
    WHERE tc.teacher_id = ?
    ORDER BY COALESCE(c.grade_level, tc.special_grade_level, 99) ASC, tc.created_at DESC
");
$stmt->execute([$teacher_id]);
$all_my_classes = $stmt->fetchAll();

// Group by Grade Level
$grouped_classes = [
    '10' => [],
    '11' => [],
    '12' => [],
    'Others' => [] // For any class without strict 10/11/12
];

foreach ($all_my_classes as $class) {
    if (in_array($class['computed_grade_level'], ['10', '11', '12'])) {
        $grouped_classes[$class['computed_grade_level']][] = $class;
    }
    else {
        // Includes lintas kelas (special_grade_level = null)
        $grouped_classes['Others'][] = $class;
    }
}

// Fetch All School Classes for Dropdown
$stmt = $pdo->query("SELECT * FROM classes ORDER BY grade_level, LENGTH(name), name");
$all_classes = $stmt->fetchAll();

// Fetch All Subjects for Dropdown
$stmt = $pdo->query("SELECT * FROM subjects ORDER BY name ASC");
$all_subjects = $stmt->fetchAll();

// Helper for Subject Colors
function getSubjectStyle($subjectName)
{
    // Define a palette of nice gradients
    $gradients = [
        'blue' => 'linear-gradient(135deg, #3b82f6 0%, #2563eb 100%)',
        'indigo' => 'linear-gradient(135deg, #6366f1 0%, #4f46e5 100%)',
        'purple' => 'linear-gradient(135deg, #a855f7 0%, #9333ea 100%)',
        'pink' => 'linear-gradient(135deg, #ec4899 0%, #db2777 100%)',
        'orange' => 'linear-gradient(135deg, #f97316 0%, #ea580c 100%)',
        'teal' => 'linear-gradient(135deg, #14b8a6 0%, #0d9488 100%)',
        'green' => 'linear-gradient(135deg, #22c55e 0%, #16a34a 100%)',
        'red' => 'linear-gradient(135deg, #ef4444 0%, #dc2626 100%)',
        'cyan' => 'linear-gradient(135deg, #06b6d4 0%, #0891b2 100%)',
    ];

    // Map common subjects to specific colors
    $subjectLower = strtolower($subjectName);
    if (strpos($subjectLower, 'matematika') !== false)
        return $gradients['blue'];
    if (strpos($subjectLower, 'biologi') !== false)
        return $gradients['green'];
    if (strpos($subjectLower, 'fisika') !== false)
        return $gradients['indigo'];
    if (strpos($subjectLower, 'kimia') !== false)
        return $gradients['purple'];
    if (strpos($subjectLower, 'sejarah') !== false)
        return $gradients['orange'];
    if (strpos($subjectLower, 'bahasa') !== false)
        return $gradients['teal'];
    if (strpos($subjectLower, 'inggris') !== false)
        return $gradients['pink'];
    if (strpos($subjectLower, 'ekonomi') !== false)
        return $gradients['cyan'];
    if (strpos($subjectLower, 'geografi') !== false)
        return $gradients['teal'];
    if (strpos($subjectLower, 'sosiologi') !== false)
        return $gradients['orange'];
    if (strpos($subjectLower, 'pk') !== false)
        return $gradients['red'];

    // Fallback: Use hash to pick a color deterministically
    $keys = array_keys($gradients);
    $hash = crc32($subjectName);
    $index = abs($hash) % count($keys);
    return $gradients[$keys[$index]];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelas Saya - Guru</title>
    <link rel="stylesheet" href="/public/assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .grade-section {
            margin-bottom: 40px;
        }
        .grade-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 10px;
        }
        .grade-badge {
            background: #1e293b;
            color: #fff;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 0.85rem;
        }
        .class-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 24px;
        }
        .class-card {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 10px 15px -3px rgba(0, 0, 0, 0.05);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid rgba(226, 232, 240, 0.8);
            display: flex;
            flex-direction: column;
            text-decoration: none;
            color: inherit;
            position: relative;
        }
        .class-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
        }
        .class-header {
            padding: 24px;
            position: relative;
            color: white;
            min-height: 120px;
            display: flex;
            flex-direction: column;
            /* justify-content: flex-end; Removed to allow natural stacking */
        }
        /* Pattern overlay for header */
        .class-header::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image: radial-gradient(rgba(255, 255, 255, 0.2) 1.5px, transparent 1.5px);
            background-size: 12px 12px;
            opacity: 0.4;
        }
        .class-header h3 {
            margin: 0;
            font-size: 1.35rem;
            font-weight: 700;
            position: relative;
            z-index: 2;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
            line-height: 1.3;
        }
        .class-header p {
            margin: 6px 0 0;
            opacity: 0.95;
            font-size: 0.95rem;
            position: relative;
            z-index: 2;
            font-weight: 500;
        }
        .subject-badge {
            /* Changed from absolute to relative flex item to prevent overlap */
            align-self: flex-end;
            margin-bottom: auto; /* Pushes content below it to the bottom */
            
            background: rgba(255,255,255,0.25);
            backdrop-filter: blur(4px);
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            border: 1px solid rgba(255,255,255,0.3);
            z-index: 2;
        }
        .class-body {
            padding: 20px 24px;
            flex-grow: 1;
            background: #fff;
        }
        .class-info-row {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 12px;
            color: #64748b;
            font-size: 0.9rem;
        }
        .class-info-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            background: #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            color: #475569;
        }
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
            max-width: 500px;
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
        .form-group label {
            font-weight: 600;
            color: #334155;
            margin-bottom: 8px;
            display: block;
        }
        .form-group input, .form-group select {
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            padding: 10px 14px;
            font-weight: 500;
            transition: all 0.2s;
        }
        .form-group input:focus, .form-group select:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }
    </style>
</head>
<body class="admin-full-layout">

<div class="app-container">
    <?php include '../templates/sidebar.php'; ?>
    
    <main class="main-content">
        <!-- Dashboard Hero -->
        <div class="dashboard-hero">
            <div class="dashboard-container hero-top-container">
                <div class="hero-text-container">
                    <h1 style="color: white; margin-bottom: 0.5rem;">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:middle;"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/></svg>
                        Kelas Saya
                    </h1>
                    <p style="color: rgba(255,255,255,0.8);">Kelola kelas dan materi pembelajaran Anda.</p>
                </div>
                <div class="hero-actions-container">
                    <div class="hero-search-box">
                        <span style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #64748b; pointer-events: none;"><svg width='18' height='18' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><circle cx='11' cy='11' r='8'/><line x1='21' y1='21' x2='16.65' y2='16.65'/></svg></span>
                        <input type="text" id="classSearchInput" class="hero-search-input" placeholder="Cari kelas atau mapel...">
                    </div>
                    <button onclick="document.getElementById('addClassModal').style.display='block'" class="btn btn-hero-action">
                        + Buat Kelas Baru
                    </button>
                </div>
            </div>
            
            <div style="position: absolute; right: -50px; top: -50px; width: 250px; height: 250px; background: rgba(255,255,255,0.05); border-radius: 50%; pointer-events: none;"></div>
            <div style="position: absolute; right: 100px; bottom: -80px; width: 150px; height: 150px; background: rgba(255,255,255,0.05); border-radius: 50%; pointer-events: none;"></div>
        </div>

        <div class="content-overlap">
            <div class="dashboard-container">

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

    echo "<div style='background:$bg; color:$color; padding:16px; border-radius:12px; margin-bottom:24px; border:1px solid $border; display:flex; align-items:center; gap:10px;'>
                    " . ($flash['type'] == 'error' ? "<svg width='20' height='20' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' style='display:inline-block; vertical-align:middle; line-height:1;'><path d=\"m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z\"/><line x1=\"12\" y1=\"9\" x2=\"12\" y2=\"13\"/><line x1=\"12\" y1=\"17\" x2=\"12.01\" y2=\"17\"/></svg> " : "<svg width='20' height='20' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' style='display:inline-block; vertical-align:middle; line-height:1;'><polyline points=\"20 6 9 17 4 12\"/></svg> ") . htmlspecialchars($flash['message']) . "
                  </div>";
    unset($_SESSION['flash']);
}
?>

        <?php if (empty($all_my_classes)): ?>
            <div style="text-align: center; padding: 5rem 2rem; background: white; border-radius: 24px; border: 2px dashed #cbd5e1; max-width: 600px; margin: 40px auto;">
                <div style="font-size: 4rem; margin-bottom: 1.5rem; opacity: 0.8;"><svg width='20' height='20' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' style='display:inline-block; vertical-align:middle; line-height:1;'><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/></svg></div>
                <h3 style="color: #1e293b; font-size: 1.5rem; font-weight: 700; margin-bottom: 0.75rem;">Belum ada kelas aktif</h3>
                <p style="color: #64748b; margin-bottom: 2rem; font-size: 1rem; line-height: 1.6;">Selamat datang! Mulai perjalanan mengajar Anda dengan membuat kelas pertama. Tambahkan materi dan tugas dengan mudah.</p>
                <button onclick="document.getElementById('addClassModal').style.display='block'" class="btn btn-secondary">
                    <svg width='20' height='20' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' style='display:inline-block; vertical-align:middle; line-height:1;'><path d='M4.5 16.5c-1.5 1.26-2 5-2 5s3.74-.5 5-2c.71-.84.7-2.13-.09-2.91a2.18 2.18 0 0 0-2.91-.09z'/><path d='m12 15-3-3a22 22 0 0 1 2-3.95A12.88 12.88 0 0 1 22 2c0 2.72-.78 7.5-6 11a22.35 22.35 0 0 1-4 2z'/><path d='M9 12H4s.55-3.03 2-4c1.62-1.08 5 0 5 0'/><path d='M12 15v5s3.03-.55 4-2c1.08-1.62 0-5 0-5'/></svg> Buat Kelas Pertama
                </button>
            </div>
        <?php
else: ?>
            
            <div id="searchResultsArea">
            <?php
    // Display logic loops through grades 10, 11, 12, and 'Others'
    $display_grades = ['10' => 'Kelas 10', '11' => 'Kelas 11', '12' => 'Kelas 12', 'Others' => 'Kelas Lainnya'];
?>

            <?php foreach ($display_grades as $key => $label): ?>
                <?php if (!empty($grouped_classes[$key])): ?>
                    <div class="grade-section" data-grade-section>
                        <div class="grade-title">
                            <span class="grade-badge"><?php echo $key === 'Others' ? 'Lainnya' : $key; ?></span>
                            <?php echo $label; ?>
                        </div>
                        <div class="class-grid">
                            <?php foreach ($grouped_classes[$key] as $class):
                $bgStyle = getSubjectStyle($class['subject']);
?>
                                <a href="view_class.php?id=<?php echo $class['id']; ?>" class="class-card" 
                                   data-name="<?php echo strtolower(htmlspecialchars($class['name'])); ?>"
                                   data-subject="<?php echo strtolower(htmlspecialchars($class['subject'])); ?>">
                                    <div class="class-header" style="background: <?php echo $bgStyle; ?>;">
                                        <div class="subject-badge"><?php echo htmlspecialchars($class['subject']); ?></div>
                                        <h3><?php echo htmlspecialchars($class['name']); ?> <?php if($class['is_special_class']) echo '<span style="font-size: 0.8rem; background: #fbbf24; color: #78350f; padding: 2px 6px; border-radius: 4px; vertical-align: middle; margin-left: 5px;">Kelas Khusus</span>'; ?></h3>
                                        <p><?php echo $class['is_special_class'] ? 'Lintas Kelas (Siswa ditambahkan manual)' : htmlspecialchars($class['school_class_name']); ?></p>
                                    </div>
                                    <div class="class-body">
                                        <div class="class-info-row">
                                            <div class="class-info-icon"><svg width='20' height='20' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' style='display:inline-block; vertical-align:middle; line-height:1;'><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></div>
                                            <span style="font-weight: 600; color: #334155;"><?php echo $class['is_special_class'] ? $class['special_student_count'] : $class['regular_student_count']; ?></span> 
                                            <span style="color: #94a3b8;">Siswa Terdaftar</span>
                                        </div>
                                        <div class="class-info-row">
                                            <div class="class-info-icon"><svg width='20' height='20' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' style='display:inline-block; vertical-align:middle; line-height:1;'><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg></div>
                                            <span style="font-size: 0.85rem;">Dibuat <?php echo date('d M Y', strtotime($class['created_at'])); ?></span>
                                        </div>
                                    </div>
                                </a>
                            <?php
            endforeach; ?>
                        </div>
                    </div>
                <?php
        endif; ?>
            <?php
    endforeach; ?>
            </div>

            <!-- No Results Message -->
            <div id="noResultsMsg" style="display: none; text-align: center; padding: 4rem 1rem; color: #64748b;">
                <p style="font-size: 3rem; margin-bottom: 10px;"><svg width='20' height='20' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' style='display:inline-block; vertical-align:middle; line-height:1;'><circle cx='11' cy='11' r='8'/><line x1='21' y1='21' x2='16.65' y2='16.65'/></svg></p>
                <p style="font-size: 1.1rem; font-weight: 500;">Tidak ditemukan kelas yang cocok.</p> 
            </div>

        <?php
endif; ?>
            </div> <!-- End Dashboard Container -->
        </div><!-- end content-overlap -->

        <!-- Add Class Modal -->
        <div id="addClassModal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="document.getElementById('addClassModal').style.display='none'">&times;</span>
                <h2 style="margin-bottom: 0.5rem; font-size: 1.5rem; font-weight: 800; color: #1e293b;">Buat Kelas Baru</h2>
                <p style="color: #64748b; margin-bottom: 2rem;">Isi formulir di bawah ini untuk menambahkan kelas.</p>
                
                <form method="POST">
                    <input type="hidden" name="create_class" value="1">
                    
                    <div class="form-group">
                        <label>Mata Pelajaran</label>
                        <select name="subject_id" required id="guruSubjectSelect" onchange="autoFillClassName()">
                            <option value="">-- Pilih Mata Pelajaran --</option>
                            <?php foreach ($all_subjects as $s): ?>
                                <option value="<?php echo $s['id']; ?>" data-name="<?php echo htmlspecialchars($s['name']); ?>"><?php echo htmlspecialchars($s['name']); ?></option>
                            <?php
endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group" style="background: #f8fafc; padding: 15px; border-radius: 10px; border: 1px solid #e2e8f0; margin-bottom: 20px;">
                        <label style="font-weight: 700; color: #4338ca; margin-bottom: 8px; display: block;">Target Jenjang / Tipe Kelas</label>
                        <select name="special_grade_type" id="specialGradeType" onchange="toggleSpecialClass()" style="width: 100%; border-color: #cbd5e1; padding: 10px;">
                            <option value="regular">Kelas Reguler (Satu Kelas Fisik Sekolah)</option>
                            <option value="10">Kelas Khusus - Jenjang X (Lintas Kelas X)</option>
                            <option value="11">Kelas Khusus - Jenjang XI (Lintas Kelas XI)</option>
                            <option value="12">Kelas Khusus - Jenjang XII (Lintas Kelas XII)</option>
                            <option value="lintas">Kelas Khusus - Lintas Semua Jenjang</option>
                        </select>
                        <p style="margin: 8px 0 0 0; font-size: 0.85rem; color: #64748b; line-height: 1.4;">Pilih "Kelas Reguler" untuk mengajar satu kelas fisik (contoh: X-1). Pilih "Kelas Khusus" untuk kelas gabungan dari berbagai kelas fisik (contoh: Agama Kristen Kelas X).</p>
                    </div>

                    <div class="form-group" id="regularClassGroup">
                        <label>Target Kelas Sekolah <span style="color: #ef4444;">*</span></label>
                        <select name="class_id" id="guruClassSelect" onchange="autoFillClassName()">
                            <option value="">-- Pilih Kelas --</option>
                            <?php foreach ($all_classes as $c): ?>
                                <option value="<?php echo $c['id']; ?>" data-name="<?php echo htmlspecialchars($c['name']); ?>"><?php echo htmlspecialchars($c['name']) . " (Kelas " . $c['grade_level'] . ")"; ?></option>
                            <?php
endforeach; ?>
                        </select>
                        <small style="color: #94a3b8; display: block; margin-top: 5px;">Kelas fisik/resmi yang terdaftar di sekolah.</small>
                    </div>

                    <div class="form-group">
                        <label>Nama Kelas (Custom)</label>
                        <input type="text" name="class_name_custom" id="guruCustomName" placeholder="Otomatis terisi, atau ketik manual" required>
                        <small style="color: #94a3b8; display: block; margin-top: 5px;">Nama ini akan muncul di dashboard Anda.</small>
                    </div>

                    <div style="display: flex; gap: 12px; margin-top: 2.5rem;">
                        <button type="button" class="btn btn-secondary" onclick="document.getElementById('addClassModal').style.display='none'" style="flex: 1; justify-content: center; background: #f1f5f9; color: #475569; border: none;">Batal</button>
                        <button type="submit" class="btn" style="flex: 2; justify-content: center; box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);">&#10024; Buat Kelas</button>
                    </div>
                </form>
            </div>
        </div>

    </main>
</div>

<script>
function toggleSpecialClass() {
    const specialType = document.getElementById('specialGradeType').value;
    const isSpecial = specialType !== 'regular';
    const regularGroup = document.getElementById('regularClassGroup');
    const classSelect = document.getElementById('guruClassSelect');
    const customName = document.getElementById('guruCustomName');
    
    if (isSpecial) {
        regularGroup.style.display = 'none';
        classSelect.required = false;
        classSelect.value = ''; // Reset selection
        
        // Auto-fill fallback based on subject alone
        const subjectSelect = document.getElementById('guruSubjectSelect');
        const subjectOpt = subjectSelect.options[subjectSelect.selectedIndex];
        const subjectName = subjectOpt && subjectOpt.dataset.name ? subjectOpt.dataset.name : '';
        
        let gradeLabel = '';
        if (specialType === '10') gradeLabel = 'Kelas X';
        else if (specialType === '11') gradeLabel = 'Kelas XI';
        else if (specialType === '12') gradeLabel = 'Kelas XII';
        else if (specialType === 'lintas') gradeLabel = 'Lintas Kelas';

        if (subjectName) {
            customName.value = subjectName + ' - ' + gradeLabel;
        }
    } else {
        regularGroup.style.display = 'block';
        classSelect.required = true;
        autoFillClassName(); // Re-trigger normal autofill
    }
}

function autoFillClassName() {
    if (document.getElementById('specialGradeType').value !== 'regular') return;

    const subjectSelect = document.getElementById('guruSubjectSelect');
    const classSelect = document.getElementById('guruClassSelect');
    const customName = document.getElementById('guruCustomName');

    const subjectOpt = subjectSelect.options[subjectSelect.selectedIndex];
    const classOpt = classSelect.options[classSelect.selectedIndex];

    const subjectName = subjectOpt && subjectOpt.dataset.name ? subjectOpt.dataset.name : '';
    const className = classOpt && classOpt.dataset.name ? classOpt.dataset.name : '';

    if (subjectName && className) {
        customName.value = subjectName + ' - ' + className;
    }
}

// Search Feature Script
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('classSearchInput');
    if (!searchInput) return;

    searchInput.addEventListener('input', function() {
        const query = this.value.toLowerCase().trim();
        const gradeSections = document.querySelectorAll('.grade-section');
        let hasGlobalResults = false;

        gradeSections.forEach(section => {
            const cards = section.querySelectorAll('.class-card');
            let hasSectionResults = false;

            cards.forEach(card => {
                const name = card.dataset.name || '';
                const subject = card.dataset.subject || '';
                
                if (name.includes(query) || subject.includes(query)) {
                    card.style.display = 'flex'; // Restore display
                    hasSectionResults = true;
                    hasGlobalResults = true;
                } else {
                    card.style.display = 'none';
                }
            });

            // Toggle Section Visibility
            if (hasSectionResults) {
                section.style.display = 'block';
            } else {
                section.style.display = 'none';
            }
        });

        // Show/Hide "No Results" Message
        const noResultsMsg = document.getElementById('noResultsMsg');
        if (hasGlobalResults) {
            noResultsMsg.style.display = 'none';
        } else {
            noResultsMsg.style.display = 'block';
        }
    });

    // Input focus styling
    searchInput.addEventListener('focus', function() {
        this.style.borderColor = '#6366f1';
        this.style.boxShadow = '0 0 0 3px rgba(99, 102, 241, 0.1)';
    });
    searchInput.addEventListener('blur', function() {
        this.style.borderColor = '#e2e8f0';
        this.style.boxShadow = '0 1px 2px rgba(0,0,0,0.05)';
    });
});
</script>

</body>
</html>


