<?php
// src/guru/get_class_stats.php
session_start();
require_once '../../config/database.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'guru') {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$teacher_id = $_SESSION['user_id'];
$class_id = $_GET['class_id'] ?? 0;

// Fetch Average Grades per Assignment (ignoring archived submissions and assignments)
// Only consider 'tugas' type assignments
$stmt = $pdo->prepare("
    SELECT a.title, AVG(s.grade) as avg_grade
    FROM assignments a
    JOIN submissions s ON a.id = s.assignment_id
    WHERE a.teacher_class_id = ? 
      AND a.teacher_id = ? 
      AND a.assignment_type = 'tugas'
      AND s.is_archived = 0
    GROUP BY a.id, a.title, a.created_at
    ORDER BY a.created_at ASC
");
$stmt->execute([$class_id, $teacher_id]);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($data);
?>
