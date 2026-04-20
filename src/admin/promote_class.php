<?php
// src/admin/promote_class.php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

$success = "";
$error = "";

// Handle Promotion Process
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['promote_students'])) {
    $from_class_id = $_POST['from_class_id'];
    $to_class_id = $_POST['to_class_id'];
    $selected_students = $_POST['students'] ?? [];

    if (empty($from_class_id) || empty($to_class_id) || empty($selected_students)) {
        $error = "Pilih kelas asal, kelas tujuan, dan minimal satu siswa.";
    }
    else {
        try {
            $pdo->beginTransaction();

            if ($to_class_id === 'graduate') {
                // Graduate Students
                $sql = "UPDATE users SET status = 'graduated', class_id = NULL WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                
                // Auto-create initial Tracer Study record
                $tracer_sql = "INSERT INTO tracer_study (user_id, kegiatan, nama_instansi, jurusan_posisi, tahun_lulus) VALUES (?, 'Belum/Tidak Bekerja', '-', '-', ?) ON DUPLICATE KEY UPDATE tahun_lulus = VALUES(tahun_lulus)";
                $tracer_stmt = $pdo->prepare($tracer_sql);
                $current_year = date('Y');

                foreach ($selected_students as $student_id) {
                    $stmt->execute([$student_id]);
                    $tracer_stmt->execute([$student_id, $current_year]);
                }
                $success = count($selected_students) . " Siswa berhasil diluluskan dan data awal tracer study telah dibuat!";
            }
            else {
                // Promote to Next Class
                $sql = "UPDATE users SET class_id = ? WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                foreach ($selected_students as $student_id) {
                    $stmt->execute([$to_class_id, $student_id]);
                }
                $success = count($selected_students) . " Siswa berhasil dinaikkan kelas!";
            }

            $pdo->commit();
        }
        catch (Exception $e) {
            $pdo->rollBack();
            $error = "Terjadi kesalahan: " . $e->getMessage();
        }
    }
}

// Fetch Validation
$valid_classes = $pdo->query("SELECT id, name FROM classes ORDER BY grade_level, LENGTH(name), name")->fetchAll();

