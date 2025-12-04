<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/core/Database.php';

$db = Database::getInstance()->getConnection();

$stmt = $db->prepare("SELECT * FROM users WHERE username = 'admin'");
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {
    echo "Current Hash: " . $user['password'] . "\n";
    
    if (password_verify('password', $user['password'])) {
        echo "MATCH FOUND: Password is 'password'\n";
    } else {
        echo "Password is NOT 'password'\n";
    }
    
    if (password_verify('admin123', $user['password'])) {
        echo "MATCH FOUND: Password is 'admin123'\n";
    } else {
        echo "Password is NOT 'admin123'\n";
        
        // Force update
        echo "Force updating password to 'admin123'...\n";
        $newHash = password_hash('admin123', PASSWORD_DEFAULT);
        $update = $db->prepare("UPDATE users SET password = :pass WHERE username = 'admin'");
        $update->execute(['pass' => $newHash]);
        echo "Update complete.\n";
    }
}

// Also fix teacher and student
$users = [
    'teacher1' => 'teacher123',
    'student1' => 'student123'
];

foreach ($users as $username => $pass) {
    $newHash = password_hash($pass, PASSWORD_DEFAULT);
    $update = $db->prepare("UPDATE users SET password = :pass WHERE username = :user");
    $update->execute(['pass' => $newHash, 'user' => $username]);
    echo "Updated $username to $pass\n";
}
