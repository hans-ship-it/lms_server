<?php
// tracer_form.php
session_start();
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Check role and status from DB
$stmt_user = $pdo->prepare("SELECT role, status, full_name, username FROM users WHERE id = ?");
$stmt_user->execute([$_SESSION['user_id']]);
$user = $stmt_user->fetch();

if (!$user || $user['role'] !== 'siswa' || $user['status'] !== 'graduated') {
    // If not a graduated student
    echo "<script>alert('Akses Ditolak. Halaman ini khusus untuk Alumni SMA Negeri 4 Makassar.'); window.location.href='index.php';</script>";
    exit;
}

// Fetch existing data if any
$stmt_tracer = $pdo->prepare("SELECT * FROM tracer_study WHERE user_id = ?");
$stmt_tracer->execute([$_SESSION['user_id']]);
$tracer_data = $stmt_tracer->fetch();

$def_kegiatan = $tracer_data['kegiatan'] ?? '';
$def_instansi = $tracer_data['nama_instansi'] ?? '';
$def_jurusan = $tracer_data['jurusan_posisi'] ?? '';
$def_tahun = $tracer_data['tahun_lulus'] ?? date('Y');

// Handle Form Submission
$success_msg = "";
$error_msg = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $kegiatan = $_POST['kegiatan'] ?? '';
    $nama_instansi = trim($_POST['nama_instansi'] ?? '');
    $jurusan_posisi = trim($_POST['jurusan_posisi'] ?? '');

    // Check if tracer data exists to grab its 'tahun_lulus', or fallback to current year
    $tahun_lulus = $tracer_data && !empty($tracer_data['tahun_lulus']) ? $tracer_data['tahun_lulus'] : date('Y');

    // File upload handling
    $foto_name = $tracer_data['foto'] ?? null;
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'public/uploads/tracer/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        
        $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
            $new_filename = 'tracer_' . $_SESSION['user_id'] . '_' . time() . '.' . $ext;
            if (move_uploaded_file($_FILES['foto']['tmp_name'], $upload_dir . $new_filename)) {
                $foto_name = $new_filename;
            }
        }
    }

    if (empty($kegiatan) || empty($nama_instansi) || empty($jurusan_posisi)) {
        $error_msg = "Semua kolom kegiatan, instansi, dan jurusan wajib diisi.";
    } else {
        try {
            // Upsert
            $stmt_upsert = $pdo->prepare("
                INSERT INTO tracer_study (user_id, kegiatan, nama_instansi, jurusan_posisi, tahun_lulus, foto) 
                VALUES (?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE 
                kegiatan = VALUES(kegiatan), 
                nama_instansi = VALUES(nama_instansi), 
                jurusan_posisi = VALUES(jurusan_posisi),
                foto = VALUES(foto)
            ");
            $stmt_upsert->execute([$_SESSION['user_id'], $kegiatan, $nama_instansi, $jurusan_posisi, $tahun_lulus, $foto_name]);
            $success_msg = "Status karir berhasil disimpan.";
            
            // Reload tracer data
            $stmt_tracer->execute([$_SESSION['user_id']]);
            $tracer_data = $stmt_tracer->fetch();
            $def_kegiatan = $tracer_data['kegiatan'];
            $def_instansi = $tracer_data['nama_instansi'];
            $def_jurusan = $tracer_data['jurusan_posisi'];

        } catch (PDOException $e) {
            $error_msg = "Terjadi kesalahan: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembaruan Status Karir - Portal Tracer</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f1f5f9;
            color: #334155;
        }
        .tracer-layout {
            display: flex;
            min-height: 100vh;
        }
        .tracer-sidebar {
            width: 260px;
            background: #ffffff;
            border-right: 1px solid #e2e8f0;
            padding: 30px 20px;
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0; left: 0; bottom: 0;
            z-index: 10;
        }
        .tracer-content-wrapper {
            flex: 1;
            margin-left: 260px;
            padding: 40px;
        }
        .sidebar-header {
            margin-bottom: 30px;
            text-align: center;
        }
        .sidebar-header h2 {
            margin: 0; font-size: 1.25rem; color: #1e293b; font-weight: 700;
        }
        .sidebar-header p {
            color: #64748b; font-size: 0.85rem; margin: 5px 0 0 0;
        }
        .sidebar-menu {
            display: flex; flex-direction: column; gap: 8px;
        }
        .sidebar-menu a {
            padding: 12px 16px;
            border-radius: 8px; color: #475569; text-decoration: none;
            font-weight: 500; display: flex; align-items: center; gap: 12px;
            transition: all 0.2s; font-size: 0.95rem;
        }
        .sidebar-menu a:hover:not(.active) {
            background: #f8fafc; color: #1e293b;
        }
        .sidebar-menu a.active {
            background: #3b82f6; color: white;
            box-shadow: 0 4px 6px rgba(59, 130, 246, 0.25);
        }
        .sidebar-logout {
            margin-top: auto; border-top: 1px solid #e2e8f0; padding-top: 20px;
        }
        .sidebar-logout a {
            color: #ef4444; display: flex; align-items: center; gap: 10px;
            text-decoration: none; font-weight: 600; padding: 12px 16px; border-radius: 8px;
            font-size: 0.95rem;
        }
        .sidebar-logout a:hover {
            background: #fef2f2;
        }
        .tracer-card {
            background: white; border-radius: 12px; padding: 30px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05); max-width: 800px;
        }
        .form-group { margin-bottom: 20px; }
        .form-label {
            display: block; margin-bottom: 8px; font-weight: 600; font-size: 0.9rem; color: #475569;
        }
        .form-control {
            width: 100%; padding: 10px 15px; border: 1px solid #cbd5e1;
            border-radius: 6px; font-family: inherit; font-size: 0.95rem; box-sizing: border-box;
            transition: border-color 0.2s;
        }
        .form-control:focus {
            outline: none; border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        .btn-submit {
            background: #3b82f6; color: white; padding: 12px 20px; border: none;
            border-radius: 6px; font-size: 1rem; font-weight: 600; cursor: pointer;
            transition: background 0.2s; width: 100%; margin-top: 10px;
        }
        .btn-submit:hover { background: #2563eb; }
        .alert {
            padding: 12px 15px; border-radius: 6px; margin-bottom: 20px; font-size: 0.9rem;
        }
        .alert-success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .alert-error { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        .user-info {
            display: flex; align-items: center; gap: 15px; margin-bottom: 25px;
            padding: 15px; background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0;
        }
        .user-avatar {
            width: 50px; height: 50px; border-radius: 50%;
            background: #3b82f6; color: white; display: flex; align-items: center; justify-content: center;
            font-size: 1.5rem; font-weight: bold;
        }
        @media (max-width: 768px) {
            .tracer-sidebar { width: 100%; height: auto; position: static; border-right: none; border-bottom: 1px solid #e2e8f0; padding: 20px 15px; }
            .tracer-content-wrapper { margin-left: 0; padding: 20px 15px; }
            .tracer-layout { flex-direction: column; }
            .form-grid { grid-template-columns: 1fr !important; gap: 15px !important; }
            .tracer-content-wrapper h1 { font-size: 1.4rem !important; }
            .user-info { flex-direction: column; text-align: center; gap: 10px; }
        }
    </style>
</head>
<body>
    <div class="tracer-layout">
        <div class="tracer-sidebar">
            <div class="sidebar-header">
                <h2>Portal Tracer</h2>
                <p>SMAN 4 Makassar</p>
            </div>
            <div class="sidebar-menu">
                <a href="tracer_form.php" class="active">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                    Status Karir Saya
                </a>
                <a href="tracer_directory.php">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    Direktori Jejak Alumni
                </a>
            </div>
            <div class="sidebar-logout">
                <a href="src/auth/logout.php">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                    Keluar Aplikasi
                </a>
            </div>
        </div>

        <div class="tracer-content-wrapper">
            <h1 style="color: #1e293b; margin-top: 0; margin-bottom: 25px; font-weight: 700; font-size: 1.8rem;">Pembaruan Status Karir</h1>
            
            <div class="tracer-card">
                <?php if ($success_msg): ?>
                    <div class="alert alert-success"><?php echo $success_msg; ?></div>
                <?php endif; ?>
                <?php if ($error_msg): ?>
                    <div class="alert alert-error"><?php echo $error_msg; ?></div>
                <?php endif; ?>

                <div class="user-info">
                    <?php if (!empty($tracer_data['foto'])): ?>
                        <img src="public/uploads/tracer/<?php echo htmlspecialchars($tracer_data['foto']); ?>" alt="Foto" width="50" height="50" style="border-radius:50%; object-fit:cover;">
                    <?php else: ?>
                        <div class="user-avatar"><?php echo strtoupper(substr($user['full_name'], 0, 1)); ?></div>
                    <?php endif; ?>
                    <div>
                        <h3 style="margin: 0; font-size: 1.1rem; color: #1e293b;"><?php echo htmlspecialchars($user['full_name']); ?></h3>
                        <p style="margin: 2px 0 0 0; color: #64748b; font-size: 0.9rem;">NIS: <?php echo htmlspecialchars($user['username']); ?> &bull; Lulusan <?php echo htmlspecialchars($def_tahun); ?></p>
                    </div>
                </div>

                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="form-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div class="form-group">
                            <label class="form-label" for="foto">Upload Foto Profil (Opsional)</label>
                            <input type="file" class="form-control" name="foto" id="foto" accept="image/*" style="padding: 7px 10px;">
                            <small style="color: #64748b; display: block; margin-top: 5px;">Maksimal 2MB, format JPG/PNG</small>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="kegiatan">Kegiatan Saat Ini</label>
                            <select class="form-control" name="kegiatan" id="kegiatan" required>
                                <option value="">-- Pilih Kegiatan --</option>
                                <option value="Kuliah" <?php echo ($def_kegiatan == 'Kuliah') ? 'selected' : ''; ?>>Kuliah (Pendidikan Tinggi)</option>
                                <option value="Kerja" <?php echo ($def_kegiatan == 'Kerja') ? 'selected' : ''; ?>>Bekerja</option>
                                <option value="Wirausaha" <?php echo ($def_kegiatan == 'Wirausaha') ? 'selected' : ''; ?>>Wirausaha / Usaha Mandiri</option>
                                <option value="Belum/Tidak Bekerja" <?php echo ($def_kegiatan == 'Belum/Tidak Bekerja') ? 'selected' : ''; ?>>Belum / Tidak Bekerja</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="nama_instansi">Nama Institusi / Perusahaan / Usaha</label>
                        <input type="text" class="form-control" name="nama_instansi" id="nama_instansi" value="<?php echo htmlspecialchars($def_instansi); ?>" required placeholder="Contoh: Univ. Indonesia, PT Pertamina, Toko Baju ABC">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="jurusan_posisi">Jurusan (Kuliah) / Posisi Pekerjaan</label>
                        <input type="text" class="form-control" name="jurusan_posisi" id="jurusan_posisi" value="<?php echo htmlspecialchars($def_jurusan); ?>" required placeholder="Contoh: Kedokteran, Sales Manager">
                    </div>

                    <button type="submit" class="btn-submit">Simpan Status Karir</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
