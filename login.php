<?php
/**
 * Login Handler
 * Processes user authentication
 */

require_once __DIR__ . '/config.php';

// If already logged in, redirect to appropriate dashboard
if (isLoggedIn()) {
    $role = getUserRole();
    switch ($role) {
        case 'Admin':
            redirect(BASE_URL . '/admin/dashboard.php');
            break;
        case 'Teacher':
            redirect(BASE_URL . '/teacher/dashboard.php');
            break;
        case 'Student':
            redirect(BASE_URL . '/student/dashboard.php');
            break;
    }
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        setFlash('danger', 'Invalid request. Please try again.');
        redirect(BASE_URL . '/login.php');
    }
    
    $username = sanitize($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        setFlash('danger', 'Please enter both username and password.');
        redirect(BASE_URL . '/login.php');
    }
    
    // Authenticate user
    $userModel = new User();
    $user = $userModel->authenticate($username, $password);
    
    if ($user) {
        // Set session variables
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['related_id'] = $user['related_id'];
        
        // Redirect based on role
        switch ($user['role']) {
            case 'Admin':
                redirect(BASE_URL . '/admin/dashboard.php');
                break;
            case 'Teacher':
                redirect(BASE_URL . '/teacher/dashboard.php');
                break;
            case 'Student':
                redirect(BASE_URL . '/student/dashboard.php');
                break;
            default:
                setFlash('danger', 'Invalid user role.');
                redirect(BASE_URL . '/login.php');
        }
    } else {
        setFlash('danger', 'Invalid username or password.');
        redirect(BASE_URL . '/login.php');
    }
}

// Display login form
include 'login_view.php';
