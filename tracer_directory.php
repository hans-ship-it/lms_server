<?php
// tracer_directory.php
session_start();
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Check role and status from DB
$stmt_user = $pdo->prepare("SELECT role, status FROM users WHERE id = ?");
$stmt_user->execute([$_SESSION['user_id']]);
$user = $stmt_user->fetch();

$is_alumni = ($user && $user['role'] === 'siswa' && $user['status'] === 'graduated');
$is_guru_kepsek = ($user && in_array($user['role'], ['guru', 'kepsek']));

if (!$is_alumni && !$is_guru_kepsek) {
    echo "<script>alert('Akses Ditolak.'); window.location.href='index.php';</script>";
    exit;
}

// Filters logic
$search_query = $_GET['q'] ?? '';
$filter_tahun = $_GET['tahun'] ?? '';
$filter_kegiatan = $_GET['kegiatan'] ?? '';

// We query tracer_study and join users
$sql = "SELECT u.full_name, t.tahun_lulus, t.kegiatan, t.nama_instansi, t.jurusan_posisi, t.foto 
        FROM tracer_study t 
        JOIN users u ON t.user_id = u.id 
        WHERE u.status = 'graduated'";
$params = [];

if (!empty($search_query)) {
    $sql .= " AND (u.full_name LIKE ? OR t.nama_instansi LIKE ? OR t.jurusan_posisi LIKE ?)";
    $q = "%$search_query%";
    $params[] = $q; $params[] = $q; $params[] = $q;
}

if (!empty($filter_tahun)) {
    $sql .= " AND t.tahun_lulus = ?";
    $params[] = $filter_tahun;
}

if (!empty($filter_kegiatan)) {
    $sql .= " AND t.kegiatan = ?";
    $params[] = $filter_kegiatan;
}

$sql .= " ORDER BY t.tahun_lulus DESC, u.full_name ASC";

$stmt_alumni = $pdo->prepare($sql);
$stmt_alumni->execute($params);
$alumni_list = $stmt_alumni->fetchAll();

