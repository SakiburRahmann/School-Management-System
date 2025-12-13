<?php
require_once __DIR__ . '/../../config.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

header('Content-Type: application/json');

try {
    if (!isset($_GET['subject_name']) || empty($_GET['subject_name'])) {
        echo json_encode([]);
        exit;
    }
    
    $subjectName = $_GET['subject_name'];
    
    $conn = Database::getInstance()->getConnection();
    
    // Get teachers assigned to this subject
    $sql = "SELECT DISTINCT t.teacher_id, t.name, t.subject_speciality
            FROM teachers t
            JOIN subject_teachers st ON t.teacher_id = st.teacher_id
            JOIN subjects s ON st.subject_id = s.subject_id
            WHERE s.subject_name = :subject_name
            ORDER BY t.name";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute(['subject_name' => $subjectName]);
    $teachers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($teachers);
    
} catch (Exception $e) {
    error_log("Error in get_teachers_by_subject.php: " . $e->getMessage());
    
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage()
    ]);
}
