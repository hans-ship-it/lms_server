<?php
// src/admin/manage_users.php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

$edit_mode = false;
$edit_user = null;
$search_query = $_GET['search'] ?? '';
$filter_role = $_GET['role'] ?? '';
$filter_class = $_GET['class_id'] ?? '';
$filter_gender = $_GET['gender'] ?? '';
$filter_gender = $_GET['gender'] ?? '';
$filter_subject = $_GET['subject_id'] ?? '';
$filter_nip = $_GET['nip'] ?? '';
$filter_nis = $_GET['nis'] ?? '';

// Fetch Classes & Subjects for Dropdowns
try {
    $classes = $pdo->query("SELECT * FROM classes ORDER BY grade_level, LENGTH(name), name")->fetchAll();
    $subjects = $pdo->query("SELECT * FROM subjects ORDER BY name")->fetchAll();
}
catch (PDOException $e) {
    die("Error Database: " . $e->getMessage());
}

// Handle Delete
if (isset($_GET['delete'])) {
    try {
        $id = $_GET['delete'];
        $stmtCheck = $pdo->prepare("SELECT role, username FROM users WHERE id = ?");
        $stmtCheck->execute([$id]);
        $userCheck = $stmtCheck->fetch();

        if ($userCheck && strtolower($userCheck['role']) !== 'admin' && strtolower($userCheck['username']) !== 'admin') {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$id]);
            header("Location: manage_users.php?msg=deleted");
        }
        else {
            header("Location: manage_users.php?msg=cannot_delete_admin");
        }
        exit;
    }
    catch (PDOException $e) {
        $error = "Gagal menghapus user: " . $e->getMessage();
    }
}

// Handle Delete All
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_all') {
    $role_to_delete = $_POST['role_to_delete'] ?? '';
    $class_to_delete = $_POST['class_to_delete'] ?? '';
    if (in_array($role_to_delete, ['siswa', 'guru'])) {
        try {
            if ($role_to_delete === 'siswa' && !empty($class_to_delete)) {
                // Delete siswa in a specific class
                $stmt = $pdo->prepare("DELETE FROM users WHERE role = 'siswa' AND class_id = ? AND username != 'admin'");
                $stmt->execute([$class_to_delete]);
                header("Location: manage_users.php?msg=siswa_class_deleted");
            } else {
                // Delete all of this role
                $stmt = $pdo->prepare("DELETE FROM users WHERE role = ? AND username != 'admin' AND role != 'admin'");
                $stmt->execute([$role_to_delete]);
                header("Location: manage_users.php?msg=" . $role_to_delete . "_deleted");
            }
            exit;
        }
        catch (PDOException $e) {
            $error = "Gagal menghapus massal: " . $e->getMessage();
        }
    }
}

// Handle Edit Fetch
if (isset($_GET['edit'])) {
    $edit_mode = true;
    $id = $_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $edit_user = $stmt->fetch();
    if (!$edit_user) {
        header("Location: manage_users.php");
        exit;
    }
}

// Handle Delete and Edit via separate page redirects
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
// Form handling moved to add_user.php
}

// Fetch Users with Filters
$sql = "SELECT users.*, classes.name as class_name, subjects.name as subject_name 
        FROM users 
        LEFT JOIN classes ON users.class_id = classes.id 
        LEFT JOIN subjects ON users.subject_id = subjects.id 
        WHERE 1=1 ";

$params = [];

if ($search_query) {
    $sql .= " AND (users.full_name LIKE ? OR users.username LIKE ? OR users.nip LIKE ? OR users.nis LIKE ?) ";
    $params[] = "%$search_query%";
    $params[] = "%$search_query%";
    $params[] = "%$search_query%";
    $params[] = "%$search_query%";
}

if ($filter_role) {
    $sql .= " AND users.role = ? ";
    $params[] = $filter_role;
}

if ($filter_class) {
    $sql .= " AND users.class_id = ? ";
    $params[] = $filter_class;
}

if ($filter_gender) {
    $sql .= " AND users.gender = ? ";
    $params[] = $filter_gender;
}

if ($filter_subject) {
    $sql .= " AND users.subject_id = ? ";
    $params[] = $filter_subject;
}

