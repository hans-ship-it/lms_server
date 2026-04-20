<?php
// src/admin/manage_snbp.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('memory_limit', '512M');
set_time_limit(300);

// Log PALING AWAL sebelum apapun - untuk diagnosa AJAX
$early_log = '/tmp/snbp_debug.txt';
file_put_contents($early_log, date('[Y-m-d H:i:s] ') . "REQUEST MASUK: " . $_SERVER['REQUEST_METHOD'] . " | ajax_batch=" . (isset($_POST['ajax_batch']) ? '1' : '0') . " | COOKIE=" . (isset($_COOKIE[session_name()]) ? 'ADA' : 'TIDAK ADA') . "\n", FILE_APPEND);

session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    // Jika ini adalah request AJAX, jangan redirect - kirim JSON error
    if (isset($_POST['ajax_batch']) || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')) {
        file_put_contents($early_log, date('[Y-m-d H:i:s] ') . "SESSION GAGAL - user_id=" . ($_SESSION['user_id'] ?? 'TIDAK ADA') . " role=" . ($_SESSION['role'] ?? 'TIDAK ADA') . "\n", FILE_APPEND);
        http_response_code(401);
        echo "AUTH_FAILED";
        exit;
    }
    header("Location: ../../login.php");
    exit;
}

$upload_dir = __DIR__ . '/../../public/uploads/index_kelulusan_snbp/';
$upload_dir = str_replace('\\', '/', $upload_dir); // Normalize Windows backslash
$debug_log = '/tmp/snbp_debug.txt';

// Create directory if not exists
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}


// Fungsi Kompresi Gambar
function compressImage($source, $destination, $quality) {
    if (!function_exists('imagecreatefromjpeg')) return false; // Pastikan ekstensi GD aktif
    if (!file_exists($source)) return false;
    $info = getimagesize($source);
    if (!$info) return false;
    
    $image = null;
    if ($info['mime'] == 'image/jpeg') {
        $image = imagecreatefromjpeg($source);
    } elseif ($info['mime'] == 'image/gif') {
        $image = imagecreatefromgif($source);
    } elseif ($info['mime'] == 'image/png') {
        $image = imagecreatefrompng($source);
        if ($image !== false) {
            imagepalettetotruecolor($image);
            imagealphablending($image, true);
            imagesavealpha($image, true);
            $bg = imagecolorallocatealpha($image, 255, 255, 255, 127);
            imagefill($image, 0, 0, $bg);
        }
    } elseif ($info['mime'] == 'image/webp') {
        $image = imagecreatefromwebp($source);
    }
    
    if (!$image) return false;
    
    // Convert and save as JPEG to maximize compression (or keep as webp if you're on very modern PHP, but jpeg is safer)
    imagejpeg($image, $destination, $quality); 
    imagedestroy($image);
    return true;
}

