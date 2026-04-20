<?php
// src/admin/manage_master_classes.php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

// Handle Create
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_master_class'])) {
    $grade_level = intval($_POST['grade_level']);
    $class_name  = trim($_POST['class_name']);
    $major       = trim($_POST['major']);
    if (empty($grade_level) || empty($class_name)) {
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'Tingkat Kelas dan Nama Kelas harus diisi.'];
    } else {
        try {
            $check = $pdo->prepare("SELECT COUNT(*) FROM classes WHERE name = ? AND grade_level = ?");
            $check->execute([$class_name, $grade_level]);
            if ($check->fetchColumn() > 0) {
                $_SESSION['flash'] = ['type' => 'error', 'message' => 'Kelas ini sudah ada.'];
            } else {
                $stmt = $pdo->prepare("INSERT INTO classes (name, grade_level, major) VALUES (?, ?, ?)");
                $stmt->execute([$class_name, $grade_level, empty($major) ? null : $major]);
                $_SESSION['flash'] = ['type' => 'success', 'message' => 'Master Kelas berhasil ditambahkan!'];
            }
        } catch (PDOException $e) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Gagal: ' . $e->getMessage()];
        }
    }
    header("Location: manage_master_classes.php"); exit;
}

// Handle Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_master_class'])) {
    $class_id = intval($_POST['class_id']);
    try {
        $stmt = $pdo->prepare("DELETE FROM classes WHERE id = ?");
        $stmt->execute([$class_id]);
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Master Kelas berhasil dihapus.'];
    } catch (PDOException $e) {
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'Gagal: Kelas masih digunakan oleh data lain.'];
    }
    header("Location: manage_master_classes.php"); exit;
}

// Handle Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_master_class'])) {
    $class_id    = intval($_POST['class_id']);
    $grade_level = intval($_POST['grade_level']);
    $class_name  = trim($_POST['class_name']);
    $major       = trim($_POST['major']);
    if (empty($grade_level) || empty($class_name)) {
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'Tingkat Kelas dan Nama Kelas harus diisi.'];
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE classes SET name=?, grade_level=?, major=? WHERE id=?");
            $stmt->execute([$class_name, $grade_level, empty($major) ? null : $major, $class_id]);
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Master Kelas berhasil diperbarui!'];
        } catch (PDOException $e) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Gagal: ' . $e->getMessage()];
        }
    }
    header("Location: manage_master_classes.php"); exit;
}

