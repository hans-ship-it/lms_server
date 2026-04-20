<?php
// src/osis/manage_news.php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'osis') {
    header("Location: ../../login.php");
    exit;
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    try {
        $stmt = $pdo->prepare("DELETE FROM news WHERE id = ?");
        $stmt->execute([$id]);
        $success = "Berita berhasil dihapus.";
    } catch (PDOException $e) {
        $error = "Gagal menghapus berita.";
    }
}

// Handle Create
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_news'])) {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $author_id = $_SESSION['user_id'];
    $image_path = null;

    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "../../public/uploads/news/";
        if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
        $filename = time() . "_" . basename($_FILES["image"]["name"]);
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_dir . $filename)) {
            $image_path = $filename;
        }
    }

    $stmt = $pdo->prepare("INSERT INTO news (title, content, image, author_id) VALUES (?, ?, ?, ?)");
    if ($stmt->execute([$title, $content, $image_path, $author_id])) {
        $success = "Berita berhasil diterbitkan!";
    }
}

$news_items = $pdo->query("SELECT * FROM news ORDER BY created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Berita - OSIS</title>
    <link rel="stylesheet" href="/public/assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .main-content { background: #f5f7fb !important; padding: 0 !important; }
        .page-hero {
            background: linear-gradient(135deg, #7c2d12 0%, #c2410c 50%, #ea580c 100%);
            padding: 2.5rem 3rem 5rem;
            position: relative; overflow: hidden;
        }
        .page-hero::before {
            content: ''; position: absolute;
            right: -60px; top: -60px;
            width: 250px; height: 250px;
            background: rgba(255,255,255,0.07); border-radius: 50%;
        }
        .page-hero h1 { color: #fff; font-size: 1.6rem; font-weight: 700; margin: 0 0 0.4rem; }
        .page-hero p  { color: rgba(255,255,255,0.8); margin: 0; font-size: 0.95rem; }
        .page-content {
            position: relative; margin-top: -2.5rem;
            padding: 0 3rem 3rem; z-index: 10;
        }
        .two-col { display: grid; grid-template-columns: 1fr 1.6fr; gap: 1.5rem; align-items: flex-start; }
        .db-section {
            background: #fff; border-radius: 14px;
            border: 1px solid #e8edf5; overflow: hidden;
        }
        .section-head {
            padding: 16px 22px;
            border-bottom: 1px solid #f1f5f9;
            display: flex; align-items: center; gap: 10px;
        }
        .section-head h3 { font-size: 1rem; font-weight: 700; color: #0f172a; margin: 0; }
        .section-body { padding: 20px 22px; }
        .alert-success {
            background: #dcfce7; color: #166534;
            padding: 10px 16px; border-radius: 8px;
            margin-bottom: 1rem; font-weight: 500;
            border: 1px solid #bbf7d0; font-size: 0.9rem;
        }
        .alert-error {
            background: #fee2e2; color: #991b1b;
            padding: 10px 16px; border-radius: 8px;
            margin-bottom: 1rem; font-weight: 500;
            border: 1px solid #fecaca; font-size: 0.9rem;
        }
        .form-group { margin-bottom: 1rem; }
        .form-group label {
            display: block; font-size: 0.85rem;
            font-weight: 600; color: #374151; margin-bottom: 5px;
        }
        .form-group input[type="text"],
        .form-group textarea,
        .form-group input[type="file"] {
            width: 100%; padding: 9px 12px;
            border: 1px solid #e2e8f0; border-radius: 8px;
            font-family: inherit; font-size: 0.9rem;
            background: #fff; box-sizing: border-box;
        }
        .form-group textarea { resize: vertical; min-height: 120px; }
        .btn-publish {
            width: 100%; padding: 10px;
            background: linear-gradient(135deg, #c2410c, #ea580c);
            color: #fff; border: none; border-radius: 9px;
            font-weight: 700; font-size: 0.95rem;
            cursor: pointer; font-family: inherit;
            margin-top: 0.5rem;
        }
        .btn-publish:hover { background: linear-gradient(135deg, #b03a09, #d44c08); }
        /* News list rows */
        .news-row {
            display: flex; align-items: center; gap: 14px;
            padding: 14px 22px;
            border-bottom: 1px solid #f1f5f9;
        }
        .news-row:last-child { border-bottom: none; }
        .news-thumb {
            width: 56px; height: 56px;
            border-radius: 10px; object-fit: cover;
            flex-shrink: 0; background: #f1f5f9;
            display: flex; align-items: center; justify-content: center;
        }
        .news-thumb img { width: 56px; height: 56px; border-radius: 10px; object-fit: cover; }
        .news-info { flex: 1; min-width: 0; }
        .news-title {
            font-weight: 700; font-size: 0.95rem;
            color: #0f172a; margin-bottom: 3px;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .news-date { font-size: 0.8rem; color: #94a3b8; }
        .btn-hapus {
            padding: 6px 14px; background: #fee2e2;
            color: #991b1b; border: none; border-radius: 7px;
            font-size: 0.8rem; font-weight: 600;
            cursor: pointer; font-family: inherit;
            text-decoration: none; flex-shrink: 0;
            white-space: nowrap;
        }
        .btn-hapus:hover { background: #fecaca; }
        .empty-state { text-align: center; padding: 3rem 2rem; color: #94a3b8; }
        @media (max-width: 768px) {
            .two-col { grid-template-columns: 1fr; }
            .page-content { padding: 0 1rem 2rem; }
            .page-hero { padding: 2rem 1.5rem 4.5rem; }
        }
    </style>
</head>
<body>

<div class="app-container">
    <?php include '../templates/sidebar.php'; ?>
    
    <main class="main-content">
        <div class="page-hero">
            <h1>
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:middle;margin-right:8px;"><path d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 1-2 2Zm0 0a2 2 0 0 1-2-2v-9c0-1.1.9-2 2-2h2"/><path d="M18 14h-8"/><path d="M15 18h-5"/><path d="M10 6h8v4h-8V6Z"/></svg>
                Kelola Berita Sekolah
            </h1>
            <p>OSIS – Suara Siswa SMAN 4</p>
        </div>

        <div class="page-content">
            <?php if (isset($success)): ?>
                <div class="alert-success">&#10003; <?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            <?php if (isset($error)): ?>
                <div class="alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <div class="two-col">
                <!-- Form Tulis Berita -->
                <div class="db-section">
                    <div class="section-head">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#ea580c" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                        <h3>Tulis Berita Baru</h3>
                    </div>
                    <div class="section-body">
                        <form method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="create_news" value="1">
                            <div class="form-group">
                                <label>Judul Berita</label>
                                <input type="text" name="title" required placeholder="Contoh: Class Meeting Semester Ganjil">
                            </div>
                            <div class="form-group">
                                <label>Konten</label>
                                <textarea name="content" rows="6" required placeholder="Tulis rincian kegiatan..."></textarea>
                            </div>
                            <div class="form-group">
                                <label>Gambar Kegiatan (Opsional)</label>
                                <input type="file" name="image" accept="image/*">
                            </div>
                            <button type="submit" class="btn-publish">Terbitkan Berita</button>
                        </form>
                    </div>
                </div>

                <!-- Daftar Berita -->
                <div class="db-section">
                    <div class="section-head">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#ea580c" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/></svg>
                        <h3>Daftar Berita (<?php echo count($news_items); ?>)</h3>
                    </div>
                    <?php if (empty($news_items)): ?>
                        <div class="empty-state">
                            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin-bottom:1rem;opacity:0.4;"><path d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 1-2 2Zm0 0a2 2 0 0 1-2-2v-9c0-1.1.9-2 2-2h2"/></svg>
                            <p style="font-weight:600; color:#64748b;">Belum ada berita yang diterbitkan.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($news_items as $item): ?>
                            <div class="news-row">
                                <div class="news-thumb">
                                    <?php if ($item['image']): ?>
                                        <img src="/public/uploads/news/<?php echo htmlspecialchars($item['image']); ?>" alt="thumb">
                                    <?php else: ?>
                                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 1-2 2Zm0 0a2 2 0 0 1-2-2v-9c0-1.1.9-2 2-2h2"/></svg>
                                    <?php endif; ?>
                                </div>
                                <div class="news-info">
                                    <div class="news-title"><?php echo htmlspecialchars($item['title']); ?></div>
                                    <div class="news-date"><?php echo date('d M Y, H:i', strtotime($item['created_at'])); ?></div>
                                </div>
                                <a href="manage_news.php?delete=<?php echo $item['id']; ?>" class="btn-hapus" onclick="return confirm('Yakin ingin menghapus berita ini?')">Hapus</a>
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
