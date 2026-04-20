<?php
// src/admin/manage_news.php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    try {
        $stmt = $pdo->prepare("DELETE FROM news WHERE id = ?");
        $stmt->execute([$id]);
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Berita berhasil dihapus.'];
    } catch (PDOException $e) {
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'Gagal menghapus berita.'];
    }
    header("Location: manage_news.php"); exit;
}

// Handle Edit Fetch
$edit_mode = false;
$edit_item = null;
if (isset($_GET['edit'])) {
    $edit_mode = true;
    $id = $_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM news WHERE id = ?");
    $stmt->execute([$id]);
    $edit_item = $stmt->fetch();
    if ($edit_item) {
        $stmt = $pdo->prepare("SELECT * FROM news_images WHERE news_id = ?");
        $stmt->execute([$id]);
        $edit_item['images_list'] = $stmt->fetchAll();
    }
    if (!$edit_item) { header("Location: manage_news.php"); exit; }
}

// Handle Create / Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $author_id = $_SESSION['user_id'];

    if (isset($_POST['create_news']) || isset($_POST['update_news'])) {
        if (isset($_POST['update_news'])) {
            $id = $_POST['news_id'];
            $stmt = $pdo->prepare("UPDATE news SET title=?, content=? WHERE id=?");
            $stmt->execute([$title, $content, $id]);
            $news_id = $id;
            $flash_message = 'Berita berhasil diupdate!';
        } else {
            $stmt = $pdo->prepare("INSERT INTO news (title, content, author_id) VALUES (?, ?, ?)");
            $stmt->execute([$title, $content, $author_id]);
            $news_id = $pdo->lastInsertId();
            $flash_message = 'Berita berhasil diterbitkan!';
        }

        if (isset($_FILES['images'])) {
            $target_dir = "../../public/uploads/news/";
            if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
            $files = $_FILES['images'];
            $count = count($files['name']);
            for ($i = 0; $i < $count; $i++) {
                if ($files['error'][$i] == 0) {
                    $filename = time() . "_" . $i . "_" . basename($files['name'][$i]);
                    if (move_uploaded_file($files['tmp_name'][$i], $target_dir . $filename)) {
                        $pdo->prepare("INSERT INTO news_images (news_id, image_path) VALUES (?, ?)") ->execute([$news_id, $filename]);
                        $stmt = $pdo->prepare("UPDATE news SET image = ? WHERE id = ? AND (image IS NULL OR image = '')");
                        $stmt->execute([$filename, $news_id]);
                    }
                }
            }
        }
        $_SESSION['flash'] = ['type' => 'success', 'message' => $flash_message];
        header("Location: manage_news.php"); exit;
    }
}

// Handle image deletion
if (isset($_POST['delete_image'])) {
    $image_id = $_POST['delete_image_id'];
    $news_id  = $_POST['news_id_for_image_delete'];
    $stmt = $pdo->prepare("SELECT image_path FROM news_images WHERE id = ?");
    $stmt->execute([$image_id]);
    $image_to_delete = $stmt->fetchColumn();
    if ($image_to_delete) {
        $file_path = "../../public/uploads/news/" . $image_to_delete;
        if (file_exists($file_path)) unlink($file_path);
        $pdo->prepare("DELETE FROM news_images WHERE id = ?")->execute([$image_id]);
        $stmt_check_main = $pdo->prepare("SELECT image FROM news WHERE id = ?");
        $stmt_check_main->execute([$news_id]);
        $main_image = $stmt_check_main->fetchColumn();
        if ($main_image == $image_to_delete) {
            $stmt_new_main = $pdo->prepare("SELECT image_path FROM news_images WHERE news_id = ? ORDER BY id ASC LIMIT 1");
            $stmt_new_main->execute([$news_id]);
            $new_main_image = $stmt_new_main->fetchColumn();
            $pdo->prepare("UPDATE news SET image = ? WHERE id = ?")->execute([$new_main_image, $news_id]);
        }
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Gambar berhasil dihapus.'];
        header("Location: manage_news.php?edit=" . $news_id); exit;
    } else {
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'Gagal menghapus gambar.'];
        header("Location: manage_news.php?edit=" . $news_id);
    }
}

