<?php
// src/admin/manage_classes.php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

$success = "";
$error = "";

// Handle Create Class for Teacher
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_class'])) {
    $teacher_id = intval($_POST['teacher_id']);
    $class_id = intval($_POST['class_id']);
    $subject_id = intval($_POST['subject_id']);
    $class_name_custom = trim($_POST['class_name_custom']);

    if (empty($teacher_id) || empty($class_id) || empty($subject_id) || empty($class_name_custom)) {
        $error = "Semua field harus diisi.";
    }
    else {
        try {
            // Check for duplicate
            $check = $pdo->prepare("SELECT COUNT(*) FROM teacher_classes WHERE teacher_id = ? AND class_id = ? AND subject = (SELECT name FROM subjects WHERE id = ?)");
            $check->execute([$teacher_id, $class_id, $subject_id]);
            if ($check->fetchColumn() > 0) {
                $error = "Kelas ini sudah ada untuk guru tersebut.";
            }
            else {
                // Fetch subject name
                $stmt = $pdo->prepare("SELECT name FROM subjects WHERE id = ?");
                $stmt->execute([$subject_id]);
                $subject_name = $stmt->fetchColumn();

                // Generate Folder Name: YYYY-MM-DD ClassName
                $date_prefix = date('Y-m-d');
                $safe_title = preg_replace('/[^A-Za-z0-9_\-]/', '_', $class_name_custom);
                $folder_name = $date_prefix . ' ' . $safe_title;

                // Create Directory
                $base_dir = "../../public/uploads/classes/" . $folder_name;
                if (!file_exists($base_dir)) {
                    mkdir($base_dir, 0777, true);
                    mkdir($base_dir . "/materi", 0777, true);
                    mkdir($base_dir . "/tugas", 0777, true);
                }

                // Added subject_id and folder_name to the insert
                $stmt = $pdo->prepare("INSERT INTO teacher_classes (teacher_id, class_id, name, subject, subject_id, folder_name) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$teacher_id, $class_id, $class_name_custom, $subject_name, $subject_id, $folder_name]);
                $_SESSION['flash'] = ['type' => 'success', 'message' => "Kelas berhasil dibuat untuk guru!"];
            }
        }
        catch (PDOException $e) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => "Gagal membuat kelas: " . $e->getMessage()];
        }
    }
    header("Location: manage_classes.php");
    exit;
}

// Handle Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_class'])) {
    $tc_id = intval($_POST['tc_id']);
    try {
        $stmt = $pdo->prepare("DELETE FROM teacher_classes WHERE id = ?");
        $stmt->execute([$tc_id]);
        $_SESSION['flash'] = ['type' => 'success', 'message' => "Kelas berhasil dihapus."];
    }
    catch (PDOException $e) {
        $_SESSION['flash'] = ['type' => 'error', 'message' => "Gagal menghapus: " . $e->getMessage()];
    }
    header("Location: manage_classes.php");
    exit;
}

// Handle Edit Class
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_class'])) {
    $tc_id = intval($_POST['tc_id']);
    $teacher_id = intval($_POST['teacher_id']);
    $class_id_val = intval($_POST['class_id']);
    $subject_id = intval($_POST['subject_id']);
    $class_name_custom = trim($_POST['class_name_custom']);

    if (empty($teacher_id) || empty($class_id_val) || empty($subject_id) || empty($class_name_custom)) {
        $error = "Semua field harus diisi.";
    }
    else {
        try {
            // Fetch subject name
            $stmt = $pdo->prepare("SELECT name FROM subjects WHERE id = ?");
            $stmt->execute([$subject_id]);
            $subject_name = $stmt->fetchColumn();

            $stmt = $pdo->prepare("UPDATE teacher_classes SET teacher_id = ?, class_id = ?, name = ?, subject = ? WHERE id = ?");
            $stmt->execute([$teacher_id, $class_id_val, $class_name_custom, $subject_name, $tc_id]);
            $_SESSION['flash'] = ['type' => 'success', 'message' => "Kelas berhasil diperbarui!"];
        }
        catch (PDOException $e) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => "Gagal mengedit kelas: " . $e->getMessage()];
        }
    }
    header("Location: manage_classes.php");
    exit;
}

// Fetch all teachers
$teachers = $pdo->query("SELECT id, full_name FROM users WHERE role='guru' ORDER BY full_name ASC")->fetchAll();

// Fetch all classes
$classes = $pdo->query("SELECT id, name, grade_level FROM classes ORDER BY grade_level, LENGTH(name), name")->fetchAll();