// Get unique years for the dropdown
$stmt_years = $pdo->query("SELECT DISTINCT tahun_lulus FROM tracer_study WHERE tahun_lulus IS NOT NULL ORDER BY tahun_lulus DESC");
$years = $stmt_years->fetchAll(PDO::FETCH_COLUMN);

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Direktori Jejak Alumni</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', sans-serif; background-color: #f1f5f9; color: #334155; }
        /* Same sidebar CSS as tracer_form.php */
        .tracer-layout { display: flex; min-height: 100vh; }
        .tracer-sidebar { width: 260px; background: #ffffff; border-right: 1px solid #e2e8f0; padding: 30px 20px; display: flex; flex-direction: column; position: fixed; top: 0; left: 0; bottom: 0; z-index: 10; }
        .tracer-content-wrapper { flex: 1; margin-left: 260px; padding: 40px; }
        .sidebar-header { margin-bottom: 30px; text-align: center; }
        .sidebar-header h2 { margin: 0; font-size: 1.25rem; color: #1e293b; font-weight: 700; }
        .sidebar-header p { color: #64748b; font-size: 0.85rem; margin: 5px 0 0 0; }
        .sidebar-menu { display: flex; flex-direction: column; gap: 8px; }
        .sidebar-menu a { padding: 12px 16px; border-radius: 8px; color: #475569; text-decoration: none; font-weight: 500; display: flex; align-items: center; gap: 12px; transition: all 0.2s; font-size: 0.95rem; }
        .sidebar-menu a:hover:not(.active) { background: #f8fafc; color: #1e293b; }
        .sidebar-menu a.active { background: #3b82f6; color: white; box-shadow: 0 4px 6px rgba(59, 130, 246, 0.25); }
        .sidebar-logout { margin-top: auto; border-top: 1px solid #e2e8f0; padding-top: 20px; }
        .sidebar-logout a { color: #ef4444; display: flex; align-items: center; gap: 10px; text-decoration: none; font-weight: 600; padding: 12px 16px; border-radius: 8px; font-size: 0.95rem; }
        .sidebar-logout a:hover { background: #fef2f2; }
        
        /* Filter Bar */
        .filter-bar {
            background: white; border-radius: 12px; padding: 20px; margin-bottom: 25px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.02); border: 1px solid #e2e8f0;
            display: flex; gap: 15px; align-items: flex-end; flex-wrap: wrap;
        }
        .filter-group { display: flex; flex-direction: column; gap: 6px; flex: 1; min-width: 150px; }
        .filter-group label { font-size: 0.85rem; font-weight: 600; color: #475569; margin: 0; }
        .form-control {
            width: 100%; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 6px;
            font-family: inherit; font-size: 0.95rem;
        }
        .btn-filter {
            background: #1e293b; color: white; padding: 10px 20px; border: none;
            border-radius: 6px; font-weight: 600; cursor: pointer; transition: 0.2s;
            height: 42px;
        }
        .btn-filter:hover { background: #0f172a; }
        
        /* Table styles */
        .table-card {
            background: white; border-radius: 12px; border: 1px solid #e2e8f0;
            overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        }
        .alumni-table {
            width: 100%; border-collapse: collapse; text-align: left;
        }
        .alumni-table th, .alumni-table td {
            padding: 15px 20px; border-bottom: 1px solid #e2e8f0; vertical-align: middle;
        }
        .alumni-table th {
            background: #f8fafc; font-size: 0.85rem; text-transform: uppercase;
            font-weight: 600; color: #64748b; letter-spacing: 0.05em;
        }
        .alumni-table tr:last-child td { border-bottom: none; }
        .alumni-table tr:hover { background: #f8fafc; }
        .alumni-photo {
            width: 40px; height: 40px; border-radius: 50%; object-fit: cover;
            border: 2px solid #e2e8f0; display: block;
        }
        .avatar-placeholder {
            width: 40px; height: 40px; border-radius: 50%; background: #e2e8f0;
            display: flex; align-items: center; justify-content: center; color: #64748b; font-weight: bold;
        }
        .badge {
            display: inline-block; padding: 4px 10px; border-radius: 20px;
            font-size: 0.75rem; font-weight: 600;
        }
        .badge.Kuliah { background: #dbeafe; color: #1e40af; }
        .badge.Kerja { background: #dcfce7; color: #166534; }
        .badge.Wirausaha { background: #fef08a; color: #854d0e; }
        .badge.BelumTidakBekerja { background: #f1f5f9; color: #475569; }

        @media (max-width: 768px) {
            .tracer-sidebar { width: 100%; height: auto; position: static; border-right: none; border-bottom: 1px solid #e2e8f0; padding: 20px 15px; }
            .tracer-content-wrapper { margin-left: 0; padding: 20px 15px; }
            .tracer-layout { flex-direction: column; }
            .filter-bar { flex-direction: column; align-items: stretch; }
            .btn-filter { width: 100%; }
            .tracer-content-wrapper h1 { font-size: 1.4rem !important; }
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
                <?php if ($is_alumni): ?>
                <a href="tracer_form.php">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                    Status Karir Saya
                </a>
                <?php endif; ?>
                <a href="tracer_directory.php" class="active">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    Direktori Jejak Alumni
                </a>
            </div>
            <div class="sidebar-logout">
                <?php if ($is_guru_kepsek): ?>
		<?php endif; ?>
                <a href="src/auth/logout.php">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                    Keluar Aplikasi
                </a>
            </div>
        </div>

        <div class="tracer-content-wrapper">
            <h1 style="color: #1e293b; margin-top: 0; margin-bottom: 25px; font-weight: 700; font-size: 1.8rem;">Direktori Jejak Alumni</h1>
            
            <form class="filter-bar" method="GET" action="">
                <div class="filter-group" style="flex: 2;">
                    <label>Pencarian</label>
                    <input type="text" name="q" class="form-control" placeholder="Cari Nama atau Instansi..." value="<?php echo htmlspecialchars($search_query); ?>">
                </div>
                <div class="filter-group">
                    <label>Tahun Lulus</label>
                    <select name="tahun" class="form-control">
                        <option value="">Semua Tahun</option>
                        <?php foreach($years as $yr): ?>
                            <option value="<?php echo htmlspecialchars($yr); ?>" <?php echo $filter_tahun == $yr ? 'selected' : ''; ?>><?php echo htmlspecialchars($yr); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Kegiatan/Kategori</label>
                    <select name="kegiatan" class="form-control">
                        <option value="">Semua Kategori</option>
                        <option value="Kuliah" <?php echo $filter_kegiatan == 'Kuliah' ? 'selected' : ''; ?>>Kuliah</option>
                        <option value="Kerja" <?php echo $filter_kegiatan == 'Kerja' ? 'selected' : ''; ?>>Kerja</option>
                        <option value="Wirausaha" <?php echo $filter_kegiatan == 'Wirausaha' ? 'selected' : ''; ?>>Wirausaha</option>
                        <option value="Belum/Tidak Bekerja" <?php echo $filter_kegiatan == 'Belum/Tidak Bekerja' ? 'selected' : ''; ?>>Belum/Tidak Bekerja</option>
                    </select>
                </div>
                <button type="submit" class="btn-filter">Terapkan</button>
                <?php if(!empty($_GET)): ?>
                    <a href="tracer_directory.php" style="color:#64748b; font-size: 0.9rem; text-decoration:none; display:flex; align-items:center; height:42px; padding:0 10px;">Reset</a>
                <?php endif; ?>
            </form>

            <div class="table-card">
                <div style="overflow-x: auto;">
                    <table class="alumni-table">
                        <thead>
                            <tr>
                                <th width="60">Foto</th>
                                <th>Nama Alumni</th>
                                <th>Lulusan</th>
                                <th>Kegiatan</th>
                                <th>Instansi / Tempat</th>
                                <th>Jurusan / Posisi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($alumni_list) > 0): ?>
                                <?php foreach ($alumni_list as $alumni): 
                                    $badge_class = str_replace(['/', ' '], '', $alumni['kegiatan']);
                                ?>
                                    <tr>
                                        <td>
                                            <?php if (!empty($alumni['foto'])): ?>
                                                <img src="public/uploads/tracer/<?php echo htmlspecialchars($alumni['foto']); ?>" class="alumni-photo" alt="Foto">
                                            <?php else: ?>
                                                <div class="avatar-placeholder">
                                                    <?php echo strtoupper(substr($alumni['full_name'], 0, 1)); ?>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td><strong><?php echo htmlspecialchars($alumni['full_name']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($alumni['tahun_lulus']); ?></td>
                                        <td><span class="badge <?php echo $badge_class; ?>"><?php echo htmlspecialchars($alumni['kegiatan']); ?></span></td>
                                        <td><?php echo $alumni['kegiatan'] !== 'Belum/Tidak Bekerja' && $alumni['nama_instansi'] !== '-' ? htmlspecialchars($alumni['nama_instansi']) : '<em style="color:#cbd5e1;">-</em>'; ?></td>
                                        <td><?php echo $alumni['kegiatan'] !== 'Belum/Tidak Bekerja' && $alumni['jurusan_posisi'] !== '-' ? htmlspecialchars($alumni['jurusan_posisi']) : '<em style="color:#cbd5e1;">-</em>'; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" style="text-align: center; color: #64748b; padding: 30px;">
                                        Pencarian tidak menemukan hasil.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