// Get Students if class selected
$students = [];
$selected_from = $_GET['from_class'] ?? '';
if ($selected_from) {
    $stmt = $pdo->prepare("SELECT id, full_name, nis FROM users WHERE role='siswa' AND class_id = ? AND status='active' ORDER BY full_name");
    $stmt->execute([$selected_from]);
    $students = $stmt->fetchAll();
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kenaikan Kelas - Admin</title>
    <link rel="stylesheet" href="/public/assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .main-content { background: #f5f7fb !important; padding: 0 !important; }
        .page-hero {
            background: linear-gradient(135deg, #1e1b4b 0%, #312e81 50%, #4338ca 100%);
            padding: 2.5rem 3rem 5rem; position: relative; overflow: hidden;
        }
        .page-hero::before { content:''; position:absolute; right:-60px; top:-60px; width:250px; height:250px; background:rgba(255,255,255,0.07); border-radius:50%; }
        .page-hero h1 { color:#fff; font-size:1.6rem; font-weight:700; margin:0 0 0.4rem; }
        .page-hero p  { color:rgba(255,255,255,0.8); margin:0; font-size:0.95rem; }
        .hero-back { position:absolute; right:3rem; top:50%; transform:translateY(-50%); z-index:10; background:rgba(255,255,255,0.18); border:1px solid rgba(255,255,255,0.3); color:#fff; padding:9px 18px; border-radius:9px; font-size:0.88rem; font-weight:600; text-decoration:none; backdrop-filter:blur(5px); }
        .page-content { position:relative; margin-top:-2.5rem; padding:0 3rem 3rem; z-index:10; }
        .db-section { background:#fff; border-radius:14px; border:1px solid #e8edf5; overflow:hidden; max-width:720px; }
        .section-head { padding:16px 22px; border-bottom:1px solid #f1f5f9; display:flex; align-items:center; gap:10px; }
        .section-head h3 { font-size:1rem; font-weight:700; color:#0f172a; margin:0; }
        .section-body { padding:24px 22px; }
        .alert-success { background:#dcfce7;color:#166534;padding:10px 16px;border-radius:8px;margin-bottom:1rem;font-weight:500;border:1px solid #bbf7d0;font-size:0.9rem; }
        .alert-danger  { background:#fee2e2;color:#991b1b;padding:10px 16px;border-radius:8px;margin-bottom:1rem;font-weight:500;border:1px solid #fecaca;font-size:0.9rem; }
        .form-group { margin-bottom:1rem; }
        .form-group label { display:block; font-size:0.85rem; font-weight:600; color:#374151; margin-bottom:5px; }
        .form-group select { width:100%; padding:9px 12px; border:1px solid #e2e8f0; border-radius:8px; font-family:inherit; font-size:0.9rem; }
        .btn-promote { width:100%; padding:11px; background:linear-gradient(135deg,#312e81,#4338ca); color:#fff; border:none; border-radius:9px; font-weight:700; font-size:0.95rem; cursor:pointer; font-family:inherit; margin-top:0.8rem; }
        .student-list-wrap { max-height:380px; overflow-y:auto; border:1px solid #e8edf5; border-radius:10px; margin-top:8px; }
        table { width:100%; border-collapse:collapse; }
        thead th { text-align:left; padding:10px 14px; border-bottom:2px solid #f1f5f9; color:#64748b; font-size:0.78rem; text-transform:uppercase; background:#f8fafc; position:sticky; top:0; }
        tbody td { padding:10px 14px; border-bottom:1px solid #f8fafc; font-size:0.88rem; }
        @media (max-width:768px) { .page-content { padding:0 1rem 2rem; } .page-hero { padding:2rem 1.5rem 4.5rem; } .hero-back { display:none; } }
    </style>
    <script>
        function toggleCheckboxes(source) {
            var checkboxes = document.getElementsByName('students[]');
            for(var i=0, n=checkboxes.length; i<n; i++) { checkboxes[i].checked = source.checked; }
        }
    </script>
</head>
<body class="admin-full-layout">

<div class="app-container">
    <?php include '../templates/sidebar.php'; ?>
    
    <main class="main-content">
        <div class="page-hero">
            <div>
                <h1>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:middle;margin-right:8px;"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
                    Kenaikan Kelas / Kelulusan
                </h1>
                <p>Pindahkan siswa ke kelas tingkat lanjut atau luluskan siswa tingkat akhir.</p>
            </div>
            <a href="manage_classes.php" class="hero-back">&larr; Kelola Kelas</a>
        </div>

        <div class="page-content">
            <div class="db-section">
                <div class="section-head">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#4338ca" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
                    <h3>Proses Kenaikan Kelas</h3>
                </div>
                <div class="section-body">
            <?php if ($success): ?><div class="alert-success"><?php echo $success; ?></div><?php endif; ?>
            <?php if ($error): ?><div class="alert-danger"><?php echo $error; ?></div><?php endif; ?>

            <!-- Step 1: Select Classes -->
            <form method="GET">
                <div class="form-group">
                    <label>Pilih Kelas Asal</label>
                    <select name="from_class" required onchange="this.form.submit()">
                        <option value="">-- Pilih Kelas --</option>
                        <?php foreach ($valid_classes as $c): ?>
                            <option value="<?php echo $c['id']; ?>" <?php echo $selected_from == $c['id'] ? 'selected' : ''; ?>>
                                Kelas <?php echo htmlspecialchars($c['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </form>

            <?php if ($selected_from && empty($students)): ?>
                <div style="text-align:center; padding: 2rem; color: #64748b;">
                    Tidak ada siswa aktif di kelas ini.
                </div>
            <?php
elseif ($selected_from): ?>
                
                <form method="POST">
                    <input type="hidden" name="from_class_id" value="<?php echo $selected_from; ?>">
                    <input type="hidden" name="promote_students" value="1">

                    <div class="form-group">
                        <label>Pilih Tujuan</label>
                        <select name="to_class_id" required style="width: 100%; padding: 10px; border: 2px solid #3b82f6;">
                            <option value="">-- Pilih Kelas Tujuan / Lulus --</option>
                            <option value="graduate"><svg width='20' height='20' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' style='display:inline-block; vertical-align:middle; line-height:1;'><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg> LULUS (Set status tamat)</option>
                            <optgroup label="Naik ke Kelas:">
                                <?php foreach ($valid_classes as $c): ?>
                                    <?php if ($c['id'] == $selected_from)
            continue; ?>
                                    <option value="<?php echo $c['id']; ?>">Kelas <?php echo htmlspecialchars($c['name']); ?></option>
                                <?php
    endforeach; ?>
                            </optgroup>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Daftar Siswa</label>
                        <div class="student-list-wrap">
                            <table>
                                <thead>
                                    <tr>
                                        <th style="width:40px;"><input type="checkbox" checked onclick="toggleCheckboxes(this)"></th>
                                        <th>NIS</th>
                                        <th>Nama Siswa</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($students as $s): ?>
                                        <tr>
                                            <td><input type="checkbox" name="students[]" value="<?php echo $s['id']; ?>" checked></td>
                                            <td style="color:#64748b;"><?php echo htmlspecialchars($s['nis'] ?? '-'); ?></td>
                                            <td><strong><?php echo htmlspecialchars($s['full_name']); ?></strong></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <p style="font-size:0.8rem; color:#94a3b8; margin-top:6px;">* Hapus centang siswa yang <strong>tidak naik kelas</strong>.</p>
                    </div>

                    <button type="submit" class="btn-promote" onclick="return confirm('Yakin ingin memproses data ini?')">Proses Kenaikan Kelas</button>
                </form>

            <?php endif; ?>

                </div>
            </div>
        </div>
    </main>
</div>

</body>
</html>

