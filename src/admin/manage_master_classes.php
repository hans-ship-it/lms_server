<?php
// src/admin/manage_master_classes.php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

$success = "";
$error = "";

// Handle Create Class
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_master_class'])) {
    $grade_level = intval($_POST['grade_level']);
    $class_name = trim($_POST['class_name']);
    $major = trim($_POST['major']);

    if (empty($grade_level) || empty($class_name)) {
        $error = "Tingkat Kelas dan Nama Kelas harus diisi.";
    }
    else {
        try {
            // Check for duplicate
            $check = $pdo->prepare("SELECT COUNT(*) FROM classes WHERE name = ? AND grade_level = ?");
            $check->execute([$class_name, $grade_level]);
            if ($check->fetchColumn() > 0) {
                $_SESSION['flash'] = ['type' => 'error', 'message' => "Kelas ini sudah ada."];
            }
            else {
                $stmt = $pdo->prepare("INSERT INTO classes (name, grade_level, major) VALUES (?, ?, ?)");
                $stmt->execute([$class_name, $grade_level, empty($major) ? null : $major]);
                $_SESSION['flash'] = ['type' => 'success', 'message' => "Master Kelas berhasil ditambahkan!"];
            }
        }
        catch (PDOException $e) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => "Gagal menambahkan kelas: " . $e->getMessage()];
        }
    }
    header("Location: manage_master_classes.php");
    exit;
}

// Handle Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_master_class'])) {
    $class_id = intval($_POST['class_id']);
    try {
        // We delete the class.
        // Also it might be best to clean up teacher_classes? The schema probably doesn't cascade.
        // But for safety let's just delete from classes.
        $stmt = $pdo->prepare("DELETE FROM classes WHERE id = ?");
        $stmt->execute([$class_id]);
        $_SESSION['flash'] = ['type' => 'success', 'message' => "Master Kelas berhasil dihapus."];
    }
    catch (PDOException $e) {
        $_SESSION['flash'] = ['type' => 'error', 'message' => "Gagal menghapus: Kelas masih digunakan oleh data lain."];
    }
    header("Location: manage_master_classes.php");
    exit;
}

// Handle Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_master_class'])) {
    $class_id = intval($_POST['class_id']);
    $grade_level = intval($_POST['grade_level']);
    $class_name = trim($_POST['class_name']);
    $major = trim($_POST['major']);

    if (empty($grade_level) || empty($class_name)) {
        $error = "Tingkat Kelas dan Nama Kelas harus diisi.";
    }
    else {
        try {
            $stmt = $pdo->prepare("UPDATE classes SET name = ?, grade_level = ?, major = ? WHERE id = ?");
            $stmt->execute([$class_name, $grade_level, empty($major) ? null : $major, $class_id]);
            $_SESSION['flash'] = ['type' => 'success', 'message' => "Master Kelas berhasil diperbarui!"];
        }
        catch (PDOException $e) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => "Gagal mengedit kelas: " . $e->getMessage()];
        }
    }
    header("Location: manage_master_classes.php");
    exit;
}

