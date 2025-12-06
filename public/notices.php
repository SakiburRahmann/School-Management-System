<?php
/**
 * Public - Notices Page
 */

require_once __DIR__ . '/../config.php';

$noticeModel = new Notice();

// Get all public notices
$notices = $noticeModel->getPublicNotices();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notices - <?php echo SITE_NAME; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/style.css?v=<?php echo time(); ?>">
</head>
<body>
    <?php require_once __DIR__ . '/../includes/public_header.php'; ?>
    
    <!-- Page Header -->
    <section class="hero" style="padding: 4rem 0;">
        <div class="container">
            <h1>Notices & Announcements</h1>
            <p>Stay updated with our latest news</p>
        </div>
    </section>
    
    <!-- Notices Section -->
    <section class="section">
        <div class="container">
            <?php if (!empty($notices)): ?>
                <div class="notice-list">
                    <?php foreach ($notices as $notice): ?>
                        <div class="notice-card <?php echo $notice['priority'] === 'High' ? 'priority-high' : ''; ?>">
                            <div class="notice-header">
                                <h3><?php echo htmlspecialchars($notice['title']); ?></h3>
                                <?php if ($notice['priority'] === 'High'): ?>
                                    <span class="priority-badge">
                                        <i class="fas fa-exclamation-circle"></i> Important
                                    </span>
                                <?php endif; ?>
                            </div>
                            
                            <p class="notice-content">
                                <?php echo htmlspecialchars($notice['content']); ?>
                            </p>
                            
                            <div class="card-meta">
                                <span><i class="fas fa-calendar"></i> <?php echo formatDate($notice['created_at']); ?></span>
                                <span><i class="fas fa-user"></i> <?php echo htmlspecialchars($notice['created_by_name'] ?? 'Administration'); ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <p>No notices available at the moment.</p>
                </div>
            <?php endif; ?>
        </div>
    </section>
    
    <!-- Footer -->
    <footer class="site-footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-section">
                    <h3>About Us</h3>
                    <p style="color: rgba(255,255,255,0.7); line-height: 1.8;">
                        We are committed to providing quality education and nurturing young minds 
                        to become future leaders.
                    </p>
                </div>
                
                <div class="footer-section">
                    <h3>Quick Links</h3>
                    <ul>
                        <li><a href="<?php echo BASE_URL; ?>/public/about.php">About Us</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/public/academics.php">Academics</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/public/admissions.php">Admissions</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/public/contact.php">Contact</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h3>Resources</h3>
                    <ul>
                        <li><a href="<?php echo BASE_URL; ?>/public/notices.php">Notices</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/public/events.php">Events</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/public/gallery.php">Gallery</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/login.php">Student/Teacher Login</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h3>Contact Info</h3>
                    <ul style="color: rgba(255,255,255,0.7);">
                        <li><i class="fas fa-map-marker-alt"></i> 123 School Street, City</li>
                        <li><i class="fas fa-phone"></i> +1 234 567 8900</li>
                        <li><i class="fas fa-envelope"></i> info@school.com</li>
                    </ul>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html>
