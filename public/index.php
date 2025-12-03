<?php
/**
 * Public Website Homepage
 */

require_once __DIR__ . '/../config.php';

$noticeModel = new Notice();
$eventModel = new Event();
$studentModel = new Student();
$teacherModel = new Teacher();
$classModel = new ClassModel();

// Get public content
$latestNotices = $noticeModel->getPublicNotices(5);
$upcomingEvents = $eventModel->getUpcoming(3, 1);

// Get statistics
$totalStudents = $studentModel->getTotalCount();
$totalTeachers = $teacherModel->getTotalCount();
$totalClasses = $classModel->getTotalCount();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Home</title>
    <meta name="description" content="Welcome to our School Management System - Building Future Leaders Through Quality Education">
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
                        <li><a href="<?php echo BASE_URL; ?>/public/events.php">Events</a></li>
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
    
    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <h1>Welcome to Our School</h1>
            <p>Building Future Leaders Through Quality Education</p>
            <div class="hero-buttons">
                <a href="<?php echo BASE_URL; ?>/public/admissions.php" class="btn btn-primary">
                    Apply for Admission
                </a>
                <a href="<?php echo BASE_URL; ?>/public/about.php" class="btn btn-outline">
                    Learn More
                </a>
            </div>
        </div>
    </section>
    
    <!-- Statistics Section -->
    <section class="section" style="background: var(--light);">
        <div class="container">
            <div class="stats-grid">
                <div class="stat-box">
                    <div class="stat-icon">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <div class="stat-value"><?php echo $totalStudents; ?>+</div>
                    <div class="stat-label">Students Enrolled</div>
                </div>
                
                <div class="stat-box">
                    <div class="stat-icon">
                        <i class="fas fa-chalkboard-teacher"></i>
                    </div>
                    <div class="stat-value"><?php echo $totalTeachers; ?>+</div>
                    <div class="stat-label">Expert Teachers</div>
                </div>
                
                <div class="stat-box">
                    <div class="stat-icon">
                        <i class="fas fa-school"></i>
                    </div>
                    <div class="stat-value"><?php echo $totalClasses; ?></div>
                    <div class="stat-label">Classes Available</div>
                </div>
                
                <div class="stat-box">
                    <div class="stat-icon">
                        <i class="fas fa-trophy"></i>
                    </div>
                    <div class="stat-value">25+</div>
                    <div class="stat-label">Years of Excellence</div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- About Section -->
    <section class="section">
        <div class="container">
            <div class="section-header">
                <h2>About Our School</h2>
                <p>Committed to excellence in education since 1999</p>
            </div>
            
            <div style="max-width: 800px; margin: 0 auto; text-align: center;">
                <p style="font-size: 1.125rem; line-height: 1.8; color: #666; margin-bottom: 2rem;">
                    We are dedicated to providing a nurturing and stimulating environment where students can develop 
                    their full potential. Our experienced faculty, modern facilities, and comprehensive curriculum 
                    ensure that every student receives the best possible education.
                </p>
                <a href="<?php echo BASE_URL; ?>/public/about.php" class="btn btn-primary">
                    Read More About Us
                </a>
            </div>
        </div>
    </section>
    
    <!-- Latest Notices -->
    <?php if (!empty($latestNotices)): ?>
    <section class="section" style="background: var(--light);">
        <div class="container">
            <div class="section-header">
                <h2>Latest Notices</h2>
                <p>Stay updated with our latest announcements</p>
            </div>
            
            <div class="cards-grid">
                <?php foreach ($latestNotices as $notice): ?>
                    <div class="card">
                        <div class="card-image">
                            <i class="fas fa-bullhorn"></i>
                        </div>
                        <div class="card-content">
                            <h3><?php echo htmlspecialchars($notice['title']); ?></h3>
                            <p><?php echo substr(strip_tags($notice['content']), 0, 120); ?>...</p>
                            <div class="card-meta">
                                <span><i class="fas fa-calendar"></i> <?php echo formatDate($notice['created_at']); ?></span>
                                <?php if ($notice['priority'] == 'High'): ?>
                                    <span style="color: var(--danger); font-weight: 600;">
                                        <i class="fas fa-exclamation-circle"></i> Important
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div style="text-align: center; margin-top: 2rem;">
                <a href="<?php echo BASE_URL; ?>/public/notices.php" class="btn btn-primary">
                    View All Notices
                </a>
            </div>
        </div>
    </section>
    <?php endif; ?>
    
    <!-- Upcoming Events -->
    <?php if (!empty($upcomingEvents)): ?>
    <section class="section">
        <div class="container">
            <div class="section-header">
                <h2>Upcoming Events</h2>
                <p>Join us for these exciting events</p>
            </div>
            
            <div class="cards-grid">
                <?php foreach ($upcomingEvents as $event): ?>
                    <div class="card">
                        <div class="card-image">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <div class="card-content">
                            <h3><?php echo htmlspecialchars($event['title']); ?></h3>
                            <p><?php echo htmlspecialchars(substr($event['description'] ?? '', 0, 120)); ?>...</p>
                            <div class="card-meta">
                                <span><i class="fas fa-calendar"></i> <?php echo formatDate($event['event_date']); ?></span>
                                <?php if ($event['location']): ?>
                                    <span><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($event['location']); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div style="text-align: center; margin-top: 2rem;">
                <a href="<?php echo BASE_URL; ?>/public/events.php" class="btn btn-primary">
                    View All Events
                </a>
            </div>
        </div>
    </section>
    <?php endif; ?>
    
    <!-- Call to Action -->
    <section class="section" style="background: linear-gradient(135deg, var(--primary), var(--secondary)); color: white; text-align: center;">
        <div class="container">
            <h2 style="font-size: 2.5rem; margin-bottom: 1rem;">Ready to Join Us?</h2>
            <p style="font-size: 1.25rem; margin-bottom: 2rem; opacity: 0.95;">
                Start your journey towards excellence today
            </p>
            <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                <a href="<?php echo BASE_URL; ?>/public/admissions.php" class="btn" style="background: white; color: var(--primary);">
                    Apply Now
                </a>
                <a href="<?php echo BASE_URL; ?>/public/contact.php" class="btn btn-outline">
                    Contact Us
                </a>
            </div>
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
