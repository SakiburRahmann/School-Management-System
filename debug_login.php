<?php
/**
 * Debug Login Script
 * Resets passwords for default user accounts.
 * Usage: php debug_login.php
 */

require_once __DIR__ . '/config.php';

// Users to reset
$users = [
    'admin' => 'admin123',
    'teacher1' => 'teacher123',
    'student1' => 'student123'
];

$userModel = new User();

foreach ($users as $username => $password) {
    $user = $userModel->findByUsername($username);

    if ($user) {
        if ($userModel->updatePassword($user['user_id'], $password)) {
            echo "Successfully updated password for '{$username}' to '{$password}'.\n";
        } else {
            echo "Failed to update password for '{$username}'.\n";
        }
    } else {
        echo "User '{$username}' not found.\n";
    }
}
