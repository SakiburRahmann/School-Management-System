<?php
/**
 * Helper Functions
 * Common utility functions used throughout the application
 */

/**
 * Sanitize input data
 */
function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

/**
 * Redirect to a URL
 */
function redirect($url) {
    header("Location: " . $url);
    exit();
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['role']);
}

/**
 * Get current user ID
 */
function getUserId() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current user role
 */
function getUserRole() {
    return $_SESSION['role'] ?? null;
}

/**
 * Check if user has specific role
 */
function hasRole($role) {
    return getUserRole() === $role;
}

/**
 * Require authentication
 */
function requireAuth() {
    if (!isLoggedIn()) {
        redirect(BASE_URL . '/login.php');
    }
}

/**
 * Require specific role
 */
function requireRole($allowedRoles) {
    requireAuth();
    
    if (!is_array($allowedRoles)) {
        $allowedRoles = [$allowedRoles];
    }
    
    if (!in_array(getUserRole(), $allowedRoles)) {
        redirect(BASE_URL . '/unauthorized.php');
    }
}

/**
 * Generate CSRF Token
 */
function generateCSRFToken() {
    if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

/**
 * Verify CSRF Token
 */
function verifyCSRFToken($token) {
    return isset($_SESSION[CSRF_TOKEN_NAME]) && hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
}

/**
 * Set flash message
 */
function setFlash($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Get and clear flash message
 */
function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Format date for display
 */
function formatDate($date, $format = DISPLAY_DATE_FORMAT) {
    if (empty($date)) return '';
    return date($format, strtotime($date));
}

/**
 * Format datetime for display
 */
function formatDateTime($datetime, $format = DISPLAY_DATETIME_FORMAT) {
    if (empty($datetime)) return '';
    return date($format, strtotime($datetime));
}

/**
 * Calculate age from date of birth
 */
function calculateAge($dob) {
    $birthDate = new DateTime($dob);
    $today = new DateTime('today');
    return $birthDate->diff($today)->y;
}

/**
 * Generate random password
 */
function generatePassword($length = 8) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    return substr(str_shuffle($chars), 0, $length);
}

/**
 * Upload file
 */
function uploadFile($file, $directory = 'general') {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'No file uploaded or upload error'];
    }
    
    // Check file size
    if ($file['size'] > MAX_FILE_SIZE) {
        return ['success' => false, 'message' => 'File size exceeds limit'];
    }
    
    // Check file type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mimeType, ALLOWED_IMAGE_TYPES)) {
        return ['success' => false, 'message' => 'Invalid file type'];
    }
    
    // Create directory if not exists
    $uploadPath = UPLOAD_DIR . $directory . '/';
    if (!is_dir($uploadPath)) {
        mkdir($uploadPath, 0755, true);
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . $extension;
    $destination = $uploadPath . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return [
            'success' => true,
            'filename' => $filename,
            'path' => $directory . '/' . $filename
        ];
    }
    
    return ['success' => false, 'message' => 'Failed to move uploaded file'];
}

/**
 * Delete file
 */
function deleteFile($path) {
    $fullPath = UPLOAD_DIR . $path;
    if (file_exists($fullPath)) {
        return unlink($fullPath);
    }
    return false;
}

/**
 * Get file URL
 */
function getFileUrl($path) {
    if (empty($path)) return '';
    return BASE_URL . '/public/uploads/' . $path;
}

/**
 * Paginate array
 */
function paginate($items, $page = 1, $perPage = RECORDS_PER_PAGE) {
    $total = count($items);
    $totalPages = ceil($total / $perPage);
    $page = max(1, min($page, $totalPages));
    $offset = ($page - 1) * $perPage;
    
    return [
        'items' => array_slice($items, $offset, $perPage),
        'current_page' => $page,
        'total_pages' => $totalPages,
        'total_items' => $total,
        'per_page' => $perPage
    ];
}

/**
 * Calculate grade from marks
 */
function calculateGrade($marks, $totalMarks = 100, $gradingSystem = null) {
    $percentage = ($marks / $totalMarks) * 100;
    
    // Use custom grading system if provided
    if ($gradingSystem && is_array($gradingSystem)) {
        foreach ($gradingSystem as $gradeRule) {
            // Support both object/array structure from JSON decode
            $gradeRule = (array)$gradeRule;
            
            $min = floatval($gradeRule['min_percent']);
            $max = floatval($gradeRule['max_percent']);
            
            if ($percentage >= $min && $percentage <= $max) {
                return $gradeRule['grade'];
            }
        }
        return 'F'; // Default fallback if no range matches (though strict ranges should cover it)
    }
    
    // Default legacy system
    if ($percentage >= 80) return 'A+';
    if ($percentage >= 70) return 'A';
    if ($percentage >= 60) return 'A-';
    if ($percentage >= 50) return 'B';
    if ($percentage >= 40) return 'C';
    if ($percentage >= 33) return 'D';
    return 'F';
}

/**
 * Get status badge class
 */
function getStatusBadge($status) {
    $badges = [
        'Paid' => 'success',
        'Unpaid' => 'danger',
        'Present' => 'success',
        'Absent' => 'danger',
        'Late' => 'warning',
        'Pending' => 'warning',
        'Approved' => 'success',
        'Rejected' => 'danger',
        'Active' => 'success',
        'Inactive' => 'secondary'
    ];
    
    return $badges[$status] ?? 'secondary';
}

/**
 * Debug helper
 */
function dd($data) {
    echo '<pre>';
    var_dump($data);
    echo '</pre>';
    die();
}
