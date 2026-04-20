<?php
// src/admin/import_guru.php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

$error = "";
$success = "";
$import_report = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    if ($_FILES['file']['error'] === 0) {
        $ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));

        $rows = [];

        // 1. Check Extension
        if ($ext === 'csv') {
            $file = fopen($_FILES['file']['tmp_name'], 'r');
            while (($data = fgetcsv($file, 1000, ",")) !== FALSE) {
                $rows[] = $data;
            }
            fclose($file);
        }
        elseif ($ext === 'xlsx' || $ext === 'xls') {
            require_once '../../src/lib/SimpleXLSX.php';
            if ($xlsx = Shuchkin\SimpleXLSX::parse($_FILES['file']['tmp_name'])) {
                $rows = $xlsx->rows();
            }
            else {
                $error = "Gagal parsing Excel file.";
            }
        }
        else {
            $error = "Hanya file CSV atau Excel (.xlsx, .xls) yang diperbolehkan.";
        }

        if (empty($error) && !empty($rows)) {
            $row_count = 0;
            $success_count = 0;
            $fail_count = 0;

            // Skip headers, looking for numeric index
            $start_data = false;

            foreach ($rows as $index => $data) {
                $row_count++;

                // If we haven't found the data yet, look for 'No' in first col and 'Nama' in second
                if (!$start_data) {
                    if (isset($data[0]) && isset($data[1]) && strtolower(trim($data[0])) == 'no' && strtolower(trim($data[1])) == 'nama') {
                        $start_data = true;
                    }
                    continue; // Skip all header lines
                }

                $col_count = count($data);

                // If first column is empty, maybe end of file or empty row
                if (empty(trim($data[0]))) {
                    continue;
                }

                // Format:
                // [1] => Nama
                // [2] => NUPTK
                // [3] => JK (P/L)
                // [6] => NIP
                // [10] => Alamat Jalan

                $full_name = isset($data[1]) ? trim($data[1]) : '';
                $nuptk = isset($data[2]) ? trim($data[2]) : '';
                $jk = isset($data[3]) ? strtoupper(trim($data[3])) : ''; // P or L
                $nip_raw = isset($data[6]) ? trim($data[6]) : '';
                $address = isset($data[10]) ? trim($data[10]) : '';

                if (empty($full_name)) {
                    continue; // empty row
                }

                // Identity: use NIP, if empty use NUPTK. If both empty, cannot import
                $identity = $nip_raw ?: $nuptk;

                if (empty($identity)) {
                    $import_report[] = "Baris $row_count: Gagal import '{$full_name}' karena NIP dan NUPTK kosong.";
                    $fail_count++;
                    continue;
                }

                // Clean NIP (ensure no spaces)
                $identity = str_replace(' ', '', $identity);

                $username = $identity;
                $password = 'Smapat40311892'; // Default password
                $role = 'guru';
                $status = 'active';
                $nip = $identity;
                $nis = null;
                $class_id = null;
                $subject_id = null; // optional

                // Standardize gender
                $gender = null;
                if ($jk == 'L' || $jk == 'P') {
                    $gender = $jk;
                }

                try {
                    $stmt = $pdo->prepare("INSERT INTO users (username, password, full_name, role, gender, status, nip, nis, class_id, subject_id, address) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$username, $password, $full_name, $role, $gender, $status, $nip, $nis, $class_id, $subject_id, $address]);
                    $success_count++;
                }
                catch (PDOException $e) {
                    $fail_count++;
                    if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                        $import_report[] = "Baris $row_count: Duplicate NIP/NUPTK '$identity' untuk '{$full_name}'.";
                    }
                    else {
                        $import_report[] = "Baris $row_count: Error DB - " . $e->getMessage();
                    }
                }
            }
            $success = "Import selesai! Sukses: $success_count, Gagal: $fail_count";
        }
    }
    else {
        $error = "Terjadi kesalahan upload file.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Import Guru - Admin</title>
    <link rel="stylesheet" href="/public/assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .main-content { background: #f5f7fb !important; padding: 0 !important; }
        .page-hero {
            background: linear-gradient(135deg, #1e1b4b 0%, #312e81 50%, #4338ca 100%);
            padding: 2.5rem 3rem 4rem; position: relative; overflow: hidden; color: white;
        }
        .page-hero::before { content:''; position:absolute; right:-60px; top:-60px; width:250px; height:250px; background:rgba(255,255,255,0.07); border-radius:50%; pointer-events:none; }
        .page-hero h1 { font-size:1.6rem; font-weight:700; margin:0 0 0.8rem; }
        .back-link { display:inline-flex; align-items:center; gap:6px; color:rgba(255,255,255,0.8); text-decoration:none; font-size:0.85rem; background:rgba(255,255,255,0.1); padding:5px 12px; border-radius:20px; margin-bottom:1rem; }
        .back-link:hover { background:rgba(255,255,255,0.2); }
        .page-content { position:relative; margin-top:-2rem; padding:0 3rem 3rem; z-index:10; }
        .db-section { background:#fff; border:1px solid #e8edf5; border-radius:14px; overflow:hidden; padding:24px; box-shadow:0 1px 3px rgba(0,0,0,0.02); }
        @media (max-width:768px) { .page-content { padding:0 1rem 2rem; } .page-hero { padding:2rem 1.5rem 3rem; } }
    </style>
</head>
<body class="admin-full-layout">

<div class="app-container">
    <?php include '../templates/sidebar.php'; ?>
    
    <main class="main-content">
        <!-- Unified Hero Header -->
        <div class="page-hero">
            <a href="manage_users.php" class="back-link">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5"/><path d="M12 19l-7-7 7-7"/></svg>
                Kembali ke Daftar Pengguna
            </a>
            <div style="display:flex; justify-content:space-between; align-items:flex-end; flex-wrap:wrap; gap:16px;">
                <div style="position: relative; z-index: 2;">
                    <h1><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block; vertical-align:middle; line-height:1; margin-right:8px;"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg> Import Guru</h1>
                    <span style="background:rgba(255,255,255,0.2); padding:4px 10px; border-radius:6px; font-size:0.85rem; font-weight:600;">Upload file Excel Daftar Guru untuk mendaftarkan Guru secara massal.</span>
                </div>
            </div>
        </div>

        <div class="page-content">
            <div class="db-section" style="max-width: 800px; margin: 0 auto;">
            <?php if ($success): ?>
                <div class="badge badge-success" style="display:block; padding: 1rem; margin-bottom: 1rem; text-align:center;">
                    <?php echo $success; ?>
                </div>
            <?php
endif; ?>
            
            <?php if ($error): ?>
                <div class="badge badge-danger" style="display:block; padding: 1rem; margin-bottom: 1rem; text-align:center;">
                    <?php echo $error; ?>
                </div>
            <?php
endif; ?>

            <?php if (!empty($import_report)): ?>
                <div style="background: #fffbeb; border: 1px solid #fcd34d; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-size: 0.85rem; color: #92400e;">
                    <strong>Laporan Error:</strong>
                    <ul style="margin: 5px 0 0 15px; padding: 0;">
                        <?php foreach ($import_report as $rep): ?>
                            <li><?php echo htmlspecialchars($rep); ?></li>
                        <?php
    endforeach; ?>
                    </ul>
                </div>
            <?php
endif; ?>
            
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Pilih File Excel Data Guru</label>
                    <input type="file" name="file" accept=".csv, .xlsx, .xls" required>
                    <p style="margin-top: 10px; font-size: 0.85rem; color: #64748b;">
                        Fitur Baru: <strong>Otomatis mendeteksi format Excel Guru</strong><br>
                        Password default untuk Guru diset ke <strong>Smapat40311892</strong>. Mata Pelajaran opsional (kosong).
                    </p>
                </div>

                <div class="form-group">
                    <label>Catatan Format:</label>
                    <div style="font-size: 0.85rem; color: #475569; background: #f8fafc; padding: 10px; border-radius: 8px;">
                        Pastikan Excel memiliki format standar Data Guru dari Dapodik:<br>
                        - Kolom B: Nama<br>
                        - Kolom C: NUPTK<br>
                        - Kolom D: JK<br>
                        - Kolom G: NIP<br>
                        - Kolom K: Alamat<br>
                        <em>Baris data akan otomatis mendeteksi dimulai setelah kata kunci "No" dan "Nama".</em>
                    </div>
                </div>

                <button type="submit" class="btn" style="width: 100%;">Upload & Import</button>
            </form>
            </div>
        </div>
    </main>
</div>

</body>
</html>

