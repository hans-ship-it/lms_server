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
        .grade-section { margin-bottom: 40px; }
        .grade-title {
            font-size: 1.25rem; font-weight: 700; color: #1e293b; margin-bottom: 20px;
            display: flex; align-items: center; gap: 10px; border-bottom: 2px solid #e2e8f0; padding-bottom: 10px;
        }
        .grade-badge {
            background: #f59e0b; color: #fff; padding: 4px 10px; border-radius: 6px; font-size: 0.85rem;
        }
        .class-grid {
            display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 24px;
        }
        .class-card {
            background: white; border-radius: 20px; overflow: hidden;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid rgba(226, 232, 240, 0.8);
            display: flex; flex-direction: column; text-decoration: none; color: inherit; position: relative;
        }
        .class-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 25px 35px -5px rgba(0, 0, 0, 0.15), 0 10px 15px -6px rgba(0, 0, 0, 0.1);
        }
        .class-header {
            padding: 24px; position: relative; color: white; min-height: 120px;
            display: flex; flex-direction: column;
        }
        .class-header::before {
            content: ''; position: absolute; inset: 0;
            background-image: radial-gradient(rgba(255, 255, 255, 0.2) 1.5px, transparent 1.5px);
            background-size: 12px 12px; opacity: 0.4;
        }
        .class-header h3 {
            margin: 0; font-size: 1.35rem; font-weight: 700; position: relative; z-index: 2;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1); line-height: 1.3;
        }
        .class-header p {
            margin: 6px 0 0; opacity: 0.95; font-size: 0.95rem; position: relative; z-index: 2; font-weight: 500;
        }
        .subject-badge {
            align-self: flex-end; margin-bottom: auto;
            background: rgba(255,255,255,0.25); backdrop-filter: blur(4px);
            padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 600;
            border: 1px solid rgba(255,255,255,0.3); z-index: 2;
        }
        .class-body {
            padding: 20px 24px; flex-grow: 1; background: #fff;
        }
        .class-info-row {
            display: flex; align-items: center; gap: 10px; margin-bottom: 12px; color: #64748b; font-size: 0.9rem;
        }
        .class-info-icon {
            width: 32px; height: 32px; border-radius: 8px; background: #fef3c7;
            display: flex; align-items: center; justify-content: center; font-size: 1rem; color: #b45309;
        }
    </style>
</head>
<body class="admin-full-layout">

<div class="app-container">
    <?php include '../templates/sidebar.php'; ?>
    
    <main class="main-content">
        <!-- Dashboard Hero -->
        <div class="dashboard-hero" data-lms-role="guru">
            <div class="dashboard-container hero-top-container">
                <div class="hero-text-container">
                    <h1 style="color: white; margin-bottom: 0.5rem;">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:middle;margin-right:8px;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                        Pilih Kelas untuk Penilaian
                    </h1>
                    <p style="color: rgba(255,255,255,0.8);">Silakan pilih kelas yang ingin Anda berikan nilainya. Data tersimpan secara real-time.</p>
                </div>
                <div class="hero-actions-container">
                    <div class="hero-search-box">
                        <span style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #64748b; pointer-events: none;"><svg width='18' height='18' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><circle cx='11' cy='11' r='8'/><line x1='21' y1='21' x2='16.65' y2='16.65'/></svg></span>
                        <input type="text" id="classSearchInput" class="hero-search-input" placeholder="Cari kelas atau mata pelajaran...">
                    </div>
                </div>
            </div>
            
            <div style="position: absolute; right: -50px; top: -50px; width: 250px; height: 250px; background: rgba(255,255,255,0.05); border-radius: 50%; pointer-events: none;"></div>
            <div style="position: absolute; right: 100px; bottom: -80px; width: 150px; height: 150px; background: rgba(255,255,255,0.05); border-radius: 50%; pointer-events: none;"></div>
        </div>

        <div class="content-overlap">
            <div class="dashboard-container">

        <?php if (empty($all_my_classes)): ?>
            <div style="text-align: center; padding: 5rem 2rem; background: white; border-radius: 24px; border: 2px dashed #cbd5e1; max-width: 600px; margin: 40px auto;">
                <div style="font-size: 4rem; opacity: 0.8; margin-bottom: 1.5rem;">â„¹ï¸</div>
                <h3 style="color: #1e293b; font-size: 1.5rem; font-weight: 700; margin-bottom: 0.75rem;">Belum ada kelas aktif</h3>
                <p style="color: #64748b;">Anda harus membuat kelas terlebih dahulu di menu "Kelas & Materi" sebelum menginput nilai.</p>
                <a href="kelas.php" class="btn" style="display: inline-block; margin-top: 1rem; text-decoration: none;">Ke Menu Kelas</a>
            </div>
        <?php else: ?>
            <div id="searchResultsArea">
            <?php
            $display_grades = ['10' => 'Kelas 10', '11' => 'Kelas 11', '12' => 'Kelas 12', 'Others' => 'Kelas Lainnya'];
            foreach ($display_grades as $key => $label): 
                if (!empty($grouped_classes[$key])): 
            ?>
                    <div class="grade-section" data-grade-section>
                        <div class="grade-title">
                            <span class="grade-badge"><?php echo $key === 'Others' ? 'Lainnya' : $key; ?></span>
                            <?php echo $label; ?>
                        </div>
                        <div class="class-grid">
                            <?php foreach ($grouped_classes[$key] as $class):
                                $bgStyle = getSubjectStyle($class['subject']);
                            ?>
                                <a href="grades_input.php?class_id=<?php echo $class['id']; ?>" class="class-card" 
                                   data-name="<?php echo strtolower(htmlspecialchars($class['name'])); ?>"
                                   data-subject="<?php echo strtolower(htmlspecialchars($class['subject'])); ?>">
                                    <div class="class-header" style="background: <?php echo $bgStyle; ?>;">
                                        <div class="subject-badge"><?php echo htmlspecialchars($class['subject']); ?></div>
                                        <h3><?php echo htmlspecialchars($class['name']); ?> <?php if($class['is_special_class']) echo '<span style="font-size: 0.8rem; background: #fbbf24; color: #78350f; padding: 2px 6px; border-radius: 4px; margin-left: 5px;">Khusus</span>'; ?></h3>
                                        <p><?php echo $class['is_special_class'] ? 'Lintas Kelas' : htmlspecialchars($class['school_class_name']); ?></p>
                                    </div>
                                    <div class="class-body">
                                        <div class="class-info-row">
                                            <div class="class-info-icon"><svg width='20' height='20' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></div>
                                            <span style="font-weight: 600; color: #334155;"><?php echo $class['is_special_class'] ? $class['special_student_count'] : $class['regular_student_count']; ?></span> 
                                            <span style="color: #94a3b8;">Siswa Terdaftar</span>
                                        </div>
                                        <div style="margin-top: 15px; border-top: 1px dashed #e2e8f0; padding-top: 15px; color: #f59e0b; font-weight: 600; font-size: 0.9rem; display: flex; align-items: center; gap: 5px;">
                                            Input Nilai Sekarang &rarr;
                                        </div>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
            </div>

            <!-- No Results Message -->
            <div id="noResultsMsg" style="display: none; text-align: center; padding: 4rem 1rem; color: #64748b;">
                <p style="font-size: 3rem; margin-bottom: 10px;">ðŸ“‰</p>
                <p style="font-size: 1.1rem; font-weight: 500;">Tidak ditemukan kelas yang cocok.</p> 
            </div>
        <?php endif; ?>

            </div> <!-- End Dashboard Container -->
        </div>
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

