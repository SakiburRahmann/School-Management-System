<?php
/**
 * Teacher Header
 * Common header for all teacher pages
 */

require_once __DIR__ . '/../config.php';
requireRole('Teacher');

$currentUser = (new User())->getUserWithRelated(getUserId());
$teacherInfo = $currentUser['related_info'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Teacher Panel'; ?> - <?php echo SITE_NAME; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/admin.css">
</head>
<body>
    <div class="admin-wrapper">
        <!-- Sidebar Overlay (for mobile) -->
        <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>
        
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <h2>Teacher Portal</h2>
                <p><?php echo htmlspecialchars($teacherInfo['name'] ?? 'Teacher'); ?></p>
                <!-- Close button for mobile -->
                <button class="sidebar-close" onclick="toggleSidebar()" aria-label="Close menu">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <nav class="sidebar-menu">
                <a href="<?php echo BASE_URL; ?>/teacher/dashboard.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                    <i class="fas fa-home"></i> Dashboard
                </a>
                
                <a href="<?php echo BASE_URL; ?>/teacher/attendance.php" class="menu-item">
                    <i class="fas fa-calendar-check"></i> Attendance
                </a>
                
                <a href="<?php echo BASE_URL; ?>/teacher/subjects.php" class="menu-item">
                    <i class="fas fa-book"></i> My Subjects
                </a>
                
                <a href="<?php echo BASE_URL; ?>/teacher/exams/" class="menu-item <?php echo (strpos($_SERVER['PHP_SELF'], '/teacher/exams/') !== false) ? 'active' : ''; ?>">
                    <i class="fas fa-clipboard-list"></i> My Exams
                </a>

                
                <a href="<?php echo BASE_URL; ?>/teacher/notices.php" class="menu-item">
                    <i class="fas fa-bullhorn"></i> Notices
                </a>
                
                <a href="<?php echo BASE_URL; ?>/teacher/profile.php" class="menu-item">
                    <i class="fas fa-user"></i> My Profile
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
                    <!-- Hamburger Menu Button (visible on mobile) -->
                    <button class="hamburger-menu" id="hamburgerBtn" onclick="toggleSidebar()" aria-label="Toggle menu">
                        <span class="hamburger-line"></span>
                        <span class="hamburger-line"></span>
                        <span class="hamburger-line"></span>
                    </button>
                    <h1><?php echo $pageTitle ?? 'Dashboard'; ?></h1>
                </div>
                
                <div class="header-right">
                    <div class="user-info">
                        <div class="user-avatar">
                            <?php echo strtoupper(substr($currentUser['username'], 0, 1)); ?>
                        </div>
                        <div class="user-details">
                            <h4><?php echo htmlspecialchars($teacherInfo['name'] ?? $currentUser['username']); ?></h4>
                            <p>Teacher</p>
                        </div>
                    </div>
                </div>
            </header>
            
            <script>
            // Sidebar toggle functionality
            function toggleSidebar() {
                const sidebar = document.getElementById('sidebar');
                const overlay = document.getElementById('sidebarOverlay');
                const hamburger = document.getElementById('hamburgerBtn');
                
                sidebar.classList.toggle('active');
                overlay.classList.toggle('active');
                hamburger.classList.toggle('active');
                
                if (sidebar.classList.contains('active')) {
                    document.body.style.overflow = 'hidden';
                } else {
                    document.body.style.overflow = '';
                }
            }
            
            document.querySelectorAll('.sidebar .menu-item').forEach(item => {
                item.addEventListener('click', function() {
                    if (window.innerWidth < 1024) {
                        toggleSidebar();
                    }
                });
            });
            
            window.addEventListener('resize', function() {
                if (window.innerWidth >= 1024) {
                    document.getElementById('sidebar').classList.remove('active');
                    document.getElementById('sidebarOverlay').classList.remove('active');
                    document.getElementById('hamburgerBtn').classList.remove('active');
                    document.body.style.overflow = '';
                }
            });
            </script>
            
            <!-- Content Area -->
            <div class="content">
                <?php if ($flash = getFlash()): ?>
                    <div class="alert alert-<?php echo $flash['type']; ?>">
                        <?php echo $flash['message']; ?>
                    </div>
                <?php endif; ?>