// Handle Upload Multiple Files (Dengan Kompresi)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'upload') {
    file_put_contents($debug_log, date('[Y-m-d H:i:s] ') . "AJAX UPLOAD RECEIVED:\nupload_dir: $upload_dir\nPOST: " . print_r($_POST, true) . "\nFILES: " . print_r($_FILES, true) . "\n", FILE_APPEND);

    if (isset($_FILES['photos']) && is_array($_FILES['photos']['name'])) {
        $success_count = 0;
        $error_count = 0;
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

        foreach ($_FILES['photos']['name'] as $key => $name) {
            $err_code = $_FILES['photos']['error'][$key];
            file_put_contents($debug_log, "PROCESSING FILE: $name (Error code: $err_code)\n", FILE_APPEND);

            if ($err_code === UPLOAD_ERR_OK) {
                $tmp_name = $_FILES['photos']['tmp_name'][$key];
                $type = $_FILES['photos']['type'][$key];
                
                if (in_array($type, $allowed_types)) {
                    // Penamaan file baru (paksa jadi format .jpg karena dikompres ke JPEG)
                    $new_name = time() . '_' . rand(1000, 9999) . '_' . preg_replace('/[^a-zA-Z0-9_.-]/', '', pathinfo($name, PATHINFO_FILENAME)) . '.jpg';
                    $target_path = $upload_dir . $new_name;
                    
                    file_put_contents($debug_log, "TARGET PATH: $target_path\n", FILE_APPEND);
                    file_put_contents($debug_log, "TMP EXISTS: " . (file_exists($tmp_name) ? 'YES' : 'NO') . "\n", FILE_APPEND);
                    file_put_contents($debug_log, "DIR WRITEABLE: " . (is_writable($upload_dir) ? 'YES' : 'NO') . "\n", FILE_APPEND);

                    // Kompres dengan GD (Kualitas 60%)
                    if (compressImage($tmp_name, $target_path, 60)) {
                        file_put_contents($debug_log, "GD COMPRESS: SUCCESS\n", FILE_APPEND);
                        $success_count++;
                    } else {
                        // Fallback jika GD gagal, pindahkan file aslinya
                        if (move_uploaded_file($tmp_name, $target_path)) {
                            file_put_contents($debug_log, "MOVE FILE: SUCCESS\n", FILE_APPEND);
                            $success_count++;
                        } else {
                            file_put_contents($debug_log, "MOVE FILE: FAILED\n", FILE_APPEND);
                            $error_count++;
                        }
                    }
                } else {
                    file_put_contents($debug_log, "FILE TYPE REJECTED: $type\n", FILE_APPEND);
                    $error_count++;
                }
            } else if ($_FILES['photos']['error'][$key] !== UPLOAD_ERR_NO_FILE) {
                $error_count++;
            }
        }

        if ($success_count > 0) {
            $_SESSION['success_msg'] = "$success_count Gambar berhasil diunggah." . ($error_count > 0 ? " ($error_count file gagal atau format tidak didukung)." : "");
        } elseif ($error_count > 0) {
            $_SESSION['error_msg'] = "Gagal mengunggah file. Pastikan format gambar yang dimasukkan benar.";
        } else {
            $_SESSION['error_msg'] = "Tidak ada file yang dipilih.";
        }
        
        // Cek jika AJAX Batch
        if (isset($_POST['ajax_batch'])) {
            if ($success_count > 0) {
                echo "ok";
            } else {
                http_response_code(400);
                echo "failed: Format dilarang atau gagal pindah file. Allowed: JPG, PNG, WEBP";
            }
            exit;
        }
    } else {
        if (isset($_POST['ajax_batch'])) {
            http_response_code(400); echo "No files."; exit;
        }
        $_SESSION['error_msg'] = "Gagal memproses form unggahan.";
    }
    header("Location: manage_snbp.php");
    exit;
}

// Handle Save Order (AJAX)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_order') {
    $raw = $_POST['order'] ?? '';
    $order = json_decode($raw, true);
    if (is_array($order)) {
        // Sanitasi: pastikan semua nama file valid dan ada di folder
        $clean = [];
        foreach ($order as $f) {
            $f = basename($f);
            if ($f && file_exists($upload_dir . $f)) $clean[] = $f;
        }
        file_put_contents($upload_dir . 'order.json', json_encode($clean));
        echo 'ok';
    } else {
        http_response_code(400);
        echo 'invalid';
    }
    exit;
}

// Handle Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    if (!empty($_POST['filename'])) {
        $filename = basename($_POST['filename']);
        $filepath = $upload_dir . $filename;
        if (file_exists($filepath) && is_file($filepath)) {
            unlink($filepath);
            // Hapus juga dari order.json
            if (file_exists($upload_dir . 'order.json')) {
                $ord = json_decode(file_get_contents($upload_dir . 'order.json'), true) ?? [];
                $ord = array_values(array_filter($ord, fn($f) => $f !== $filename));
                file_put_contents($upload_dir . 'order.json', json_encode($ord));
            }
            $_SESSION['success_msg'] = "Gambar berhasil dihapus.";
        } else {
            $_SESSION['error_msg'] = "File tidak ditemukan.";
        }
    }
    header("Location: manage_snbp.php");
    exit;
}

