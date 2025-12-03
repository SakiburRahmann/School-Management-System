<?php
/**
 * Public - Events Page
 */

require_once __DIR__ . '/../config.php';

$eventModel = new Event();

// Get upcoming and past events
$upcomingEvents = $eventModel->getUpcoming(20, 1);
$pastEvents = $eventModel->getPast(10, 1);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Events - <?php echo SITE_NAME; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/style.css">
</head>
<body>
    <!-- Header -->
    <header class="site-header">
        <div class="container">
            <div class="header-content">
                <div class="site-logo">
                    <h1><?php echo SITE_NAME; ?></h1>
                    <p>Excellence in Education</p>
                </div>
                
                <nav class="main-nav">
                    <ul>
                        <li><a href="<?php echo BASE_URL; ?>/public/index.php">Home</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/public/about.php">About</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/public/academics.php">Academics</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/public/admissions.php">Admissions</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/public/events.php" style="opacity: 1; font-weight: 600;">Events</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/public/gallery.php">Gallery</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/public/notices.php">Notices</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/public/contact.php">Contact</a></li>
                    </ul>
                </nav>
                
                <a href="<?php echo BASE_URL; ?>/login.php" class="login-btn">
                    <i class="fas fa-sign-in-alt"></i> Login
                </a>
            </div>
        </div>
    </header>
    
    <!-- Page Header -->
    <section class="hero" style="padding: 4rem 0;">
        <div class="container">
            <h1>School Events</h1>
            <p>Stay connected with our community</p>
        </div>
    </section>
    
    <!-- Upcoming Events -->
    <section class="section">
        <div class="container">
            <div class="section-header">
                <h2>Upcoming Events</h2>
                <p>Mark your calendars for these exciting events</p>
            </div>
            
            <?php if (!empty($upcomingEvents)): ?>
                <div class="cards-grid">
                    <?php foreach ($upcomingEvents as $event): ?>
                        <div class="card">
                            <div class="card-image">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                            <div class="card-content">
                                <h3><?php echo htmlspecialchars($event['title']); ?></h3>
                                <p><?php echo htmlspecialchars(substr($event['description'] ?? '', 0, 150)); ?><?php echo strlen($event['description'] ?? '') > 150 ? '...' : ''; ?></p>
                                <div class="card-meta">
                                    <span>
                                        <i class="fas fa-calendar"></i> 
                                        <?php echo formatDate($event['event_date']); ?>
                                    </span>
                                    <?php if ($event['event_time']): ?>
                                        <span>
                                            <i class="fas fa-clock"></i> 
                                            <?php echo date('h:i A', strtotime($event['event_time'])); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <?php if ($event['location']): ?>
                                    <p style="margin-top: 0.5rem; color: #666;">
                                        <i class="fas fa-map-marker-alt"></i> 
                                        <?php echo htmlspecialchars($event['location']); ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div style="text-align: center; padding: 3rem 0;">
                    <i class="fas fa-calendar-times" style="font-size: 3rem; color: #ccc; margin-bottom: 1rem;"></i>
                    <p style="font-size: 1.125rem; color: #999;">No upcoming events at the moment.</p>
                </div>
            <?php endif; ?>
        </div>
    </section>
    
    <!-- Past Events -->
    <?php if (!empty($pastEvents)): ?>
    <section class="section" style="background: var(--light);">
        <div class="container">
            <div class="section-header">
                <h2>Past Events</h2>
                <p>Highlights from our recent activities</p>
            </div>
            
            <div class="cards-grid">
                <?php foreach ($pastEvents as $event): ?>
                    <div class="card">
                        <div class="card-image" style="opacity: 0.7;">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <div class="card-content">
                            <h3><?php echo htmlspecialchars($event['title']); ?></h3>
                            <p><?php echo htmlspecialchars(substr($event['description'] ?? '', 0, 120)); ?><?php echo strlen($event['description'] ?? '') > 120 ? '...' : ''; ?></p>
                            <div class="card-meta">
                                <span>
                                    <i class="fas fa-calendar"></i> 
                                    <?php echo formatDate($event['event_date']); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>
    
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
