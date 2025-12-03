<?php
/**
 * Configuration File
 * Contains all application settings and constants
 */

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'IAmTheMan!20040113!');
define('DB_NAME', 'school_management');

// Application Configuration
define('BASE_URL', 'http://localhost/SchoolMS');
define('SITE_NAME', 'School Management System');
define('SITE_TITLE', 'SMS - School Management System');

// File Upload Configuration
define('UPLOAD_DIR', __DIR__ . '/public/uploads/');
define('MAX_FILE_SIZE', 5242880); // 5MB in bytes
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);

// Session Configuration
define('SESSION_LIFETIME', 7200); // 2 hours in seconds
define('SESSION_NAME', 'SMS_SESSION');

// Security Configuration
define('CSRF_TOKEN_NAME', 'csrf_token');
define('PASSWORD_MIN_LENGTH', 6);

// Pagination Configuration
define('RECORDS_PER_PAGE', 20);

// Date & Time Configuration
define('DATE_FORMAT', 'Y-m-d');
define('DATETIME_FORMAT', 'Y-m-d H:i:s');
define('DISPLAY_DATE_FORMAT', 'd M, Y');
define('DISPLAY_DATETIME_FORMAT', 'd M, Y h:i A');

// Timezone
date_default_timezone_set('Asia/Dhaka');

// Error Reporting (Set to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start Session
if (session_status() === PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    session_start();
}

// Autoloader for Classes
spl_autoload_register(function ($class) {
    $paths = [
        __DIR__ . '/core/' . $class . '.php',
        __DIR__ . '/core/models/' . $class . '.php',
        __DIR__ . '/core/controllers/' . $class . '.php',
    ];
    
    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});

// Helper Functions
require_once __DIR__ . '/core/helpers.php';