// Get list of images, respecting order.json
$all_files = [];
$pattern = $upload_dir . '*.{jpg,jpeg,png,gif,webp}';
foreach (glob($pattern, GLOB_BRACE) as $file) {
    if (is_file($file)) $all_files[] = basename($file);
}

$images = [];
$order_file = $upload_dir . 'order.json';
if (file_exists($order_file)) {
    $saved_order = json_decode(file_get_contents($order_file), true) ?? [];
    // Tampilkan yang ada di order.json dulu
    foreach ($saved_order as $f) {
        if (in_array($f, $all_files)) $images[] = $f;
    }
    // Tambahkan foto baru yang belum ada di order
    foreach ($all_files as $f) {
        if (!in_array($f, $images)) $images[] = $f;
    }
} else {
    // Fallback: sort by file modified time (newest first)
    usort($all_files, fn($a, $b) => filemtime($upload_dir . $b) - filemtime($upload_dir . $a));
    $images = $all_files;
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Index Kelulusan SNBP</title>
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
        .page-content { position:relative; margin-top:-2.5rem; padding:0 3rem 3rem; z-index:10; }
        .upload-section {
            background: #fff;
            border: 1px solid #e8edf5;
            border-radius: 14px;
            padding: 1.75rem 2rem;
            margin-bottom: 1.5rem;
        }
        .gallery-grid {
            display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 1.5rem;
        }
        .gallery-item {
            background: #fff;
            border: 1px solid #e8edf5;
            border-radius: 12px;
            padding: 1rem;
            text-align: center;
        }
        .gallery-img {
            width: 100%; height: 150px; object-fit: contain; border-radius: 8px; margin-bottom: 1rem; background: #f8fafc;
        }
    </style>
</head>
<body>

<div class="app-container">
    <?php include '../templates/sidebar.php'; ?>
    
    <main class="main-content">
        <div class="page-hero">
            <h1>
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:middle;margin-right:8px;"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 11.23a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.6 0.5h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 7.91"/><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07"/></svg>
                Kelola Index Kelulusan SNBP
            </h1>
            <p>Unggah dan hapus foto siswa-siswi yang lulus SNBP untuk ditampilkan di halaman utama.</p>
        </div>

        <div class="page-content">

        <?php if (isset($_SESSION['success_msg'])): ?>
            <div style="background:#f0fdf4; color:#166534; padding:14px 18px; border-radius:10px; margin-bottom:16px; border:1px solid #bbf7d0;">
                <?php echo $_SESSION['success_msg']; unset($_SESSION['success_msg']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_msg'])): ?>
            <div style="background:#fef2f2; color:#991b1b; padding:14px 18px; border-radius:10px; margin-bottom:16px; border:1px solid #fecaca;">
                <?php echo $_SESSION['error_msg']; unset($_SESSION['error_msg']); ?>
            </div>
        <?php endif; ?>

        <!-- Upload UI -->
        <div id="uploadOverlay" style="display:none; position:fixed; inset:0; background:rgba(15,23,42,0.8); z-index:9999; align-items:center; justify-content:center;">
            <div style="background:#fff; border-radius:16px; padding:2rem 2.5rem; max-width:460px; width:90%; text-align:center; box-shadow:0 25px 50px rgba(0,0,0,0.4);">
                <div style="width:52px; height:52px; border:5px solid #e2e8f0; border-top-color:#3b82f6; border-radius:50%; animation:spin 0.8s linear infinite; margin:0 auto 1.25rem;"></div>
                <h3 id="overlayTitle" style="font-size:1rem; font-weight:700; color:#1e293b; margin:0 0 0.4rem;">Menyiapkan...</h3>
                <p id="overlayDetail" style="font-size:0.82rem; color:#64748b; margin:0 0 1rem; line-height:1.5;"><b>Jangan tutup atau refresh</b> halaman ini.</p>
                <!-- Progress bar real -->
                <div style="width:100%; height:10px; background:#e2e8f0; border-radius:5px; overflow:hidden; margin-bottom:0.5rem;">
                    <div id="overlayBar" style="height:100%; width:0%; background:#3b82f6; border-radius:5px; transition:width 0.3s ease;"></div>
                </div>
                <p id="overlayPercent" style="font-size:0.8rem; font-weight:600; color:#3b82f6; margin:0 0 0.25rem;">0%</p>
                <p id="overlayFileCount" style="font-size:0.75rem; color:#94a3b8; margin:0;"></p>
            </div>
        </div>

        <style>
            @keyframes spin { to { transform: rotate(360deg); } }
        </style>

        <div class="upload-section">
            <h2 style="font-size:1.2rem; margin-bottom:1rem;">Unggah Gambar Baru</h2>
            <form id="uploadForm" action="manage_snbp.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="upload">
                <input type="hidden" name="ajax_batch" value="1">
                <div style="display:flex; gap:1rem; align-items:center;">
                    <input type="file" id="photoInput" name="photos[]" accept="image/*" multiple required
                           style="padding:0.5rem; border:1px solid #cbd5e1; border-radius:6px; flex:1;"
                           title="Pilih banyak foto sekaligus dengan Ctrl+Click">
                    <button type="submit" id="uploadBtn" class="btn btn-primary" style="padding:0.75rem 1.5rem; white-space:nowrap;">
                        Unggah Foto
                    </button>
                </div>
            </form>
            <p style="margin-top:0.5rem; font-size:0.85rem; color:#64748b;">Pilih <b>banyak foto sekaligus</b> — sistem akan mengirimnya <b>satu per satu</b> otomatis. Format: JPG, PNG, WEBP.</p>
        </div>

        <script>
        document.getElementById('uploadForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const files = document.getElementById('photoInput').files;
            if (files.length === 0) return;

            const overlay      = document.getElementById('uploadOverlay');
            const overlayTitle = document.getElementById('overlayTitle');
            const overlayDetail= document.getElementById('overlayDetail');
            const overlayBar   = document.getElementById('overlayBar');
            const overlayPct   = document.getElementById('overlayPercent');
            const overlayCount = document.getElementById('overlayFileCount');

            overlay.style.display = 'flex';

            let done = 0, failed = 0;

            // Kompres gambar di browser sebelum kirim (agar ukuran kecil)
            const compress = (file) => new Promise((resolve) => {
                if (!file.type.startsWith('image/')) { resolve(file); return; }
                const reader = new FileReader();
                reader.readAsDataURL(file);
                reader.onload = (ev) => {
                    const img = new Image();
                    img.src = ev.target.result;
                    img.onload = () => {
                        const MAX = 1280;
                        let w = img.width, h = img.height;
                        if (w > MAX) { h = h * MAX / w; w = MAX; }
                        if (h > MAX) { w = w * MAX / h; h = MAX; }
                        const canvas = document.createElement('canvas');
                        canvas.width = w; canvas.height = h;
                        canvas.getContext('2d').drawImage(img, 0, 0, w, h);
                        canvas.toBlob(blob => {
                            if (blob) resolve(new File([blob], file.name.replace(/\.[^.]+$/, '.jpg'), {type:'image/jpeg'}));
                            else resolve(file);
                        }, 'image/jpeg', 0.82);
                    };
                    img.onerror = () => resolve(file);
                };
                reader.onerror = () => resolve(file);
            });

            for (let i = 0; i < files.length; i++) {
                const pct = Math.round((i / files.length) * 100);
                overlayBar.style.width = pct + '%';
                overlayPct.textContent = pct + '%';
                overlayTitle.textContent = `Mengunggah foto ${i+1} dari ${files.length}...`;
                overlayCount.textContent = `✓ Berhasil: ${done}   ✗ Gagal: ${failed}`;

                try {
                    const compressed = await compress(files[i]);
                    const fd = new FormData();
                    fd.append('action', 'upload');
                    fd.append('ajax_batch', '1');
                    fd.append('photos[]', compressed);

                    // Kirim tanpa header tambahan (agar tidak diblok Cloudflare WAF)
                    const resp = await fetch('manage_snbp.php', { method:'POST', body: fd, credentials:'include' });
                    const txt  = await resp.text();

                    if (resp.ok && txt.trim() === 'ok') {
                        done++;
                    } else {
                        console.warn('Resp file ' + (i+1) + ':', txt.substring(0, 150));
                        failed++;
                        // Jika sesi habis, berhenti
                        if (txt.trim() === 'AUTH_FAILED') {
                            overlayTitle.textContent = 'Sesi habis — silakan login ulang.';
                            overlayBar.style.background = '#ef4444';
                            setTimeout(() => location.reload(), 2000);
                            return;
                        }
                    }
                } catch(err) {
                    console.error('Fetch error:', err);
                    failed++;
                }
            }

            // Selesai
            overlayBar.style.width = '100%';
            overlayPct.textContent = '100%';
            overlayBar.style.background = failed === 0 ? '#10b981' : '#f59e0b';
            overlayTitle.textContent = failed === 0 ? '✅ Semua foto berhasil diunggah!' : `⚠️ Selesai: ${done} berhasil, ${failed} gagal`;
            overlayDetail.textContent = 'Halaman akan dimuat ulang...';
            overlayCount.textContent  = '';
            setTimeout(() => location.reload(), 1500);
        });
        </script>

        <div class="gallery-section">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem; flex-wrap:wrap; gap:0.75rem;">
                <div>
                    <h2 style="font-size:1.2rem; margin:0;">Daftar Foto SNBP</h2>
                    <p style="font-size:0.8rem; color:#64748b; margin:0.25rem 0 0;">Geser foto untuk mengatur urutannya, lalu klik <b>Simpan Urutan</b>.</p>
                </div>
                <button id="saveOrderBtn" onclick="saveOrder()" style="display:none; padding:0.6rem 1.4rem; background:#10b981; color:white; border:none; border-radius:8px; font-weight:600; cursor:pointer; font-size:0.9rem;">
                    💾 Simpan Urutan
                </button>
            </div>

            <?php if (empty($images)): ?>
                <div style="text-align:center; padding:3rem; background:#fff; border-radius:12px; color:#64748b;">
                    Belum ada foto yang diunggah.
                </div>
            <?php else: ?>
                <div id="galleryGrid" class="gallery-grid" style="cursor:grab;">
                    <?php foreach ($images as $idx => $img): ?>
                        <div class="gallery-item" draggable="true" data-filename="<?php echo htmlspecialchars($img); ?>"
                             style="position:relative; user-select:none; transition:opacity 0.2s, transform 0.2s;">
                            <!-- Badge urutan -->
                            <div class="order-badge" style="position:absolute; top:6px; left:6px; background:#1e293b; color:#fff; font-size:0.7rem; font-weight:700; padding:2px 7px; border-radius:20px;">
                                #<?php echo $idx + 1; ?>
                            </div>
                            <!-- Drag handle -->
                            <div style="text-align:center; font-size:1.2rem; color:#94a3b8; margin-bottom:0.4rem; cursor:grab;" title="Seret untuk mengubah urutan">⠿</div>
                            <img src="/public/uploads/index_kelulusan_snbp/<?php echo htmlspecialchars($img); ?>" alt="SNBP" class="gallery-img">
                            <form action="manage_snbp.php" method="POST" onsubmit="return confirm('Hapus foto ini?');" style="margin-top:0.5rem;">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="filename" value="<?php echo htmlspecialchars($img); ?>">
                                <button type="submit" style="background:#ef4444; color:white; width:100%; border:none; border-radius:6px; padding:0.4rem; font-size:0.8rem; cursor:pointer;">Hapus</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>

                <script>
                (function() {
                    const grid = document.getElementById('galleryGrid');
                    const saveBtn = document.getElementById('saveOrderBtn');
                    let dragSrc = null;
                    let orderChanged = false;

                    grid.addEventListener('dragstart', function(e) {
                        dragSrc = e.target.closest('.gallery-item');
                        if (!dragSrc) return;
                        dragSrc.style.opacity = '0.4';
                        dragSrc.style.transform = 'scale(0.97)';
                        e.dataTransfer.effectAllowed = 'move';
                    });

                    grid.addEventListener('dragend', function(e) {
                        const item = e.target.closest('.gallery-item');
                        if (item) { item.style.opacity = '1'; item.style.transform = ''; }
                        document.querySelectorAll('.gallery-item').forEach(el => el.classList.remove('drag-over'));
                    });

                    grid.addEventListener('dragover', function(e) {
                        e.preventDefault();
                        const target = e.target.closest('.gallery-item');
                        if (!target || target === dragSrc) return;
                        document.querySelectorAll('.gallery-item').forEach(el => el.classList.remove('drag-over'));
                        target.classList.add('drag-over');
                    });

                    grid.addEventListener('drop', function(e) {
                        e.preventDefault();
                        const target = e.target.closest('.gallery-item');
                        if (!target || target === dragSrc) return;

                        // Masukkan dragSrc sebelum target
                        const allItems = [...grid.querySelectorAll('.gallery-item')];
                        const dragIdx  = allItems.indexOf(dragSrc);
                        const dropIdx  = allItems.indexOf(target);

                        if (dragIdx < dropIdx) {
                            grid.insertBefore(dragSrc, target.nextSibling);
                        } else {
                            grid.insertBefore(dragSrc, target);
                        }

                        target.classList.remove('drag-over');
                        orderChanged = true;
                        saveBtn.style.display = 'block';
                        updateBadges();
                    });

                    function updateBadges() {
                        grid.querySelectorAll('.gallery-item').forEach((el, i) => {
                            const badge = el.querySelector('.order-badge');
                            if (badge) badge.textContent = '#' + (i + 1);
                        });
                    }

                    window.saveOrder = async function() {
                        const items = [...grid.querySelectorAll('.gallery-item')];
                        const order = items.map(el => el.dataset.filename);
                        saveBtn.textContent = '⏳ Menyimpan...';
                        saveBtn.disabled = true;

                        const fd = new FormData();
                        fd.append('action', 'save_order');
                        fd.append('order', JSON.stringify(order));

                        try {
                            const resp = await fetch('manage_snbp.php', { method:'POST', body: fd, credentials:'include' });
                            const txt  = await resp.text();
                            if (resp.ok && txt.trim() === 'ok') {
                                saveBtn.textContent = '✅ Urutan Tersimpan!';
                                saveBtn.style.background = '#059669';
                                orderChanged = false;
                                setTimeout(() => {
                                    saveBtn.textContent = '💾 Simpan Urutan';
                                    saveBtn.style.background = '#10b981';
                                    saveBtn.disabled = false;
                                    saveBtn.style.display = 'none';
                                }, 2000);
                            } else {
                                saveBtn.textContent = '❌ Gagal Simpan';
                                saveBtn.style.background = '#ef4444';
                                saveBtn.disabled = false;
                            }
                        } catch(e) {
                            saveBtn.textContent = '❌ Error Koneksi';
                            saveBtn.style.background = '#ef4444';
                            saveBtn.disabled = false;
                        }
                    };
                })();
                </script>

                <style>
                    .gallery-item.drag-over { outline: 3px dashed #3b82f6; background: #eff6ff !important; }
                    .gallery-grid { user-select: none; }
                </style>
            <?php endif; ?>
        </div><!-- end page-content -->

    </main>
</div>

</body>
</html>
