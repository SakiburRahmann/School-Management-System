<?php
require_once __DIR__ . '/../../config.php';

header('Content-Type: application/json');

$classIds = [];

if (isset($_GET['class_id'])) {
    // Check if it's a comma-separated string or an array (though GET usually sends multiple params with same name as array if name ends in [], but here we might just send comma separated for simplicity in JS)
    // Or if we use standard form serialization, it might be separate.
    // Let's support comma separated string for the fetch call.
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

$db = new Database();

// Create placeholders for IN clause
$placeholders = str_repeat('?,', count($classIds) - 1) . '?';
$sql = "SELECT DISTINCT subject_name 
        FROM subjects 
        WHERE class_id IN ($placeholders) AND status = 'Active' 
        ORDER BY subject_name";

$stmt = $db->prepare($sql);
$stmt->execute($classIds);
$subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($subjects);
