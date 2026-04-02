<?php
// src/admin/sync_classes.php

// Helper script included in manage_schedules.php right before commit
// Assumes $pdo is available and we are inside the transaction block
// $schedules table has been populated

try {
    // 1. Get all unique combinations from the schedules table we just populated
    // We exclude 'UPACARA BENDERA' since it's not a real subject
    $stmt_distinct = $pdo->query("
        SELECT DISTINCT kelas, mata_pelajaran, nama_guru 
        FROM schedules 
        WHERE mata_pelajaran != 'UPACARA BENDERA' 
          AND mata_pelajaran IS NOT NULL AND mata_pelajaran != ''
          AND nama_guru IS NOT NULL AND nama_guru != ''
          AND kelas IS NOT NULL AND kelas != ''
    ");
    $unique_schedules = $stmt_distinct->fetchAll(PDO::FETCH_ASSOC);

    // Prepare lookups
    $stmt_get_class = $pdo->prepare("SELECT id FROM classes WHERE REPLACE(REPLACE(LOWER(name), ' ', ''), '-', '') = REPLACE(REPLACE(LOWER(?), ' ', ''), '-', '') LIMIT 1");
    // Looser matching for subjects and teachers
    $stmt_get_subject = $pdo->prepare("SELECT id FROM subjects WHERE LOWER(name) LIKE LOWER(?) LIMIT 1");
    // Match guru by name (ignoring titles if possible, using LIKE)
    $stmt_get_guru = $pdo->prepare("SELECT id FROM users WHERE role = 'guru' AND LOWER(full_name) LIKE LOWER(?) LIMIT 1");

    $stmt_check_tc = $pdo->prepare("SELECT id FROM teacher_classes WHERE teacher_id = ? AND subject_id = ? AND class_id = ?");
    $stmt_insert_tc = $pdo->prepare("INSERT INTO teacher_classes (teacher_id, subject_id, class_id) VALUES (?, ?, ?)");

    $upload_base_dir = __DIR__ . '/../../public/uploads/classes/';

    foreach ($unique_schedules as $sched) {
        $kelas_name = $sched['kelas'];
        $mapel_name = $sched['mata_pelajaran'];
        $guru_name = $sched['nama_guru'];

        // Find Class ID
        $stmt_get_class->execute([$kelas_name]);
        $class_id = $stmt_get_class->fetchColumn();

        // Find Subject ID
        $stmt_get_subject->execute(['%' . $mapel_name . '%']);
        $subject_id = $stmt_get_subject->fetchColumn();

        // Find Teacher ID (strip titles for better matching)
        $clean_guru_name = trim(explode(',', $guru_name)[0]);
        $stmt_get_guru->execute(['%' . $clean_guru_name . '%']);
        $teacher_id = $stmt_get_guru->fetchColumn();

        // If we found all three required IDs
        if ($class_id && $subject_id && $teacher_id) {
            // Check if it already exists
            $stmt_check_tc->execute([$teacher_id, $subject_id, $class_id]);
            if (!$stmt_check_tc->fetch()) {
                // Doesn't exist, create it!
                $stmt_insert_tc->execute([$teacher_id, $subject_id, $class_id]);
                $new_tc_id = $pdo->lastInsertId();

                // Create physical folders for this new class
                $class_dir = $upload_base_dir . $new_tc_id;
                if (!is_dir($class_dir)) {
                    mkdir($class_dir, 0777, true);
                    // Create subdirectories for Materials and Assignments (Tugas/Materi logic usually depends on folders)
                    mkdir($class_dir . '/materi', 0777, true);
                    mkdir($class_dir . '/tugas', 0777, true);
                }
            }
        }
    }
}
catch (Exception $e) {
    // Log the error but don't fail the whole import if this is just a sync step
    error_log("Failed to auto-sync teacher classes during schedule import: " . $e->getMessage());
}
?>