// Fetch all classes
$classes = $pdo->query("SELECT * FROM classes ORDER BY grade_level ASC, LENGTH(name) ASC, name ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Master Kelas - Admin</title>
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
                <h1 style="color: white; margin-bottom: 0.5rem;">&#127979; Kelola Master Kelas</h1>
                <p style="color: rgba(255,255,255,0.8);">Buat dan kelola Master Kelas di sekolah</p>
            </div>
            <!-- Decorative circle -->
            <div style="position: absolute; right: -50px; top: -50px; width: 200px; height: 200px; background: rgba(255,255,255,0.1); border-radius: 50%; blur: 20px;"></div>
        </div>

        <div class="content-overlap">
            <!-- Action Bar -->
            <div style="margin-bottom: 20px;">
                <button onclick="document.getElementById('createClassModal').showModal()" class="btn btn-primary" style="box-shadow: var(--shadow-md);">
                    &#10133; Tambah Master Kelas
                </button>
            </div>

            <!-- Existing Classes Table -->
            <div class="card">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 1px solid #f1f5f9;">
                    <h3>&#128218; Daftar Master Kelas</h3>
                    <div style="display: flex; gap: 10px;">
                        <span class="badge" style="background: #eff6ff; color: #1d4ed8;">Total: <?php echo count($classes); ?> kelas</span>
                    </div>
                </div>

                <?php if (isset($_SESSION['flash'])):
    $flash = $_SESSION['flash'];
    $cls = ($flash['type'] == 'error') ? 'badge-danger' : 'badge-success';
    $ico = ($flash['type'] == 'error') ? '&#10060;' : '&#9989;';
    echo "<div class='badge $cls' style='display:block; padding:10px; margin-bottom:15px; text-align:center;'>$ico " . htmlspecialchars($flash['message']) . "</div>";
    unset($_SESSION['flash']);
endif; ?>

                <?php if (empty($classes)): ?>
                    <div style="text-align: center; padding: 40px; color: var(--text-muted);">
                        <div style="font-size: 3rem; margin-bottom: 10px;">&#128218;</div>
                        <p>Belum ada master kelas yang dibuat.</p>
                    </div>
                <?php
else: ?>
                    <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Jenjang Kelas</th>
                                <th>Nama Kelas</th>
                                <th>Jurusan</th>
                                <th style="text-align: right;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($classes as $c): ?>
                            <tr>
                                <td><span class="badge" style="background: #e2e8f0; color: #334155;">Kelas <?php echo htmlspecialchars($c['grade_level']); ?></span></td>
                                <td><strong><?php echo htmlspecialchars($c['name']); ?></strong></td>
                                <td>
                                    <?php if (!empty($c['major'])): ?>
                                        <span class="badge" style="background: #fdf4ff; color: #a21caf;"><?php echo htmlspecialchars($c['major']); ?></span>
                                    <?php
        else: ?>
                                        <span style="color: #94a3b8; font-size: 0.85rem;">-</span>
                                    <?php
        endif; ?>
                                </td>
                                <td style="text-align: right;">
                                    <div style="display: flex; gap: 6px; justify-content: flex-end;">
                                        <button type="button" class="btn" style="padding: 6px 12px; font-size: 0.8rem; background: #dbeafe; color: #1d4ed8; border: none; cursor: pointer;" onclick="openEditModal(<?php echo $c['id']; ?>, <?php echo $c['grade_level']; ?>, '<?php echo htmlspecialchars(addslashes($c['name'])); ?>', '<?php echo htmlspecialchars(addslashes($c['major'] ?? '')); ?>')">Edit</button>
                                        <form method="POST" onsubmit="return confirm('Yakin hapus master kelas ini? Ingat, kelas akan terhapus jika tidak digunakan oleh tabel lain.');" style="margin:0;">
                                            <input type="hidden" name="delete_master_class" value="1">
                                            <input type="hidden" name="class_id" value="<?php echo $c['id']; ?>">
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
        <h3 style="margin: 0;">&#10133; Buat Master Kelas</h3>
        <button onclick="document.getElementById('createClassModal').close()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer;">&times;</button>
    </div>
    <div style="padding: 20px;">
        <form method="POST">
            <input type="hidden" name="create_master_class" value="1">

            <div class="form-group">
                <label>Jenjang Kelas (Angka)</label>
                <select name="grade_level" required class="filter-select" style="width: 100%;">
                    <option value="">-- Pilih Jenjang --</option>
                    <option value="10">Kelas 10 (X)</option>
                    <option value="11">Kelas 11 (XI)</option>
                    <option value="12">Kelas 12 (XII)</option>
                </select>
            </div>

            <div class="form-group">
                <label>Nama Kelas (Contoh: X-1, XI IPA 1)</label>
                <input type="text" name="class_name" required placeholder="Contoh: X-1" class="filter-input" style="width: 100%;">
            </div>

            <div class="form-group">
                <label>Jurusan (Opsional)</label>
                <input type="text" name="major" placeholder="Contoh: MIPA, IPS, Bahasa" class="filter-input" style="width: 100%;">
            </div>

            <div style="margin-top: 20px; display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" onclick="document.getElementById('createClassModal').close()" class="btn" style="background: #e2e8f0; color: #475569;">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Kelas</button>
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
        <h3 style="margin: 0;"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block; vertical-align:middle; line-height:1;"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg> Edit Master Kelas</h3>
        <button onclick="document.getElementById('editClassModal').close()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer;">&times;</button>
    </div>
    <div style="padding: 20px;">
        <form method="POST">
            <input type="hidden" name="edit_master_class" value="1">
            <input type="hidden" name="class_id" id="edit_class_id">

            <div class="form-group">
                <label>Jenjang Kelas (Angka)</label>
                <select name="grade_level" id="edit_grade_level" required class="filter-select" style="width: 100%;">
                    <option value="">-- Pilih Jenjang --</option>
                    <option value="10">Kelas 10 (X)</option>
                    <option value="11">Kelas 11 (XI)</option>
                    <option value="12">Kelas 12 (XII)</option>
                </select>
            </div>

            <div class="form-group">
                <label>Nama Kelas</label>
                <input type="text" name="class_name" id="edit_class_name" required placeholder="Contoh: X-1" class="filter-input" style="width: 100%;">
            </div>

            <div class="form-group">
                <label>Jurusan (Opsional)</label>
                <input type="text" name="major" id="edit_major" placeholder="Contoh: MIPA, IPS, Bahasa" class="filter-input" style="width: 100%;">
            </div>

            <div style="margin-top: 20px; display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" onclick="document.getElementById('editClassModal').close()" class="btn" style="background: #e2e8f0; color: #475569;">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</dialog>

<script>
function openEditModal(id, grade, name, major) {
    document.getElementById('edit_class_id').value = id;
    document.getElementById('edit_grade_level').value = grade;
    document.getElementById('edit_class_name').value = name;
    document.getElementById('edit_major').value = major;
    document.getElementById('editClassModal').showModal();
}
</script>

</body>
</html>