$sql .= " ORDER BY users.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Pengguna - Admin</title>
    <link rel="stylesheet" href="/public/assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Inline styles just for specific page tweaks, global styles in style.css -->
    <style>
        .role-badge { padding: 4px 10px; border-radius: 20px; font-weight: 700; font-size: 0.75rem; text-transform: uppercase; }
        .role-admin { background: #fee2e2; color: #991b1b; }
        .role-guru { background: #e0e7ff; color: #3730a3; }
        .role-siswa { background: #dcfce7; color: #166534; }
        .role-osis { background: #ffedd5; color: #9a3412; }
        .role-kepsek { background: #fef08a; color: #854d0e; }
        .role-wakasek { background: #fef9c3; color: #a16207; }
        .role-bk { background: #fce7f3; color: #be185d; }
    </style>
</head>
<body class="admin-full-layout">

<div class="app-container">
    <?php include '../templates/sidebar.php'; ?>
    
    <main class="main-content">
        <!-- Unified Hero Header -->
        <div class="dashboard-hero">
            <div style="position: relative; z-index: 2;">
                <h1 style="color: white; margin-bottom: 0.5rem;"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block; vertical-align:middle; line-height:1;"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg> Kelola Pengguna</h1>
                <p style="color: rgba(255,255,255,0.8);">Manajemen data seluruh pengguna sistem.</p>
            </div>
            <div style="position: absolute; right: 40px; top: 50%; transform: translateY(-50%); z-index: 10;">
                <div style="display: flex; gap: 10px;">
                    <a href="import_siswa.php" class="btn" style="background: rgba(255,255,255,0.2); border: 1px solid rgba(255,255,255,0.3); backdrop-filter: blur(5px);"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block; vertical-align:middle; line-height:1;"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg> Import Siswa</a>
                    <a href="import_guru.php" class="btn" style="background: rgba(255,255,255,0.2); border: 1px solid rgba(255,255,255,0.3); backdrop-filter: blur(5px);"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block; vertical-align:middle; line-height:1;"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg> Import Guru</a>
                    <a href="add_user.php" class="btn" style="background: rgba(255,255,255,0.2); border: 1px solid rgba(255,255,255,0.3); backdrop-filter: blur(5px);">+ Tambah Pengguna</a>
                </div>
            </div>
            <!-- Decorative circle -->
            <div style="position: absolute; right: -50px; top: -50px; width: 200px; height: 200px; background: rgba(255,255,255,0.1); border-radius: 50%;"></div>
        </div>
        
        <!-- User List -->
        <div class="content-overlap">
            <div class="card">
            <div style="margin-bottom: 1.5rem;">
            <div style="margin-bottom: 1.5rem;">
                <!-- Header moved to Hero -->

                <?php if (isset($_SESSION['flash'])): ?>
                    <div class="badge badge-success" style="display:block; padding: 1rem; margin-bottom: 1rem; text-align:center;">
                        <?php echo $_SESSION['flash'];
    unset($_SESSION['flash']); ?>
                    </div>
                <?php
endif; ?>
                
                <?php if (isset($error)): ?>
                    <div class="badge badge-danger" style="display:block; padding: 1rem; margin-bottom: 1rem; text-align:center; background: #fee2e2; color: #b91c1c;">
                        <?php echo $error; ?>
                    </div>
                <?php
endif; ?>
                <?php if (isset($_GET['msg'])): ?>
                                    <?php if ($_GET['msg'] == 'deleted'): ?>
                        <div class="badge badge-success" style="display:block; padding: 1rem; margin-bottom: 1rem; text-align:center;">User berhasil dihapus.</div>
                    <?php
    elseif ($_GET['msg'] == 'siswa_deleted'): ?>
                        <div class="badge badge-success" style="display:block; padding: 1rem; margin-bottom: 1rem; text-align:center;">Semua Siswa berhasil dihapus.</div>
                    <?php
    elseif ($_GET['msg'] == 'siswa_class_deleted'): ?>
                        <div class="badge badge-success" style="display:block; padding: 1rem; margin-bottom: 1rem; text-align:center;">Siswa pada kelas yang dipilih berhasil dihapus.</div>
                    <?php
    elseif ($_GET['msg'] == 'guru_deleted'): ?>
                        <div class="badge badge-success" style="display:block; padding: 1rem; margin-bottom: 1rem; text-align:center;">Semua Guru berhasil dihapus.</div>
                    <?php
    elseif ($_GET['msg'] == 'cannot_delete_admin'): ?>
                        <div class="badge badge-danger" style="display:block; padding: 1rem; margin-bottom: 1rem; text-align:center; background: #fee2e2; color: #b91c1c;">User Admin tidak dapat dihapus.</div>
                    <?php
    endif; ?>
                <?php
endif; ?>

                <div style="display: flex; justify-content: flex-end; align-items: center; margin-bottom: 1rem; flex-wrap: wrap; gap: 10px;">
                                    <form method="POST" id="bulkDeleteForm" onsubmit="return confirmBulkDelete()" style="display: flex; gap: 10px; align-items: center; background: #fee2e2; padding: 10px; border-radius: 8px; border: 1px solid #fca5a5; flex-wrap: wrap;">
                        <span style="font-weight: 600; color: #991b1b; font-size: 0.9rem;">Zona Bahaya:</span>
                        <input type="hidden" name="action" value="delete_all">
                        <select name="role_to_delete" id="bulkRole" class="filter-select" style="max-width: 180px; border-color: #fca5a5; margin-bottom: 0;" onchange="toggleClassFilter()">
                            <option value="">-- Pilih Role --</option>
                            <option value="siswa">Semua Siswa</option>
                            <option value="guru">Semua Guru</option>
                        </select>
                        <select name="class_to_delete" id="bulkClass" class="filter-select" style="max-width: 200px; border-color: #fca5a5; margin-bottom: 0; display:none;">
                            <option value="">-- Semua Kelas --</option>
                            <?php foreach ($classes as $c): ?>
                                <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="btn btn-danger" style="display: flex; align-items: center; gap: 5px; padding: 6px 12px;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                            Hapus Massal
                        </button>
                    </form>
                </div>

                <form method="GET" class="filter-bar">
                    <input type="text" name="search" class="filter-input" placeholder="Cari nama, NIP, NIS..." value="<?php echo htmlspecialchars($search_query); ?>" style="flex: 2; min-width: 200px;">
                    
                    <select name="role" class="filter-select">
                        <option value="">Semua Role</option>
                        <option value="guru" <?php echo $filter_role == 'guru' ? 'selected' : ''; ?>>Guru</option>
                        <option value="siswa" <?php echo $filter_role == 'siswa' ? 'selected' : ''; ?>>Siswa</option>
                        <option value="admin" <?php echo $filter_role == 'admin' ? 'selected' : ''; ?>>Admin</option>
                        <option value="osis" <?php echo $filter_role == 'osis' ? 'selected' : ''; ?>>OSIS</option>
                        <option value="kepsek" <?php echo $filter_role == 'kepsek' ? 'selected' : ''; ?>>Kepala Sekolah</option>
                        <option value="wakasek" <?php echo $filter_role == 'wakasek' ? 'selected' : ''; ?>>Wakil Kepala Sekolah</option>
                        <option value="bk" <?php echo $filter_role == 'bk' ? 'selected' : ''; ?>>Guru BK</option>
                    </select>

                    <select name="gender" class="filter-select">
                        <option value="">Gender</option>
                        <option value="L" <?php echo $filter_gender == 'L' ? 'selected' : ''; ?>>Laki-laki</option>
                        <option value="P" <?php echo $filter_gender == 'P' ? 'selected' : ''; ?>>Perempuan</option>
                    </select>

                    <select name="class_id" class="filter-select" style="max-width: 150px;">
                        <option value="">Kelas</option>
                        <?php foreach ($classes as $c): ?>
                            <option value="<?php echo $c['id']; ?>" <?php echo $filter_class == $c['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($c['name']); ?>
                            </option>
                        <?php
endforeach; ?>
                    </select>

                    <select name="subject_id" class="filter-select" style="max-width: 150px;">
                        <option value="">Mapel</option>
                        <?php foreach ($subjects as $s): ?>
                            <option value="<?php echo $s['id']; ?>" <?php echo $filter_subject == $s['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($s['name']); ?>
                            </option>
                        <?php
endforeach; ?>
                    </select>

                    <button type="submit" class="btn btn-filter">Filter</button>
                    <!-- Reset Button logic simplified -->
                    <?php if ($search_query || $filter_role || $filter_class || $filter_gender || $filter_subject): ?>
                        <a href="manage_users.php" class="btn btn-secondary btn-filter">Reset</a>
                    <?php
endif; ?>
                </form>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th style="width: 50px; text-align: center;">No.</th>
                            <th>User</th>
                            <th>Password</th>
                            <th>Info</th>
                            <th>Role & Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $row_no = 1; foreach ($users as $u): ?>
                        <tr>
                            <td style="text-align: center; color: #64748b; font-weight: 500;">
                                <?php echo $row_no++; ?>
                            </td>
                            <td>
                                <div class="user-info">
                                    <?php if ($u['photo_path']): ?>
                                        <img src="/<?php echo htmlspecialchars($u['photo_path']); ?>" class="user-avatar">
                                    <?php
    else: ?>
                                        <div class="user-avatar-placeholder"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block; vertical-align:middle; line-height:1;"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></div>
                                    <?php
    endif; ?>
                                    <div>
                                        <strong style="color: var(--secondary);"><?php echo htmlspecialchars($u['full_name']); ?></strong><br>
                                        <span style="color: var(--text-muted); font-size: 0.8rem;">@<?php echo htmlspecialchars($u['username']); ?></span>
                                        <?php if ($u['nip']): ?>
                                            <div style="font-size: 0.75rem; color: #475569;">NIP: <?php echo htmlspecialchars($u['nip']); ?></div>
                                        <?php
    elseif ($u['nis']): ?>
                                            <div style="font-size: 0.75rem; color: #475569;">NIS: <?php echo htmlspecialchars($u['nis']); ?></div>
                                        <?php
    endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 5px;">
                                    <span class="pwd-mask" style="font-family: monospace; color: #64748b;">••••••••</span>
                                    <span class="pwd-text" style="display: none; font-family: monospace; font-size: 0.85rem; background: #f1f5f9; padding: 2px 5px; border-radius: 4px;"><?php echo htmlspecialchars($u['password']); ?></span>
                                    <button type="button" class="btn btn-secondary" style="padding: 2px 6px; font-size: 0.7rem;" onclick="togglePasswordRow(this)">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    </button>
                                </div>
                            </td>
                            <td>
                                <?php if ($u['gender'])
        echo($u['gender'] == 'L' ? '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block; vertical-align:middle; line-height:1;"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg> Laki-laki' : '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block; vertical-align:middle; line-height:1;"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg> Perempuan') . '<br>'; ?>
                                <?php if ($u['role'] == 'siswa' && $u['class_name']): ?>
                                    <span class="badge" style="background: #f1f5f9; margin-top: 5px; display: inline-block;">Kelas <?php echo $u['class_name']; ?></span>
                                <?php
    elseif ($u['role'] == 'guru' && $u['subject_name']): ?>
                                    <span class="badge" style="background: #f1f5f9; margin-top: 5px; display: inline-block;"><?php echo $u['subject_name']; ?></span>
                                <?php
    endif; ?>
                            </td>
                            <td>
                                <span class="role-badge role-<?php echo $u['role']; ?>">
                                    <?php echo $u['role']; ?>
                                </span>
                                <?php if ($u['status'] == 'graduated'): ?>
                                    <div style="font-size: 0.75rem; color: #64748b; margin-top: 4px; font-weight: 600;"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block; vertical-align:middle; line-height:1;"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg> TAMAT</div>
                                <?php
    endif; ?>
                                <?php if ($u['status'] == 'suspended'): ?>
                                    <div style="font-size: 0.75rem; color: #ef4444; margin-top: 4px; font-weight: 600;"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block; vertical-align:middle; line-height:1;"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg> SUSPEND</div>
                                <?php
    endif; ?>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a href="add_user.php?edit=<?php echo $u['id']; ?>" class="btn btn-secondary" style="padding: 6px 10px; font-size: 0.8rem;"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block; vertical-align:middle; line-height:1;"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg></a>
                                    <?php if ($u['username'] !== 'admin'): ?>
                                        <a href="manage_users.php?delete=<?php echo $u['id']; ?>" class="btn btn-danger" style="padding: 6px 10px; font-size: 0.8rem;" onclick="return confirm('Hapus?')"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block; vertical-align:middle; line-height:1;"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg></a>
                                    <?php
    endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php
endforeach; ?>
                    </tbody>
                </table>
            </div>
            </div>
        </div>
    </main>
</div>

<script>
function toggleClassFilter() {
    const role = document.getElementById('bulkRole').value;
    const classSelect = document.getElementById('bulkClass');
    classSelect.style.display = (role === 'siswa') ? 'block' : 'none';
    if (role !== 'siswa') classSelect.value = '';
}

function confirmBulkDelete() {
    const role = document.getElementById('bulkRole').value;
    const classSelect = document.getElementById('bulkClass');
    if (!role) { alert('Pilih role terlebih dahulu!'); return false; }
    const classText = classSelect.value && classSelect.options[classSelect.selectedIndex].text !== '-- Semua Kelas --'
        ? ' di kelas ' + classSelect.options[classSelect.selectedIndex].text
        : '';
    return confirm('Apakah Anda yakin ingin menghapus SEMUA ' + role + classText + '?\nTindakan ini TIDAK DAPAT DIBATALKAN!');
}

function togglePasswordRow(btn) {
    const mask = btn.previousElementSibling.previousElementSibling;
    const text = btn.previousElementSibling;
    if (text.style.display === 'none') {
        text.style.display = 'inline';
        mask.style.display = 'none';
        btn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>';
    } else {
        text.style.display = 'none';
        mask.style.display = 'inline';
        btn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>';
    }
}
</script>

</body>
</html>