// Fetch all subjects
$subjects = $pdo->query("SELECT id, name FROM subjects ORDER BY name ASC")->fetchAll();

// Fetch all teacher_classes with joins
$all_tc = $pdo->query("
    SELECT tc.id, tc.teacher_id, tc.class_id, tc.name as custom_name, tc.subject, tc.created_at, tc.is_special_class, tc.special_grade_level,
           u.full_name as teacher_name,
           c.name as class_name, c.grade_level
    FROM teacher_classes tc
    JOIN users u ON tc.teacher_id = u.id
    LEFT JOIN classes c ON tc.class_id = c.id
    ORDER BY u.full_name ASC, COALESCE(c.grade_level, tc.special_grade_level) ASC, LENGTH(c.name) ASC, c.name ASC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Kelas - Admin</title>
    <link rel="stylesheet" href="/public/assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body class="admin-full-layout">

    <div class="app-container">
    <?php include '../templates/sidebar.php'; ?>
    
    <main class="main-content">
        <!-- Unified Hero Header -->
        <div class="dashboard-hero">
            <div style="position: relative; z-index: 2;">
                <h1 style="color: white; margin-bottom: 0.5rem;">&#127979; Kelola Kelas Guru</h1>
                <p style="color: rgba(255,255,255,0.8);">Buat dan kelola kelas untuk setiap guru</p>
            </div>
            
            <div style="position: absolute; right: 40px; top: 50%; transform: translateY(-50%); z-index: 10;">
                 <a href="promote_class.php" class="btn" style="background: rgba(255,255,255,0.2); border: 1px solid rgba(255,255,255,0.3); backdrop-filter: blur(5px);">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block; vertical-align:middle; line-height:1;"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg> Kenaikan Kelas
                </a>
            </div>

            <!-- Decorative circle -->
            <div style="position: absolute; right: -50px; top: -50px; width: 200px; height: 200px; background: rgba(255,255,255,0.1); border-radius: 50%; blur: 20px;"></div>
        </div>

        <div class="content-overlap">
            
            <!-- Action Bar -->
            <div style="margin-bottom: 20px;">
                <button onclick="document.getElementById('createClassModal').showModal()" class="btn btn-primary" style="box-shadow: var(--shadow-md);">
                    &#10133; Tambah Kelas Baru
                </button>
            </div>

            <!-- Existing Classes Table -->
            <div class="card">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 1px solid #f1f5f9;">
                    <h3>&#128218; Daftar Kelas Guru</h3>
                    <div style="display: flex; gap: 10px;">
                        <span class="badge" style="background: #eff6ff; color: #1d4ed8;">Total: <?php echo count($all_tc); ?> kelas</span>
                        <span class="badge" style="background: #f0fdf4; color: #15803d;">Guru: <?php echo count($teachers); ?> orang</span>
                    </div>
                </div>

                <?php
if (isset($_SESSION['flash'])) {
    $flash = $_SESSION['flash'];
    $cls = ($flash['type'] == 'error') ? 'badge-danger' : 'badge-success';
    $ico = ($flash['type'] == 'error') ? '&#10060;' : '&#9989;';
    echo "<div class='badge $cls' style='display:block; padding:10px; margin-bottom:15px; text-align:center;'>$ico " . htmlspecialchars($flash['message']) . "</div>";
    unset($_SESSION['flash']);
}
?>

                <?php if (empty($all_tc)): ?>
                    <div style="text-align: center; padding: 40px; color: var(--text-muted);">
                        <div style="font-size: 3rem; margin-bottom: 10px;">&#128218;</div>
                        <p>Belum ada kelas yang dibuat.</p>
                    </div>
                <?php
else: ?>
                    <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Guru</th>
                                <th>Nama Kelas</th>
                                <th>Kelas</th>
                                <th>Mata Pelajaran</th>
                                <th>Tanggal</th>
                                <th style="text-align: right;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($all_tc as $tc): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($tc['teacher_name']); ?></strong></td>
                                <td>
                                    <?php echo htmlspecialchars($tc['custom_name']); ?>
                                    <?php if(isset($tc['is_special_class']) && $tc['is_special_class']) echo '<span style="font-size: 0.75rem; background: #fef3c7; color: #92400e; padding: 2px 6px; border-radius: 4px; vertical-align: middle; margin-left: 5px; border: 1px solid #fde68a;">Kelas Khusus</span>'; ?>
                                </td>
                                <td>
                                    <?php if (isset($tc['is_special_class']) && $tc['is_special_class']): ?>
                                        <span class="badge" style="background: #fdf4ff; color: #86198f; border: 1px solid #fae8ff;">Lintas Kelas <?php echo htmlspecialchars($tc['special_grade_level'] ?? ''); ?></span>
                                    <?php else: ?>
                                        <span class="badge" style="background: #dbeafe; color: #1e40af;"><?php echo htmlspecialchars($tc['class_name'] ?? ''); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td><span class="badge" style="background: #ede9fe; color: #5b21b6;"><?php echo htmlspecialchars($tc['subject']); ?></span></td>
                                <td style="color: var(--text-muted); font-size: 0.85rem;"><?php echo date('d M Y', strtotime($tc['created_at'])); ?></td>
                                <td style="text-align: right;">
                                    <div style="display: flex; gap: 6px; justify-content: flex-end;">
                                        <?php if (!isset($tc['is_special_class']) || !$tc['is_special_class']): ?>
                                        <button type="button" class="btn" style="padding: 6px 12px; font-size: 0.8rem; background: #dbeafe; color: #1d4ed8; border: none; cursor: pointer;" onclick="openEditModal(<?php echo $tc['id']; ?>, <?php echo $tc['teacher_id'] ?? 0; ?>, <?php echo $tc['class_id'] ?? 0; ?>, '<?php echo htmlspecialchars($tc['subject'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($tc['custom_name'], ENT_QUOTES); ?>')">Edit</button>
                                        <?php endif; ?>
                                        <form method="POST" onsubmit="return confirm('Yakin hapus kelas ini?');" style="margin:0;">
                                            <input type="hidden" name="delete_class" value="1">
                                            <input type="hidden" name="tc_id" value="<?php echo $tc['id']; ?>">
                                            <button type="submit" class="btn btn-danger" style="padding: 6px 12px; font-size: 0.8rem;">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <?php
    endforeach; ?>
                        </tbody>
                    </table>
                    </div>
                <?php
endif; ?>
            </div>

        </div>
    </main>
</div>

<!-- Modal for Create Class -->
<dialog id="createClassModal" style="border: none; border-radius: 16px; padding: 0; box-shadow: 0 25px 50px rgba(0,0,0,0.25); width: 100%; max-width: 500px; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); margin: 0;">
    <style>
        #createClassModal::backdrop {
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
        }
    </style>
    <div style="padding: 20px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
        <h3 style="margin: 0;">&#10133; Buat Kelas Baru</h3>
        <button onclick="document.getElementById('createClassModal').close()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer;">&times;</button>
    </div>
    <div style="padding: 20px;">
        <form method="POST">
            <input type="hidden" name="create_class" value="1">

            <div class="form-group">
                <label>Guru Tujuan</label>
                <select name="teacher_id" required class="filter-select" style="width: 100%;">
                    <option value="">-- Pilih Guru --</option>
                    <?php foreach ($teachers as $t): ?>
                        <option value="<?php echo $t['id']; ?>"><?php echo htmlspecialchars($t['full_name']); ?></option>
                    <?php
endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Mata Pelajaran</label>
                <select name="subject_id" required id="subjectSelect" onchange="autoFillName()" class="filter-select" style="width: 100%;">
                    <option value="">-- Pilih Mata Pelajaran --</option>
                    <?php foreach ($subjects as $s): ?>
                        <option value="<?php echo $s['id']; ?>" data-name="<?php echo htmlspecialchars($s['name']); ?>"><?php echo htmlspecialchars($s['name']); ?></option>
                    <?php
endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Kelas</label>
                <select name="class_id" required id="classSelect" onchange="autoFillName()" class="filter-select" style="width: 100%;">
                    <option value="">-- Pilih Kelas --</option>
                    <?php foreach ($classes as $c): ?>
                        <option value="<?php echo $c['id']; ?>" data-name="<?php echo htmlspecialchars($c['name']); ?>">Kelas <?php echo htmlspecialchars($c['name']); ?></option>
                    <?php
endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Nama Kelas (Custom)</label>
                <input type="text" name="class_name_custom" id="customName" required placeholder="Auto-generated atau ketik manual" class="filter-input" style="width: 100%;">
            </div>

            <div style="margin-top: 20px; display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" onclick="document.getElementById('createClassModal').close()" class="btn" style="background: #e2e8f0; color: #475569;">Batal</button>
                <button type="submit" class="btn btn-primary">Buat Kelas</button>
            </div>
        </form>
    </div>
