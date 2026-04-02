<?php
// src/templates/sidebar.php
$role = $_SESSION['role'] ?? 'guest';
// We use basename to highlight active links, but we need robust checking
$current_page = basename($_SERVER['PHP_SELF']);
$base_url = ""; // Hardcoded base URL for reliability
if ($role !== 'guest') {
    echo '<script>document.documentElement.setAttribute("data-lms-role",' . json_encode($role, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) . ');</script>' . "\n";
}
?>

<!-- Mobile Toggle Button (Visible only on mobile via CSS) -->
<button id="sidebarToggle" class="mobile-toggle-btn" aria-label="Toggle Menu">
    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
</button>

<!-- Sidebar Overlay -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<aside class="sidebar" id="sidebar">
    <div class="sidebar-header" style="position: relative; padding-right: 40px;">
        <div style="display: flex; flex-direction: column; align-items: flex-start;">
            <span style="font-size: 1.1rem; line-height: 1.2;">SMA Negeri 4</span>
            <span style="font-size: 0.85rem; color: #94a3b8; font-weight: 500;">Makassar</span>
        </div>
        
        <button id="sidebarClose" class="mobile-close-btn" aria-label="Close Sidebar">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
    </div>
    <nav class="sidebar-nav">
        <!-- Common Links -->
        <!-- Note: We point to specific dashboard paths per role to be safe -->
        <?php
$dashboard_link = "#";
if ($role == 'admin')
    $dashboard_link = "$base_url/src/admin/dashboard.php";
if ($role == 'guru')
    $dashboard_link = "$base_url/src/guru/dashboard.php";
if ($role == 'siswa')
    $dashboard_link = "$base_url/src/siswa/dashboard.php";
if ($role == 'osis')
    $dashboard_link = "$base_url/src/osis/dashboard.php";
if ($role == 'kepsek' || $role == 'wakasek')
    $dashboard_link = "$base_url/src/pimpinan/dashboard.php";
if ($role == 'bk')
    $dashboard_link = "$base_url/src/bk/dashboard.php";
?>
        
        <a href="<?php echo $dashboard_link; ?>" class="nav-link <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 10px;"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg> Dashboard
        </a>
        
        <!-- Role Specific Links -->
        <?php if ($role == 'admin'): ?>
            <a href="<?php echo $base_url; ?>/src/admin/manage_users.php" class="nav-link <?php echo $current_page == 'manage_users.php' ? 'active' : ''; ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 10px;"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg> Kelola Pengguna
            </a>
            <a href="<?php echo $base_url; ?>/src/admin/manage_news.php" class="nav-link <?php echo $current_page == 'manage_news.php' ? 'active' : ''; ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 10px;"><path d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 1-2 2Zm0 0a2 2 0 0 1-2-2v-9c0-1.1.9-2 2-2h2"/><path d="M18 14h-8"/><path d="M15 18h-5"/><path d="M10 6h8v4h-8V6Z"/></svg> Kelola Berita
            </a>
            <a href="<?php echo $base_url; ?>/src/admin/manage_master_classes.php" class="nav-link <?php echo $current_page == 'manage_master_classes.php' ? 'active' : ''; ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 10px;"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/></svg> Kelola Master Kelas
            </a>
            <a href="<?php echo $base_url; ?>/src/admin/manage_classes.php" class="nav-link <?php echo $current_page == 'manage_classes.php' ? 'active' : ''; ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 10px;"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg> Kelola Kelas Guru
            </a>
            <a href="<?php echo $base_url; ?>/src/admin/manage_schedules.php" class="nav-link <?php echo $current_page == 'manage_schedules.php' ? 'active' : ''; ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 10px;"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg> Kelola Jadwal
            </a>
            <a href="<?php echo $base_url; ?>/src/admin/manage_backups.php" class="nav-link <?php echo $current_page == 'manage_backups.php' ? 'active' : ''; ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 10px;"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/></svg> Backup Arsip Data
            </a>
            <a href="<?php echo $base_url; ?>/src/admin/promote_class.php" class="nav-link <?php echo $current_page == 'promote_class.php' ? 'active' : ''; ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 10px;"><path d="m16 13 5.223 3.482a.5.5 0 0 0 .777-.416V7.934a.5.5 0 0 0-.777-.416L16 11"/><path d="M12 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h6"/><path d="M12 18V6a2 2 0 0 1 2-2h1a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2z"/></svg> Promosi Kelas
            </a>
        <?php
endif; ?>

        <?php if ($role == 'guru'): ?>
            <a href="<?php echo $base_url; ?>/src/guru/kelas.php" class="nav-link <?php echo $current_page == 'kelas.php' || $current_page == 'view_class.php' || (isset($active_tab) && $active_tab == 'kelas') ? 'active' : ''; ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 10px;"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg> Kelas & Materi
            </a>
            <a href="<?php echo $base_url; ?>/src/guru/jadwal_mengajar.php" class="nav-link <?php echo $current_page == 'jadwal_mengajar.php' ? 'active' : ''; ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 10px;"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg> Jadwal Mengajar
            </a>
            <a href="<?php echo $base_url; ?>/src/guru/grades.php" class="nav-link <?php echo in_array($current_page, ['grades.php', 'grades_input.php', 'grades_import.php']) ? 'active' : ''; ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 10px;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg> Input Nilai Siswa
            </a>
        <?php endif; ?>

        <?php if ($role == 'siswa'): ?>
            <a href="<?php echo $base_url; ?>/src/siswa/materials.php" class="nav-link <?php echo $current_page == 'materials.php' ? 'active' : ''; ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 10px;"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg> Materi
            </a>
            <a href="<?php echo $base_url; ?>/src/siswa/assignments.php" class="nav-link <?php echo $current_page == 'assignments.php' ? 'active' : ''; ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 10px;"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg> Tugas Saya
            </a>
            <a href="<?php echo $base_url; ?>/src/siswa/jadwal.php" class="nav-link <?php echo $current_page == 'jadwal.php' ? 'active' : ''; ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 10px;"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg> Jadwal Pelajaran
            </a>
        <?php
