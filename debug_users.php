<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/core/Database.php';

$db = Database::getInstance()->getConnection();

$usernames = ['teacher1', 'student1'];

foreach ($usernames as $username) {
    echo "--------------------------------------------------\n";
    echo "Checking user: $username\n";
    
    $stmt = $db->prepare("SELECT * FROM users WHERE username = :username");
    $stmt->execute(['username' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        echo "User ID: " . $user['user_id'] . "\n";
        echo "Role: " . $user['role'] . "\n";
        echo "Status: " . $user['status'] . "\n";
        echo "Is Active: " . $user['is_active'] . "\n";
        echo "Related ID: " . $user['related_id'] . "\n";
        
        // Verify password
        $pass = $username . '123'; // teacher123, student123
        if (password_verify($pass, $user['password'])) {
            echo "Password '$pass': MATCH\n";
        } else {
            echo "Password '$pass': MISMATCH\n";
        }
        
        // Check related record
        if ($user['related_id']) {
            $table = ($user['role'] === 'Teacher') ? 'teachers' : 'students';
            $idCol = ($user['role'] === 'Teacher') ? 'teacher_id' : 'student_id';
            
            $stmt2 = $db->prepare("SELECT * FROM $table WHERE $idCol = :id");
            $stmt2->execute(['id' => $user['related_id']]);
            $related = $stmt2->fetch(PDO::FETCH_ASSOC);
            
            if ($related) {
                echo "Related Record ($table): Found (Name: " . $related['name'] . ")\n";
            } else {
                echo "Related Record ($table): NOT FOUND (ID: " . $user['related_id'] . ")\n";
            }
        } else {
            echo "Related ID is NULL!\n";
        }
        
    } else {
        echo "User not found in database.\n";
    }
}
echo "--------------------------------------------------\n";
