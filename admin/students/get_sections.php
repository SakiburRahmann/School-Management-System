<?php
/**
 * AJAX endpoint to get sections by class
 */

require_once __DIR__ . '/../../config.php';
requireRole(['Admin', 'Teacher']);

header('Content-Type: application/json');

$classId = $_GET['class_id'] ?? null;

if (!$classId) {
    echo json_encode([]);
    exit;
}

$classModel = new ClassModel();
$sections = $classModel->getSections($classId);

echo json_encode($sections);
