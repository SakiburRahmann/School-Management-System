<?php
/**
 * Logout Handler
 * Destroys session and redirects to login
 */

require_once __DIR__ . '/config.php';

// Destroy session
session_destroy();

// Clear session variables
$_SESSION = [];

// Redirect to login
setFlash('success', 'You have been logged out successfully.');
redirect(BASE_URL . '/login.php');
