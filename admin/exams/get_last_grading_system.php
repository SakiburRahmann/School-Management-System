<?php
require_once __DIR__ . '/../../config.php';

requireAuth();

header('Content-Type: application/json');

try {
    $db = Database::getInstance()->getConnection();
    
    // Get last exam's grading system
    $stmt = $db->query("SELECT grading_system FROM exams 
                        WHERE grading_system IS NOT NULL 
                        ORDER BY created_at DESC LIMIT 1");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result && !empty($result['grading_system'])) {
        echo json_encode([
            'success' => true,
            'data' => json_decode($result['grading_system']),
            'source' => 'last_exam'
        ]);
    } else {
        // Return default system with GPA values
        $defaults = [
            ['grade' => 'A+', 'gpa' => 5.00, 'min_percent' => 80, 'max_percent' => 100],
            ['grade' => 'A', 'gpa' => 4.00, 'min_percent' => 70, 'max_percent' => 79],
            ['grade' => 'A-', 'gpa' => 3.50, 'min_percent' => 60, 'max_percent' => 69],
            ['grade' => 'B', 'gpa' => 3.00, 'min_percent' => 50, 'max_percent' => 59],
            ['grade' => 'C', 'gpa' => 2.00, 'min_percent' => 40, 'max_percent' => 49],
            ['grade' => 'D', 'gpa' => 1.00, 'min_percent' => 33, 'max_percent' => 39],
            ['grade' => 'F', 'gpa' => 0.00, 'min_percent' => 0, 'max_percent' => 32]
        ];
        
        echo json_encode([
            'success' => true,
            'data' => $defaults,
            'is_default' => true
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