// Fetch News with Images
$news_items = $pdo->query("
    SELECT n.*,
    (SELECT GROUP_CONCAT(image_path SEPARATOR ',') FROM news_images WHERE news_id = n.id) as images
    FROM news n
    ORDER BY created_at DESC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Berita - Admin</title>
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
        .two-col { display:grid; grid-template-columns:1fr 1.6fr; gap:1.5rem; align-items:flex-start; }
        .db-section { background:#fff; border-radius:14px; border:1px solid #e8edf5; overflow:hidden; }
        .section-head { padding:16px 22px; border-bottom:1px solid #f1f5f9; display:flex; align-items:center; gap:10px; }
        .section-head h3 { font-size:1rem; font-weight:700; color:#0f172a; margin:0; }
        .section-body { padding:20px 22px; }
        .alert-success { background:#dcfce7;color:#166534;padding:10px 16px;border-radius:8px;margin-bottom:1rem;font-weight:500;border:1px solid #bbf7d0;font-size:0.9rem; }
        .alert-error   { background:#fee2e2;color:#991b1b;padding:10px 16px;border-radius:8px;margin-bottom:1rem;font-weight:500;border:1px solid #fecaca;font-size:0.9rem; }
        .form-group { margin-bottom:1rem; }
        .form-group label { display:block; font-size:0.85rem; font-weight:600; color:#374151; margin-bottom:5px; }
        .form-group input[type="text"], .form-group textarea, .form-group input[type="file"] {
            width:100%; padding:9px 12px; border:1px solid #e2e8f0; border-radius:8px;
            font-family:inherit; font-size:0.9rem; background:#fff; box-sizing:border-box;
        }
        .form-group textarea { resize:vertical; min-height:120px; }
        .btn-publish {
            width:100%; padding:10px;
            background:linear-gradient(135deg, #312e81, #4338ca);
            color:#fff; border:none; border-radius:9px;
            font-weight:700; font-size:0.95rem;
            cursor:pointer; font-family:inherit; margin-top:0.5rem;
        }
        .btn-publish:hover { background:linear-gradient(135deg, #1e1b4b, #312e81); }
        .btn-cancel-edit {
            display:block; width:100%; text-align:center;
            padding:9px; background:#f1f5f9; color:#475569;
            border-radius:9px; font-weight:600; font-size:0.9rem;
            text-decoration:none; margin-top:0.6rem; box-sizing:border-box;
        }
        .img-thumbs { display:flex; gap:8px; flex-wrap:wrap; margin-bottom:14px; }
        .img-thumb-wrap { position:relative; width:72px; height:72px; }
        .img-thumb-wrap img { width:100%; height:100%; object-fit:cover; border-radius:8px; }
        .img-thumb-del { position:absolute; top:-5px; right:-5px; background:#ef4444; color:#fff; border:none; border-radius:50%; width:20px; height:20px; cursor:pointer; font-size:12px; display:flex; align-items:center; justify-content:center; }
        /* News list rows */
        .news-row { display:flex; align-items:center; gap:14px; padding:14px 22px; border-bottom:1px solid #f1f5f9; }
        .news-row:last-child { border-bottom:none; }
        .news-thumb { width:60px; height:50px; border-radius:8px; object-fit:cover; flex-shrink:0; background:#f1f5f9; display:flex; align-items:center; justify-content:center; }
        .news-thumb img { width:60px; height:50px; border-radius:8px; object-fit:cover; }
        .news-info { flex:1; min-width:0; }
        .news-title { font-weight:700; font-size:0.9rem; color:#0f172a; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; margin-bottom:2px; }
        .news-date { font-size:0.78rem; color:#94a3b8; }
        .news-actions { display:flex; gap:6px; flex-shrink:0; }
        .btn-edit-news { padding:6px 12px; background:#dbeafe; color:#1d4ed8; border:none; border-radius:7px; font-size:0.78rem; font-weight:600; cursor:pointer; text-decoration:none; }
        .btn-del-news  { padding:6px 12px; background:#fee2e2; color:#991b1b; border:none; border-radius:7px; font-size:0.78rem; font-weight:600; cursor:pointer; text-decoration:none; }
        .empty-state { text-align:center; padding:3rem 2rem; color:#94a3b8; }
        .img-count-badge { position:absolute; bottom:0; right:0; background:rgba(0,0,0,0.6); color:#fff; font-size:9px; padding:2px 5px; border-radius:4px; }
        @media (max-width: 768px) {
            .two-col { grid-template-columns:1fr; }
            .page-content { padding:0 1rem 2rem; }
            .page-hero { padding:2rem 1.5rem 4.5rem; }
        }
    </style>
</head>
<body>

<div class="app-container">
    <?php include '../templates/sidebar.php'; ?>
    
    <main class="main-content">
        <div class="page-hero">
            <h1>
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:middle;margin-right:8px;"><path d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 1-2 2Zm0 0a2 2 0 0 1-2-2v-9c0-1.1.9-2 2-2h2"/></svg>
                Kelola Berita
            </h1>
            <p>Bagikan informasi terbaru untuk seluruh warga sekolah.</p>
        </div>

        <div class="page-content">
            <?php if (isset($_SESSION['flash'])):
                $flash = $_SESSION['flash']; unset($_SESSION['flash']);
                $cls = $flash['type'] === 'error' ? 'alert-error' : 'alert-success';
            ?>
                <div class="<?php echo $cls; ?>"><?php echo htmlspecialchars($flash['message']); ?></div>
            <?php endif; ?>

            <div class="two-col">
                <!-- Form -->
                <div class="db-section">
                    <div class="section-head">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#4338ca" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                        <h3><?php echo $edit_mode ? 'Edit Berita' : 'Tulis Berita Baru'; ?></h3>
                    </div>
                    <div class="section-body">
                        <?php if ($edit_mode && !empty($edit_item['images_list'])): ?>
                            <div style="font-size:0.82rem;font-weight:600;color:#374151;margin-bottom:6px;">Gambar Saat Ini:</div>
                            <div class="img-thumbs">
                                <?php foreach ($edit_item['images_list'] as $img): ?>
                                    <div class="img-thumb-wrap">
                                        <img src="/public/uploads/news/<?php echo htmlspecialchars($img['image_path']); ?>">
                                        <form method="POST" style="margin:0;" onsubmit="return confirm('Hapus gambar ini?');">
                                            <input type="hidden" name="delete_image" value="1">
                                            <input type="hidden" name="delete_image_id" value="<?php echo $img['id']; ?>">
                                            <input type="hidden" name="news_id_for_image_delete" value="<?php echo $edit_item['id']; ?>">
                                            <button type="submit" class="img-thumb-del">&times;</button>
                                        </form>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" enctype="multipart/form-data">
                            <?php if ($edit_mode): ?>
                                <input type="hidden" name="update_news" value="1">
                                <input type="hidden" name="news_id" value="<?php echo $edit_item['id']; ?>">
                            <?php else: ?>
                                <input type="hidden" name="create_news" value="1">
                            <?php endif; ?>
                            <div class="form-group">
                                <label>Judul Berita</label>
                                <input type="text" name="title" required placeholder="Contoh: Juara 1 Lomba Matematika" value="<?php echo $edit_mode ? htmlspecialchars($edit_item['title']) : ''; ?>">
                            </div>
                            <div class="form-group">
                                <label>Konten</label>
                                <textarea name="content" rows="6" required placeholder="Tulis isi berita di sini..."><?php echo $edit_mode ? htmlspecialchars($edit_item['content']) : ''; ?></textarea>
                            </div>
                            <div class="form-group">
                                <label><?php echo $edit_mode ? 'Tambah Gambar Lagi (Opsional)' : 'Upload Gambar (Bisa lebih dari satu)'; ?></label>
                                <input type="file" name="images[]" multiple accept="image/*">
                                <small style="color:#94a3b8; display:block; margin-top:4px; font-size:0.78rem;">Tekan Ctrl untuk memilih banyak gambar sekaligus.</small>
                            </div>
                            <button type="submit" class="btn-publish"><?php echo $edit_mode ? 'Simpan Perubahan' : 'Terbitkan Berita'; ?></button>
                            <?php if ($edit_mode): ?>
                                <a href="manage_news.php" class="btn-cancel-edit">Batal Edit</a>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>

                <!-- Daftar Berita -->
                <div class="db-section">
                    <div class="section-head">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#4338ca" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/></svg>
                        <h3>Daftar Berita (<?php echo count($news_items); ?>)</h3>
                    </div>
                    <?php if (empty($news_items)): ?>
                        <div class="empty-state">
                            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin-bottom:1rem;opacity:0.4;"><path d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 1-2 2Zm0 0a2 2 0 0 1-2-2v-9c0-1.1.9-2 2-2h2"/></svg>
                            <p style="font-weight:600; color:#64748b;">Belum ada berita yang diterbitkan.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($news_items as $item): ?>
                            <?php
                                $imgs = $item['images'] ? explode(',', $item['images']) : [];
                                $main_img = $imgs[0] ?? null;
                                $img_count = count($imgs);
                            ?>
                            <div class="news-row">
                                <div class="news-thumb" style="position:relative;">
                                    <?php if ($main_img): ?>
                                        <img src="/public/uploads/news/<?php echo htmlspecialchars($main_img); ?>" alt="">
                                        <?php if ($img_count > 1): ?>
                                            <span class="img-count-badge">+<?php echo $img_count - 1; ?></span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5"><path d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 1-2 2Zm0 0a2 2 0 0 1-2-2v-9c0-1.1.9-2 2-2h2"/></svg>
                                    <?php endif; ?>
                                </div>
                                <div class="news-info">
                                    <div class="news-title"><?php echo htmlspecialchars($item['title']); ?></div>
                                    <div class="news-date"><?php echo date('d M Y, H:i', strtotime($item['created_at'])); ?></div>
                                </div>
                                <div class="news-actions">
                                    <a href="manage_news.php?edit=<?php echo $item['id']; ?>" class="btn-edit-news">Edit</a>
                                    <a href="manage_news.php?delete=<?php echo $item['id']; ?>" class="btn-del-news" onclick="return confirm('Yakin hapus berita ini?')">Hapus</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
</div>

</body>
</html>
