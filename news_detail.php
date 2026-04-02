<?php
// news_detail.php
require_once 'config/database.php';

$id = $_GET['id'] ?? 0;

if (!$id) {
    header("Location: index.php");
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT news.*, users.full_name as author_name 
        FROM news 
        JOIN users ON news.author_id = users.id 
        WHERE news.id = ?
    ");
    $stmt->execute([$id]);
    $news = $stmt->fetch();

    if (!$news) {
        die("Berita tidak ditemukan.");
    }
}
catch (PDOException $e) {
    die("Terjadi kesalahan: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($news['title']); ?> - SMAN 4 Makassar</title>
    <link rel="stylesheet" href="/public/assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&family=Merriweather:wght@300;400;700&display=swap" rel="stylesheet">
    <style>
        body { background-color: #f8fafc; }
        .detail-container { max-width: 900px; margin: 4rem auto; padding: 0 20px; }
        .back-link { display: inline-block; margin-bottom: 2rem; color: #64748b; text-decoration: none; font-weight: 500; transition: color 0.2s; }
        .back-link:hover { color: var(--primary-color); }
        
        .article-card { background: white; border-radius: 20px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.05), 0 8px 10px -6px rgba(0,0,0,0.01); overflow: hidden; }
        .article-header { padding: 3rem 3rem 2rem; }
        .article-title { font-size: 2.5rem; font-weight: 800; color: #1e293b; line-height: 1.2; margin-bottom: 1.5rem; letter-spacing: -0.5px; }
        .article-meta { display: flex; align-items: center; gap: 1rem; color: #64748b; font-size: 0.95rem; }
        .meta-divider { width: 4px; height: 4px; background: #cbd5e1; border-radius: 50%; }
        
        .article-image-container { width: 100%; height: 500px; position: relative; }
        .article-image { width: 100%; height: 100%; object-fit: cover; }
        
        .article-content { padding: 3rem; font-family: 'Merriweather', serif; font-size: 1.1rem; line-height: 1.8; color: #334155; }
        .article-content p { margin-bottom: 1.5rem; }
        
        @media (max-width: 768px) {
            .article-header { padding: 2rem; }
            .article-title { font-size: 1.8rem; }
            .article-image-container { height: 300px; }
            .article-content { padding: 2rem; }
        }
               /* ========== MOBILE NAV ========== */
        .news-nav {
            position: sticky;
            top: 0;
            background: white;
            z-index: 100;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            padding: 0 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 64px;
        }
        .news-nav-logo {
            font-weight: 800;
            font-size: 1rem;
            color: var(--secondary);
            text-decoration: none;
        }
        .news-nav-links {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }
        .news-nav-links a {
            color: var(--secondary);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.95rem;
        }
        .news-nav-links a.active { color: var(--primary); }
        .news-nav-links .nav-btn {
            background: var(--primary);
            color: white;
            padding: 8px 18px;
            border-radius: 50px;
            font-weight: 700;
            font-size: 0.875rem;
        }
        .news-hamburger {
            display: none;
            flex-direction: column;
            gap: 5px;
            cursor: pointer;
            background: none;
            border: none;
            padding: 6px;
        }
        .news-hamburger span {
            display: block;
            width: 22px;
            height: 2px;
            background: var(--secondary);
            border-radius: 2px;
            transition: all 0.3s;
        }
        .news-mobile-drawer {
            position: fixed;
            top: 0; right: -300px;
            width: 280px;
            height: 100vh;
            background: var(--bg-sidebar);
            z-index: 200;
            padding: 1.5rem;
            transition: right 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        .news-mobile-drawer.open { right: 0; }
        .news-mobile-drawer .drawer-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        .news-mobile-drawer .mobile-link {
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            padding: 12px 16px;
            border-radius: 10px;
            font-weight: 500;
            transition: all 0.2s;
        }
        .news-mobile-drawer .mobile-link:hover,
        .news-mobile-drawer .mobile-link.active {
            background: rgba(255,255,255,0.1);
            color: white;
        }
        .news-mobile-drawer .mobile-link.cta {
            background: var(--primary);
            color: white;
            font-weight: 700;
            margin-top: 0.5rem;
            text-align: center;
        }
        .mobile-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.5);
            z-index: 190;
            backdrop-filter: blur(3px);
        }

        @media (max-width: 768px) {
            .news-nav-links { display: none; }
            .news-hamburger { display: flex; }
        }
    </style>
</head>
<body>

<nav class="news-nav">
    <a href="index.php" class="news-nav-logo">SMA Negeri 4 Makassar</a>
    <div class="news-nav-links">
        <a href="index.php">Beranda</a>     
        <a href="news.php" class="active">Berita</a>
        <a href="login.php" class="nav-btn">Login Portal</a>
    </div>
    <button class="news-hamburger" id="newsHamburger" onclick="toggleNewsNav()" aria-label="Menu">
        <span></span><span></span><span></span>
    </button>
</nav>

<!-- Mobile Overlay -->
<div class="mobile-overlay" id="newsOverlay" onclick="toggleNewsNav()"></div>

<!-- Mobile Drawer -->
<div class="news-mobile-drawer" id="newsDrawer">
    <div class="drawer-header">
        <span style="color:white; font-weight:800; font-size:0.95rem;">SMA Negeri 4 Makassar</span>
        <button onclick="toggleNewsNav()" style="background:none;border:none;cursor:pointer;color:rgba(255,255,255,0.7);font-size:1.4rem;line-height:1;">&times;</button>
    </div>
    <a href="index.php" class="mobile-link">Beranda</a>
    <a href="news.php" class="mobile-link active">Berita</a>
    <a href="login.php" class="mobile-link cta">Login Portal</a>
</div>

    <div class="detail-container">
        <a href="news.php" class="back-link">&larr; Kembali ke Suara Sekolah</a>
        
        <article class="article-card">
            <div class="article-header">
                <h1 class="article-title"><?php echo htmlspecialchars($news['title']); ?></h1>
                <div class="article-meta">
                    <span><?php echo date('d F Y', strtotime($news['created_at'])); ?></span>
                    <span class="meta-divider"></span>
                    <span>Penulis: <strong><?php echo htmlspecialchars($news['author_name']); ?></strong></span>
                </div>
            </div>
            
            <?php if ($news['image']): ?>
                <div class="article-image-container">
                    <img src="public/uploads/news/<?php echo htmlspecialchars($news['image']); ?>" class="article-image" alt="<?php echo htmlspecialchars($news['title']); ?>">
                </div>
            <?php
endif; ?>
            
            <div class="article-content">
                <?php echo nl2br(htmlspecialchars($news['content'])); ?>
            </div>
        </article>
    </div>

    <footer style="text-align: center; padding: 2rem; color: #94a3b8;">
        &copy; <?php echo date('Y'); ?> SMA Negeri 4 Makassar
    </footer>

<script>
function toggleNewsNav() {
    const drawer  = document.getElementById('newsDrawer');
    const overlay = document.getElementById('newsOverlay');
    const hamburger = document.getElementById('newsHamburger');
    const isOpen = drawer.classList.toggle('open');
    overlay.style.display = isOpen ? 'block' : 'none';
    hamburger.setAttribute('aria-expanded', isOpen);
    document.body.style.overflow = isOpen ? 'hidden' : '';
}
</script>
</body>
</html>

