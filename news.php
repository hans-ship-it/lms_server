<?php
// news.php
require_once 'config/database.php';

// Fetch All News
try {
    $stmt = $pdo->query("SELECT news.*, users.full_name as author_name FROM news JOIN users ON news.author_id = users.id ORDER BY created_at DESC");
    $news_items = $stmt->fetchAll();
}
catch (PDOException $e) {
    $news_items = [];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Arsip Berita - SMA Negeri 4 Makassar</title>
    <link rel="stylesheet" href="/public/assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Merriweather:ital,wght@0,300;0,400;0,700;1,300&display=swap" rel="stylesheet">
    <style>
        body { background-color: #f8fafc; }

        /* ========== NEWSPAPER HEADER ========== */
        .newspaper-header {
            text-align: center;
            padding: 3rem 1.5rem 2rem;
            background: white;
            border-bottom: double 4px var(--primary);
            margin-bottom: 2.5rem;
        }
        .newspaper-title {
            font-family: 'Merriweather', serif;
            font-size: clamp(2rem, 7vw, 3.5rem);
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: -1px;
            color: var(--secondary);
            margin-bottom: 0.5rem;
        }
        .newspaper-date {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-style: italic;
            color: var(--text-muted);
            font-size: 1rem;
            border-top: 1px solid #e2e8f0;
            border-bottom: 1px solid #e2e8f0;
            display: inline-block;
            padding: 0.5rem 2rem;
            margin-top: 1rem;
        }

        /* ========== NEWS GRID ========== */
        .news-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 5% 4rem;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 2rem;
        }
        .news-article {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            position: relative;
            display: flex;
            flex-direction: column;
        }
        .news-article::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 3px;
            background: var(--primary);
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }
        .news-article:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }
        .news-article:hover::after { transform: scaleX(1); }

        .article-img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            filter: sepia(20%);
            transition: filter 0.3s ease;
            display: block;
        }
        .article-img-placeholder {
            width: 100%;
            height: 200px;
            background: linear-gradient(135deg, #f1f5f9, #e2e8f0);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            color: #94a3b8;
        }
        .news-article:hover .article-img { filter: sepia(0%); }

        .article-body { padding: 1.5rem; flex: 1; display: flex; flex-direction: column; }

        .article-meta {
            font-size: 0.8rem;
            color: #64748b;
            margin-bottom: 0.75rem;
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.5px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .article-meta::before {
            content: '';
            display: inline-block;
            width: 8px; height: 8px;
            border-radius: 50%;
            background: var(--primary);
            flex-shrink: 0;
        }
        .article-headline {
            font-family: 'Merriweather', serif;
            font-size: 1.2rem;
            font-weight: 700;
            line-height: 1.4;
            margin-bottom: 0.75rem;
            color: #1e293b;
        }
        .article-excerpt {
            font-size: 0.95rem;
            line-height: 1.7;
            color: #475569;
            margin-bottom: 1.5rem;
            flex: 1;
        }
        .read-more {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #1e293b;
            color: white;
            padding: 0.6rem 1.5rem;
            border-radius: 50px;
            font-weight: 600;
            text-decoration: none;
            font-size: 0.875rem;
            transition: background 0.2s;
            align-self: flex-start;
        }
        .read-more:hover { background: var(--primary); }

        /* ========== EMPTY STATE ========== */
        .empty-state {
            grid-column: 1 / -1;
            text-align: center;
            padding: 5rem 1.5rem;
            color: #64748b;
        }
        .empty-state .empty-icon { font-size: 4rem; margin-bottom: 1rem; }

        /* ========== FOOTER ========== */
        .news-footer {
            background: var(--bg-sidebar);
            color: #94a3b8;
            padding: 3rem 5%;
        }
        .footer-inner {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1.5rem;
        }
        .footer-links { display: flex; gap: 1.5rem; }
        .footer-links a { color: white; text-decoration: none; font-size: 0.9rem; }

        /* ========== MOBILE RESPONSIVE ========== */
        @media (max-width: 768px) {
            .news-container {
                grid-template-columns: 1fr;
                padding: 0 1rem 3rem;
            }
            .newspaper-header { padding: 2rem 1rem 1.5rem; }
            .news-footer { padding: 2.5rem 1.5rem; }
            .footer-inner { flex-direction: column; align-items: flex-start; }
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

<!-- Mobile Overlay -->
<div class="mobile-overlay" id="newsOverlay" onclick="closeNewsNav()"></div>

<!-- Nav -->
<nav class="news-nav">
    <a href="index.php" class="news-nav-logo">SMA Negeri 4 Makassar</a>
    <button class="news-hamburger" id="newsHamburger" onclick="toggleNewsNav()" aria-label="Menu">
        <span></span><span></span><span></span>
    </button>
    <div class="news-nav-links">
        <a href="index.php">Beranda</a>     
        <a href="news.php" class="active">Berita</a>
        <a href="login.php" class="nav-btn">Login Portal</a>
    </div>
</nav>

<!-- Mobile Drawer -->
<div class="news-mobile-drawer" id="newsDrawer">
    <div class="drawer-header">
        <span style="font-weight: 800; color: white; font-size: 0.9rem;">Menu Navigasi</span>
        <button onclick="closeNewsNav()" style="background: rgba(255,255,255,0.1); border: none; color: white; cursor: pointer; font-size: 1.2rem; width: 32px; height: 32px; border-radius: 8px;">&times;</button>
    </div>
    <a href="index.php" class="mobile-link">Beranda</a>
    <a href="news.php" class="mobile-link active">Berita</a>
    <a href="login.php" class="mobile-link cta">Masuk Portal &rarr;</a>
</div>

<!-- Newspaper Header -->
<header class="newspaper-header">
    <h1 class="newspaper-title">Suara Sekolah</h1>
    <div class="newspaper-date">Arsip Berita &amp; Kegiatan Siswa &mdash; <?php echo date('d F Y'); ?></div>
</header>

<!-- News Grid -->
<main class="news-container">
    <?php if (empty($news_items)): ?>
        <div class="empty-state">
            <div class="empty-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/></svg>
            </div>
            <h3 style="color: #1e293b; font-size: 1.4rem; margin-bottom: 0.5rem;">Belum ada berita</h3>
            <p>Belum ada artikel yang diterbitkan saat ini.</p>
        </div>
    <?php endif; ?>

    <?php foreach ($news_items as $news): ?>
    <article class="news-article">
        <?php if ($news['image']): ?>
            <img src="public/uploads/news/<?php echo htmlspecialchars($news['image']); ?>" class="article-img" alt="<?php echo htmlspecialchars($news['title']); ?>">
        <?php else: ?>
            <div class="article-img-placeholder">
                <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/></svg>
            </div>
        <?php endif; ?>

        <div class="article-body">
            <div class="article-meta">
                <?php echo date('d M Y', strtotime($news['created_at'])); ?> &bull; <?php echo htmlspecialchars($news['author_name']); ?>
            </div>
            <h2 class="article-headline"><?php echo htmlspecialchars($news['title']); ?></h2>
            <div class="article-excerpt">
                <?php echo substr(strip_tags($news['content']), 0, 150); ?>...
            </div>
            <a href="news_detail.php?id=<?php echo $news['id']; ?>" class="read-more">
                Baca Lengkap &rarr;
            </a>
        </div>
    </article>
    <?php endforeach; ?>
</main>

<!-- Footer -->
<footer class="news-footer">
    <div class="footer-inner">
        <div>
            <h3 style="color: white; font-size: 1.25rem; font-weight: 800; margin-bottom: 0.25rem;">SMA Negeri 4 Makassar</h3>
            <p style="font-size: 0.875rem;">&copy; <?php echo date('Y'); ?> SMA Negeri 4 Makassar</p>
        </div>
        <div class="footer-links">
            <a href="#">Instagram</a>
            <a href="#">Youtube</a>
        </div>
    </div>
</footer>

<script>
function toggleNewsNav() {
    const drawer = document.getElementById('newsDrawer');
    const overlay = document.getElementById('newsOverlay');
    if (drawer.classList.contains('open')) {
        closeNewsNav();
    } else {
        drawer.classList.add('open');
        overlay.style.display = 'block';
        document.body.style.overflow = 'hidden';
    }
}
function closeNewsNav() {
    document.getElementById('newsDrawer').classList.remove('open');
    document.getElementById('newsOverlay').style.display = 'none';
    document.body.style.overflow = '';
}
</script>
</body>
</html>