endif; ?>
        
        <?php if ($role == 'osis'): ?>
            <a href="<?php echo $base_url; ?>/src/osis/manage_news.php" class="nav-link <?php echo $current_page == 'manage_news.php' ? 'active' : ''; ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 10px;"><path d="m12 15-3-3a22 22 0 0 1 2-3.95A12.88 12.88 0 0 1 22 2c0 2.72-.78 7.5-6 11a22.35 22.35 0 0 1-4 2z"/><path d="M9 12H4s.55-3.03 2-4c1.62-1.08 5 0 5 0"/><path d="M12 15v5s3.03-.55 4-2c1.08-1.62 0-5 0-5"/></svg> Berita Sekolah
            </a>
        <?php
endif; ?>

        <?php if ($role == 'kepsek' || $role == 'wakasek'): ?>
            <a href="<?php echo $base_url; ?>/src/pimpinan/guru_list.php" class="nav-link <?php echo $current_page == 'guru_list.php' ? 'active' : ''; ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 10px;"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg> Data Guru
            </a>
            <a href="<?php echo $base_url; ?>/src/pimpinan/siswa_list.php" class="nav-link <?php echo $current_page == 'siswa_list.php' ? 'active' : ''; ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 10px;"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg> Data Siswa
            </a>
            <a href="<?php echo $base_url; ?>/src/pimpinan/jadwal_sekolah.php" class="nav-link <?php echo $current_page == 'jadwal_sekolah.php' ? 'active' : ''; ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 10px;"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg> Jadwal Mengajar
            </a>
            <a href="<?php echo $base_url; ?>/src/pimpinan/pemantauan_meet.php" class="nav-link <?php echo $current_page == 'pemantauan_meet.php' ? 'active' : ''; ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 10px;"><path d="M23 7l-7 5 7 5V7z"/><rect x="1" y="5" width="15" height="14" rx="2" ry="2"/></svg> Pemantauan Meet
            </a>

        <?php
endif; ?>

        <?php if ($role == 'bk'): ?>
            <a href="<?php echo $base_url; ?>/src/bk/pengaduan.php" class="nav-link <?php echo $current_page == 'pengaduan.php' ? 'active' : ''; ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 10px;"><path d="M14 9a2 2 0 0 1-2 2H6l-4 4V4c0-1.1.9-2 2-2h8a2 2 0 0 1 2 2v5Z"/><path d="M18 9h2a2 2 0 0 1 2 2v11l-4-4h-6a2 2 0 0 1-2-2v-1"/></svg> Kelola E-Counseling
            </a>
        <?php endif; ?>

        <div style="margin: 1.5rem 0; border-top: 1px solid rgba(255,255,255,0.1);"></div>
        
        <a href="<?php echo $base_url; ?>/src/profile.php" class="nav-link <?php echo $current_page == 'profile.php' ? 'active' : ''; ?>">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 10px;"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg> Profil Saya
        </a>
        <a href="<?php echo $base_url; ?>/src/auth/logout.php" class="nav-link" style="color: #ef4444;" onclick="return confirm('Apakah Anda yakin ingin logout?');">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 10px;"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg> Logout
        </a>
    </nav>
</aside>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const toggle = document.getElementById('sidebarToggle');
    const close = document.getElementById('sidebarClose');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    const mainContent = document.querySelector('.main-content');

    // restore state
    const isCollapsed = localStorage.getItem('sidebar-collapsed') === 'true';
    if (isCollapsed && window.innerWidth > 768) {
        sidebar.classList.add('collapsed');
        if(mainContent) mainContent.classList.add('expanded');
        if(toggle) toggle.classList.add('sidebar-hidden');
    }

    function toggleSidebar() {
        if (window.innerWidth <= 768) {
            // Mobile: Toggle active/overlay
            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
        } else {
            // Desktop: Toggle collapse
            sidebar.classList.toggle('collapsed');
            if(mainContent) mainContent.classList.toggle('expanded');
            if(toggle) toggle.classList.toggle('sidebar-hidden');
            
            // Save state
            const collapsed = sidebar.classList.contains('collapsed');
            localStorage.setItem('sidebar-collapsed', collapsed);
        }
    }

    if(toggle) toggle.addEventListener('click', toggleSidebar);
    if(close) close.addEventListener('click', toggleSidebar);
    
    if(overlay) overlay.addEventListener('click', () => {
        sidebar.classList.remove('active');
        overlay.classList.remove('active');
    });
});
</script>