</dialog>

<!-- Modal for Edit Class -->
<dialog id="editClassModal" style="border: none; border-radius: 16px; padding: 0; box-shadow: 0 25px 50px rgba(0,0,0,0.25); width: 100%; max-width: 500px; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); margin: 0;">
    <style>
        #editClassModal::backdrop {
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
        }
    </style>
    <div style="padding: 20px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
        <h3 style="margin: 0;"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block; vertical-align:middle; line-height:1;"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg> Edit Kelas</h3>
        <button onclick="document.getElementById('editClassModal').close()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer;">&times;</button>
    </div>
    <div style="padding: 20px;">
        <form method="POST">
            <input type="hidden" name="edit_class" value="1">
            <input type="hidden" name="tc_id" id="edit_tc_id">

            <div class="form-group">
                <label>Guru Tujuan</label>
                <select name="teacher_id" required id="edit_teacher_id" class="filter-select" style="width: 100%;">
                    <option value="">-- Pilih Guru --</option>
                    <?php foreach ($teachers as $t): ?>
                        <option value="<?php echo $t['id']; ?>"><?php echo htmlspecialchars($t['full_name']); ?></option>
                    <?php
endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Mata Pelajaran</label>
                <select name="subject_id" required id="edit_subject_id" onchange="editAutoFillName()" class="filter-select" style="width: 100%;">
                    <option value="">-- Pilih Mata Pelajaran --</option>
                    <?php foreach ($subjects as $s): ?>
                        <option value="<?php echo $s['id']; ?>" data-name="<?php echo htmlspecialchars($s['name']); ?>"><?php echo htmlspecialchars($s['name']); ?></option>
                    <?php
endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Kelas</label>
                <select name="class_id" required id="edit_class_id" onchange="editAutoFillName()" class="filter-select" style="width: 100%;">
                    <option value="">-- Pilih Kelas --</option>
                    <?php foreach ($classes as $c): ?>
                        <option value="<?php echo $c['id']; ?>" data-name="<?php echo htmlspecialchars($c['name']); ?>">Kelas <?php echo htmlspecialchars($c['name']); ?></option>
                    <?php
endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Nama Kelas (Custom)</label>
                <input type="text" name="class_name_custom" id="edit_customName" required placeholder="Nama kelas" class="filter-input" style="width: 100%;">
            </div>

            <div style="margin-top: 20px; display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" onclick="document.getElementById('editClassModal').close()" class="btn" style="background: #e2e8f0; color: #475569;">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</dialog>


<script>
function autoFillName() {
    const subjectSelect = document.getElementById('subjectSelect');
    const classSelect = document.getElementById('classSelect');
    const customName = document.getElementById('customName');

    const subjectOption = subjectSelect.options[subjectSelect.selectedIndex];
    const classOption = classSelect.options[classSelect.selectedIndex];

    const subjectName = subjectOption && subjectOption.dataset.name ? subjectOption.dataset.name : '';
    const className = classOption && classOption.dataset.name ? classOption.dataset.name : '';

    if (subjectName && className) {
        customName.value = subjectName + ' - ' + className;
    }
}

function editAutoFillName() {
    const subjectSelect = document.getElementById('edit_subject_id');
    const classSelect = document.getElementById('edit_class_id');
    const customName = document.getElementById('edit_customName');

    const subjectOption = subjectSelect.options[subjectSelect.selectedIndex];
    const classOption = classSelect.options[classSelect.selectedIndex];

    const subjectName = subjectOption && subjectOption.dataset.name ? subjectOption.dataset.name : '';
    const className = classOption && classOption.dataset.name ? classOption.dataset.name : '';

    if (subjectName && className) {
        customName.value = subjectName + ' - ' + className;
    }
}

function openEditModal(tcId, teacherId, classId, subjectName, customName) {
    document.getElementById('edit_tc_id').value = tcId;
    document.getElementById('edit_teacher_id').value = teacherId;
    document.getElementById('edit_class_id').value = classId;
    document.getElementById('edit_customName').value = customName;

    // Find and select the subject by name
    const subjectSelect = document.getElementById('edit_subject_id');
    for (let i = 0; i < subjectSelect.options.length; i++) {
        if (subjectSelect.options[i].dataset.name === subjectName) {
            subjectSelect.selectedIndex = i;
            break;
        }
    }

    document.getElementById('editClassModal').showModal();
}
</script>

</body>
</html>

