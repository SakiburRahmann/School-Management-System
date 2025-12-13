<?php
require_once __DIR__ . '/config.php';

try {
    $db = Database::getInstance()->getConnection();
    
    // Check if column exists
    $stmt = $db->query("SHOW COLUMNS FROM exams LIKE 'grading_system'");
    $exists = $stmt->fetch();
    
    if (!$exists) {
        // Add column
        $sql = "ALTER TABLE exams ADD COLUMN grading_system TEXT DEFAULT NULL AFTER total_marks";
        $db->exec($sql);
        echo "Successfully added 'grading_system' column to exams table.\n";
    } else {
        echo "Column 'grading_system' already exists.\n";
    }
    
} catch (PDOException $e) {
    die("Error updating database: " . $e->getMessage() . "\n");
}
