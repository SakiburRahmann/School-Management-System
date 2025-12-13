<?php
require_once __DIR__ . '/../../config.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors in output, only log them
ini_set('log_errors', 1);

header('Content-Type: application/json');

try {
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
    $sql = "SELECT DISTINCT subject_name 
            FROM subjects 
            WHERE (class_id IN ($placeholders) OR class_id IS NULL) 
            AND status = 'Active' 
            ORDER BY subject_name";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute($classIds);
    $subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($subjects);
    
} catch (Exception $e) {
    // Log the error
    error_log("Error in get_subjects_by_class.php: " . $e->getMessage());
    
    // Return error details for debugging
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
}
