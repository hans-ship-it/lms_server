<?php
// src/guru/grades.php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'guru') {
    header("Location: ../../login.php");
    exit;
}

$teacher_id = $_SESSION['user_id'];

// Fetch Teacher's Classes with Grade Level
$stmt = $pdo->prepare("
    SELECT tc.*, c.name as school_class_name, 
    COALESCE(c.grade_level, tc.special_grade_level) as computed_grade_level,
    (SELECT COUNT(*) FROM class_members cm WHERE cm.teacher_class_id = tc.id) as special_student_count,
    (SELECT COUNT(*) FROM users u WHERE u.class_id = tc.class_id AND u.role = 'siswa') as regular_student_count
    FROM teacher_classes tc
    LEFT JOIN classes c ON tc.class_id = c.id
    WHERE tc.teacher_id = ?
    ORDER BY COALESCE(c.grade_level, tc.special_grade_level, 99) ASC, tc.created_at DESC
");
$stmt->execute([$teacher_id]);
$all_my_classes = $stmt->fetchAll();

// Group by Grade Level
$grouped_classes = [
    '10' => [],
    '11' => [],
    '12' => [],
    'Others' => [] // For any class without strict 10/11/12
];

foreach ($all_my_classes as $class) {
    if (in_array($class['computed_grade_level'], ['10', '11', '12'])) {
        $grouped_classes[$class['computed_grade_level']][] = $class;
    }
    else {
        $grouped_classes['Others'][] = $class;
    }
}

// Helper for Subject Colors
function getSubjectStyle($subjectName)
{
    $gradients = [
        'blue' => 'linear-gradient(135deg, #3b82f6 0%, #2563eb 100%)',
        'indigo' => 'linear-gradient(135deg, #6366f1 0%, #4f46e5 100%)',
        'purple' => 'linear-gradient(135deg, #a855f7 0%, #9333ea 100%)',
        'pink' => 'linear-gradient(135deg, #ec4899 0%, #db2777 100%)',
        'orange' => 'linear-gradient(135deg, #f97316 0%, #ea580c 100%)',
        'teal' => 'linear-gradient(135deg, #14b8a6 0%, #0d9488 100%)',
        'green' => 'linear-gradient(135deg, #22c55e 0%, #16a34a 100%)',
        'red' => 'linear-gradient(135deg, #ef4444 0%, #dc2626 100%)',
        'cyan' => 'linear-gradient(135deg, #06b6d4 0%, #0891b2 100%)',
    ];

    $subjectLower = strtolower($subjectName);
    if (strpos($subjectLower, 'matematika') !== false) return $gradients['blue'];
    if (strpos($subjectLower, 'biologi') !== false) return $gradients['green'];
    if (strpos($subjectLower, 'fisika') !== false) return $gradients['indigo'];
    if (strpos($subjectLower, 'kimia') !== false) return $gradients['purple'];
    if (strpos($subjectLower, 'sejarah') !== false) return $gradients['orange'];
    if (strpos($subjectLower, 'bahasa') !== false) return $gradients['teal'];
    if (strpos($subjectLower, 'inggris') !== false) return $gradients['pink'];
    if (strpos($subjectLower, 'ekonomi') !== false) return $gradients['cyan'];
    if (strpos($subjectLower, 'geografi') !== false) return $gradients['teal'];
    if (strpos($subjectLower, 'sosiologi') !== false) return $gradients['orange'];
    if (strpos($subjectLower, 'pk') !== false) return $gradients['red'];

    $keys = array_keys($gradients);
    $hash = crc32($subjectName);
    $index = abs($hash) % count($keys);
    return $gradients[$keys[$index]];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Input Nilai Siswa</title>
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
        .search-bar { display:flex; align-items:center; gap:10px; padding:14px 18px; background:#fafbfd; border-bottom:1px solid #f1f5f9; }
        .search-bar input { flex:1; padding:8px 14px; border:1px solid #e2e8f0; border-radius:8px; font-family:inherit; font-size:0.9rem; outline:none; }
        .search-bar input:focus { border-color:#6366f1; }
        .db-section { background:#fff; border:1px solid #e8edf5; border-radius:14px; overflow:hidden; margin-bottom:1.5rem; }
        .grade-header { padding:12px 18px; background:#f8fafc; border-bottom:1px solid #f1f5f9; display:flex; align-items:center; gap:10px; }
        .grade-header .grade-badge { background:#f59e0b; color:#fff; padding:2px 10px; border-radius:6px; font-size:0.78rem; font-weight:700; }
        .grade-header span { font-size:0.95rem; font-weight:700; color:#1e293b; }
        .class-row { display:flex; align-items:center; justify-content:space-between; padding:14px 18px; border-bottom:1px solid #f8f9fc; transition:background 0.15s; text-decoration:none; }
        .class-row:last-child { border-bottom:none; }
        .class-row:hover { background:#f8faff; }
        .class-row-left { display:flex; align-items:center; gap:14px; }
        .class-icon { width:40px; height:40px; border-radius:10px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
        .class-name { font-weight:700; color:#0f172a; font-size:0.95rem; }
        .class-meta { font-size:0.82rem; color:#64748b; margin-top:2px; }
        .subject-chip { display:inline-block; background:#eef2ff; color:#4338ca; padding:2px 10px; border-radius:20px; font-size:0.78rem; font-weight:600; }
        .class-row-right { display:flex; align-items:center; gap:8px; }
        .student-count { font-size:0.82rem; color:#64748b; }
        @media (max-width:768px) { .page-content { padding:0 1rem 2rem; } }
    </style>
</head>
<body class="admin-full-layout">

<div class="app-container">
    <?php include '../templates/sidebar.php'; ?>
    
    <main class="main-content">
        <div class="page-hero">
            <h1>
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:middle;margin-right:8px;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                Pilih Kelas untuk Penilaian
            </h1>
            <p>Silakan pilih kelas yang ingin Anda berikan nilainya. Data tersimpan secara real-time.</p>
        </div>

        <div class="page-content">

        <?php if (empty($all_my_classes)): ?>
            <div class="db-section" style="text-align:center; padding:4rem; border-style:dashed; color:#94a3b8;">
                <h3 style="color:#1e293b; margin-bottom:.5rem;">Belum ada kelas aktif</h3>
                <p>Buat kelas terlebih dahulu di menu "Kelas &amp; Materi" sebelum menginput nilai.</p>
                <a href="kelas.php" class="btn" style="display:inline-block; margin-top:1rem; text-decoration:none;">Ke Menu Kelas</a>
            </div>
        <?php else: ?>

            <div class="db-section" style="margin-bottom:1rem;">
                <div class="search-bar">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <input type="text" id="classSearchInput" placeholder="Cari kelas atau mata pelajaran...">
                </div>
            </div>

            <div id="searchResultsArea">
            <?php
            $display_grades = ['10' => 'Kelas 10', '11' => 'Kelas 11', '12' => 'Kelas 12', 'Others' => 'Kelas Lainnya'];
            foreach ($display_grades as $key => $label):
                if (!empty($grouped_classes[$key])):
            ?>
                <div class="db-section grade-section" data-grade-section>
                    <div class="grade-header">
                        <span class="grade-badge"><?php echo $key === 'Others' ? 'Lainnya' : $key; ?></span>
                        <span><?php echo $label; ?></span>
                    </div>
                    <?php foreach ($grouped_classes[$key] as $class):
                        $bgStyle = getSubjectStyle($class['subject']);
                        $studentCount = $class['is_special_class'] ? $class['special_student_count'] : $class['regular_student_count'];
                    ?>
                    <a href="grades_input.php?class_id=<?php echo $class['id']; ?>"
                       class="class-row"
                       data-name="<?php echo strtolower(htmlspecialchars($class['name'])); ?>"
                       data-subject="<?php echo strtolower(htmlspecialchars($class['subject'])); ?>">
                        <div class="class-row-left">
                            <div class="class-icon" style="background:<?php echo $bgStyle; ?>">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,0.9)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                            </div>
                            <div>
                                <div class="class-name">
                                    <?php echo htmlspecialchars($class['name']); ?>
                                    <?php if($class['is_special_class']): ?><span style="background:#fbbf24; color:#78350f; font-size:0.7rem; padding:1px 6px; border-radius:4px; margin-left:5px; font-weight:700;">Khusus</span><?php endif; ?>
                                </div>
                                <div class="class-meta">
                                    <span class="subject-chip"><?php echo htmlspecialchars($class['subject']); ?></span>
                                    &nbsp;·&nbsp;<?php echo $class['is_special_class'] ? 'Lintas Kelas' : htmlspecialchars($class['school_class_name']); ?>
                                </div>
                            </div>
                        </div>
                        <div class="class-row-right">
                            <span class="student-count"><?php echo $studentCount; ?> siswa</span>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; endforeach; ?>
            </div>

            <div id="noResultsMsg" style="display:none; text-align:center; padding:4rem 1rem; color:#64748b;">
                <p style="font-size:1.1rem; font-weight:500;">Tidak ditemukan kelas yang cocok.</p>
            </div>
        <?php endif; ?>

        </div><!-- end page-content -->
    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('classSearchInput');
    if (!searchInput) return;

    searchInput.addEventListener('input', function() {
        const query = this.value.toLowerCase().trim();
        const gradeSections = document.querySelectorAll('.grade-section');
        let hasGlobalResults = false;

        gradeSections.forEach(section => {
            const cards = section.querySelectorAll('.class-card');
            let hasSectionResults = false;

            cards.forEach(card => {
                const name = card.dataset.name || '';
                const subject = card.dataset.subject || '';
                
                if (name.includes(query) || subject.includes(query)) {
                    card.style.display = 'flex';
                    hasSectionResults = true;
                    hasGlobalResults = true;
                } else {
                    card.style.display = 'none';
                }
            });

            if (hasSectionResults) {
                section.style.display = 'block';
            } else {
                section.style.display = 'none';
            }
        });

        const noResultsMsg = document.getElementById('noResultsMsg');
        if (hasGlobalResults) {
            noResultsMsg.style.display = 'none';
        } else {
            noResultsMsg.style.display = 'block';
        }
    });
});
</script>
</body>
</html>



