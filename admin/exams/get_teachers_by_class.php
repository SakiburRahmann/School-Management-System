<?php
require_once __DIR__ . '/../../config.php';

header('Content-Type: application/json');

$classIds = [];

if (isset($_GET['class_id'])) {
    if (strpos($_GET['class_id'], ',') !== false) {
        $classIds = explode(',', $_GET['class_id']);
    } else {
        $classIds = [$_GET['class_id']];
    }
}

if (empty($classIds)) {
    echo json_encode([]);
    exit;
}


$conn = Database::getInstance()->getConnection();

// Create placeholders for IN clause
$placeholders = str_repeat('?,', count($classIds) - 1) . '?';

$sql = "SELECT DISTINCT t.teacher_id, t.name, t.subject_speciality
        FROM teachers t
        JOIN subject_teachers st ON t.teacher_id = st.teacher_id
        JOIN subjects s ON st.subject_id = s.subject_id
        WHERE s.class_id IN ($placeholders)
        ORDER BY t.name";

$stmt = $conn->prepare($sql);
$stmt->execute($classIds);
$teachers = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($teachers);
