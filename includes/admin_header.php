<?php
/**
 * Admin Header
 * Common header for all admin pages
 */

require_once __DIR__ . '/../config.php';
requireRole('Admin');

$currentUser = (new User())->find(getUserId());
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Admin Panel'; ?> - <?php echo SITE_NAME; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/admin.css">
</head>
<body>
    <div class="admin-wrapper">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2>SMS Admin</h2>
                <p>School Management System</p>
            </div>
            
            <nav class="sidebar-menu">
                <a href="<?php echo BASE_URL; ?>/admin/dashboard.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                    <i class="fas fa-home"></i> Dashboard
                </a>
                
                <div class="menu-section">Academic</div>
                
                <a href="<?php echo BASE_URL; ?>/admin/students/" class="menu-item">
                    <i class="fas fa-user-graduate"></i> Students
                </a>
                
                <a href="<?php echo BASE_URL; ?>/admin/teachers/" class="menu-item">
                    <i class="fas fa-chalkboard-teacher"></i> Teachers
                </a>
                
                <a href="<?php echo BASE_URL; ?>/admin/classes/" class="menu-item">
                    <i class="fas fa-school"></i> Classes & Sections
                </a>
                
                <a href="<?php echo BASE_URL; ?>/admin/subjects/" class="menu-item">
                    <i class="fas fa-book"></i> Subjects
                </a>
                
                <div class="menu-section">Operations</div>
                
                <a href="<?php echo BASE_URL; ?>/admin/attendance/" class="menu-item">
                    <i class="fas fa-calendar-check"></i> Attendance
                </a>
                
                <a href="<?php echo BASE_URL; ?>/admin/exams/" class="menu-item">
                    <i class="fas fa-file-alt"></i> Exams
                </a>
                
                <a href="<?php echo BASE_URL; ?>/admin/results/" class="menu-item">
                    <i class="fas fa-chart-line"></i> Results
                </a>
                
                <a href="<?php echo BASE_URL; ?>/admin/fees/" class="menu-item">
                    <i class="fas fa-dollar-sign"></i> Fees
                </a>
                
                <div class="menu-section">Management</div>
                
                <a href="<?php echo BASE_URL; ?>/admin/users/" class="menu-item">
                    <i class="fas fa-users"></i> Users
                </a>
                
                <a href="<?php echo BASE_URL; ?>/admin/notices/" class="menu-item">
                    <i class="fas fa-bullhorn"></i> Notices
                </a>
                
                <a href="<?php echo BASE_URL; ?>/admin/events/" class="menu-item">
                    <i class="fas fa-calendar"></i> Events
                </a>
                
                <a href="<?php echo BASE_URL; ?>/admin/admissions/" class="menu-item">
                    <i class="fas fa-user-plus"></i> Admissions
                </a>
                
                <a href="<?php echo BASE_URL; ?>/admin/messages/" class="menu-item">
                    <i class="fas fa-envelope"></i> Messages
                </a>
                
                <div class="menu-section">Website</div>
                
                <a href="<?php echo BASE_URL; ?>/admin/website/" class="menu-item">
                    <i class="fas fa-globe"></i> Website Content
                </a>
                
                <a href="<?php echo BASE_URL; ?>/admin/gallery/" class="menu-item">
                    <i class="fas fa-images"></i> Gallery
                </a>
                
                <div class="menu-section">Account</div>
                
                <a href="<?php echo BASE_URL; ?>/logout.php" class="menu-item">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </nav>
        </aside>
        
        <!-- Main Content -->
        <div class="main-content">
            <!-- Header -->
            <header class="header">
                <div class="header-left">
                    <h1><?php echo $pageTitle ?? 'Dashboard'; ?></h1>
                </div>
                
                <div class="header-right">
                    <div class="user-info">
                        <div class="user-avatar">
                            <?php echo strtoupper(substr($currentUser['username'], 0, 1)); ?>
                        </div>
                        <div class="user-details">
                            <h4><?php echo htmlspecialchars($currentUser['username']); ?></h4>
                            <p><?php echo $currentUser['role']; ?></p>
                        </div>
                    </div>
                </div>
            </header>
            
            <!-- Content Area -->
            <div class="content">
                <?php if ($flash = getFlash()): ?>
                    <div class="alert alert-<?php echo $flash['type']; ?>">
                        <?php echo $flash['message']; ?>
                    </div>
                <?php endif; ?>