$classes = $pdo->query("SELECT * FROM classes ORDER BY grade_level ASC, LENGTH(name) ASC, name ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Master Kelas - Admin</title>
    <link rel="stylesheet" href="/public/assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .main-content { background: #f5f7fb !important; padding: 0 !important; }
        .page-hero {
            background: linear-gradient(135deg, #1e1b4b 0%, #312e81 50%, #4338ca 100%);
            padding: 2.5rem 3rem 5rem;
            position: relative; overflow: hidden;
        }
        .page-hero::before {
            content: ''; position: absolute; right: -60px; top: -60px;
            width: 250px; height: 250px; background: rgba(255,255,255,0.07); border-radius: 50%;
        }
        .page-hero h1 { color: #fff; font-size: 1.6rem; font-weight: 700; margin: 0 0 0.4rem; }
        .page-hero p  { color: rgba(255,255,255,0.8); margin: 0; font-size: 0.95rem; }
        .page-content { position: relative; margin-top: -2.5rem; padding: 0 3rem 3rem; z-index: 10; }
        .action-bar { display: flex; justify-content: flex-end; margin-bottom: 1.2rem; }
        .btn-add {
            padding: 9px 20px; background: #4338ca; color: #fff;
            border: none; border-radius: 9px; font-weight: 700;
            font-size: 0.88rem; cursor: pointer; font-family: inherit;
        }
        .btn-add:hover { background: #3730a3; }
        .db-section { background: #fff; border-radius: 14px; border: 1px solid #e8edf5; overflow: hidden; }
        .section-head {
            padding: 14px 20px; border-bottom: 1px solid #f1f5f9;
            display: flex; justify-content: space-between; align-items: center;
        }
        .section-head h3 { font-size: 0.95rem; font-weight: 700; color: #0f172a; margin: 0; }
        .alert-success { background:#dcfce7;color:#166534;padding:10px 18px;border-radius:9px;margin-bottom:1.2rem;font-weight:500;border:1px solid #bbf7d0; }
        .alert-error   { background:#fee2e2;color:#991b1b;padding:10px 18px;border-radius:9px;margin-bottom:1.2rem;font-weight:500;border:1px solid #fecaca; }
        .class-row {
            display: flex; align-items: center; gap: 14px;
            padding: 13px 20px; border-bottom: 1px solid #f8fafc;
        }
        .class-row:last-child { border-bottom: none; }
        .badge-jenjang {
            background: #e2e8f0; color: #334155;
            padding: 4px 10px; border-radius: 8px; font-size: 0.78rem; font-weight: 700;
        }
        .class-name { font-weight: 700; color: #0f172a; font-size: 0.95rem; flex: 1; }
        .badge-jurusan {
            background: #fdf4ff; color: #a21caf;
            padding: 3px 10px; border-radius: 20px; font-size: 0.78rem; font-weight: 600;
        }
        .row-actions { display: flex; gap: 6px; margin-left: auto; }
        .btn-edit {
            padding: 6px 14px; background: #dbeafe; color: #1d4ed8;
            border: none; border-radius: 7px; font-size: 0.8rem; font-weight: 600;
            cursor: pointer; font-family: inherit;
        }
        .btn-edit:hover { background: #bfdbfe; }
        .btn-delete {
            padding: 6px 12px; background: #fee2e2; color: #991b1b;
            border: none; border-radius: 7px; font-size: 0.8rem; font-weight: 600;
            cursor: pointer; font-family: inherit;
        }
        .btn-delete:hover { background: #fecaca; }
        .empty-state { text-align: center; padding: 3rem 2rem; color: #94a3b8; }
        /* Modal */
        dialog { border: none; border-radius: 14px; padding: 0; box-shadow: 0 25px 50px rgba(0,0,0,0.25); width: 100%; max-width: 480px; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); margin: 0; }
        dialog::backdrop { background: rgba(0,0,0,0.5); backdrop-filter: blur(4px); }
        .modal-head { padding: 18px 20px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; }
        .modal-head h3 { margin: 0; font-size: 1rem; font-weight: 700; color: #0f172a; }
        .modal-body { padding: 20px; }
        .form-group { margin-bottom: 1rem; }
        .form-group label { display: block; font-size: 0.85rem; font-weight: 600; color: #374151; margin-bottom: 5px; }
        .form-group input, .form-group select { width: 100%; padding: 9px 12px; border: 1px solid #e2e8f0; border-radius: 8px; font-family: inherit; font-size: 0.9rem; box-sizing: border-box; }
        .modal-footer { display: flex; justify-content: flex-end; gap: 10px; margin-top: 1.2rem; }
        .btn-cancel { padding: 8px 16px; background: #e2e8f0; color: #475569; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; font-family: inherit; }
        .btn-submit { padding: 8px 18px; background: #4338ca; color: #fff; border: none; border-radius: 8px; font-weight: 700; cursor: pointer; font-family: inherit; }
        @media (max-width: 768px) { .page-content { padding: 0 1rem 2rem; } .page-hero { padding: 2rem 1.5rem 4.5rem; } }
    </style>
</head>
<body>

<div class="app-container">
    <?php include '../templates/sidebar.php'; ?>
    
    <main class="main-content">
        <div class="page-hero">
            <h1>
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:middle;margin-right:8px;"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M9 9h6M9 13h6M9 17h4"/></svg>
                Kelola Master Kelas
            </h1>
            <p>Buat dan kelola daftar kelas di sekolah.</p>
        </div>

        <div class="page-content">
            <?php if (isset($_SESSION['flash'])):
                $f = $_SESSION['flash']; unset($_SESSION['flash']);
                $cls = $f['type'] === 'error' ? 'alert-error' : 'alert-success';
            ?>
                <div class="<?php echo $cls; ?>"><?php echo htmlspecialchars($f['message']); ?></div>
            <?php endif; ?>

            <div class="action-bar">
                <button class="btn-add" onclick="document.getElementById('createClassModal').showModal()">
                    + Tambah Master Kelas
                </button>
            </div>

            <div class="db-section">
                <div class="section-head">
                    <h3>Daftar Master Kelas</h3>
                    <span style="font-size:0.82rem;color:#94a3b8;"><?php echo count($classes); ?> kelas</span>
                </div>
                <?php if (empty($classes)): ?>
                    <div class="empty-state">
                        <svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin-bottom:1rem;opacity:0.4;"><rect x="3" y="4" width="18" height="18" rx="2"/></svg>
                        <p style="font-weight:600; color:#64748b;">Belum ada master kelas yang dibuat.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($classes as $c): ?>
                        <div class="class-row">
                            <span class="badge-jenjang">Kelas <?php echo htmlspecialchars($c['grade_level']); ?></span>
                            <span class="class-name"><?php echo htmlspecialchars($c['name']); ?></span>
                            <?php if (!empty($c['major'])): ?>
                                <span class="badge-jurusan"><?php echo htmlspecialchars($c['major']); ?></span>
                            <?php else: ?>
                                <span style="color:#9ca3af; font-size:0.82rem;">—</span>
                            <?php endif; ?>
                            <div class="row-actions">
                                <button type="button" class="btn-edit"
                                    onclick="openEditModal(<?php echo $c['id']; ?>, <?php echo $c['grade_level']; ?>, '<?php echo htmlspecialchars(addslashes($c['name'])); ?>', '<?php echo htmlspecialchars(addslashes($c['major'] ?? '')); ?>')">
                                    Edit
                                </button>
                                <form method="POST" style="margin:0;" onsubmit="return confirm('Hapus kelas ini?');">
                                    <input type="hidden" name="delete_master_class" value="1">
                                    <input type="hidden" name="class_id" value="<?php echo $c['id']; ?>">
                                    <button type="submit" class="btn-delete">Hapus</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

<!-- Modal Tambah Kelas -->
<dialog id="createClassModal">
    <div class="modal-head">
        <h3>+ Buat Master Kelas</h3>
        <button onclick="document.getElementById('createClassModal').close()" style="background:none;border:none;font-size:1.5rem;cursor:pointer;">&times;</button>
    </div>
    <div class="modal-body">
        <form method="POST">
            <input type="hidden" name="create_master_class" value="1">
            <div class="form-group">
                <label>Jenjang Kelas</label>
                <select name="grade_level" required>
                    <option value="">-- Pilih Jenjang --</option>
                    <option value="10">Kelas 10 (X)</option>
                    <option value="11">Kelas 11 (XI)</option>
                    <option value="12">Kelas 12 (XII)</option>
                </select>
            </div>
            <div class="form-group">
                <label>Nama Kelas</label>
                <input type="text" name="class_name" required placeholder="Contoh: X-1">
            </div>
            <div class="form-group">
                <label>Jurusan (Opsional)</label>
                <input type="text" name="major" placeholder="Contoh: MIPA, IPS, Bahasa">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="document.getElementById('createClassModal').close()">Batal</button>
                <button type="submit" class="btn-submit">Simpan Kelas</button>
            </div>
        </form>
    </div>
</dialog>

<!-- Modal Edit Kelas -->
<dialog id="editClassModal">
    <div class="modal-head">
        <h3>Edit Master Kelas</h3>
        <button onclick="document.getElementById('editClassModal').close()" style="background:none;border:none;font-size:1.5rem;cursor:pointer;">&times;</button>
    </div>
    <div class="modal-body">
        <form method="POST">
            <input type="hidden" name="edit_master_class" value="1">
            <input type="hidden" name="class_id" id="edit_class_id">
            <div class="form-group">
                <label>Jenjang Kelas</label>
                <select name="grade_level" id="edit_grade_level" required>
                    <option value="">-- Pilih Jenjang --</option>
                    <option value="10">Kelas 10 (X)</option>
                    <option value="11">Kelas 11 (XI)</option>
                    <option value="12">Kelas 12 (XII)</option>
                </select>
            </div>
            <div class="form-group">
                <label>Nama Kelas</label>
                <input type="text" name="class_name" id="edit_class_name" required placeholder="Contoh: X-1">
            </div>
            <div class="form-group">
                <label>Jurusan (Opsional)</label>
                <input type="text" name="major" id="edit_major" placeholder="Contoh: MIPA, IPS, Bahasa">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="document.getElementById('editClassModal').close()">Batal</button>
                <button type="submit" class="btn-submit">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</dialog>

<script>
function openEditModal(id, grade, name, major) {
    document.getElementById('edit_class_id').value    = id;
    document.getElementById('edit_grade_level').value = grade;
    document.getElementById('edit_class_name').value  = name;
    document.getElementById('edit_major').value       = major;
    document.getElementById('editClassModal').showModal();
}
</script>
</body>
</html>
