<?php
/**
 * Public - About Us Page
 */

require_once __DIR__ . '/../config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - <?php echo SITE_NAME; ?></title>
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
                        <li><a href="<?php echo BASE_URL; ?>/public/about.php" style="opacity: 1; font-weight: 600;">About</a></li>
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
    
    <!-- Page Header -->
    <section class="hero" style="padding: 4rem 0;">
        <div class="container">
            <h1>About Our School</h1>
            <p>Building Future Leaders Since 1999</p>
        </div>
    </section>
    
    <!-- About Content -->
    <section class="section">
        <div class="container">
            <div style="max-width: 900px; margin: 0 auto;">
                <h2 style="margin-bottom: 1.5rem; color: var(--primary);">Our Mission</h2>
                <p style="font-size: 1.125rem; line-height: 1.8; margin-bottom: 2rem;">
                    Our mission is to provide a nurturing and stimulating environment where students can develop 
                    their full potential academically, socially, and emotionally. We are committed to fostering 
                    critical thinking, creativity, and a lifelong love of learning in every student.
                </p>
                
                <h2 style="margin-bottom: 1.5rem; color: var(--primary);">Our Vision</h2>
                <p style="font-size: 1.125rem; line-height: 1.8; margin-bottom: 2rem;">
                    We envision a school community where every student is empowered to become a responsible, 
                    compassionate, and innovative leader who contributes positively to society. Through excellence 
                    in education and character development, we prepare students for success in an ever-changing world.
                </p>
                
                <h2 style="margin-bottom: 1.5rem; color: var(--primary);">Our Values</h2>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
                    <div style="padding: 1.5rem; background: var(--light); border-radius: 10px;">
                        <h3 style="color: var(--primary); margin-bottom: 0.5rem;">
                            <i class="fas fa-star"></i> Excellence
                        </h3>
                        <p>We strive for the highest standards in everything we do.</p>
                    </div>
                    
                    <div style="padding: 1.5rem; background: var(--light); border-radius: 10px;">
                        <h3 style="color: var(--primary); margin-bottom: 0.5rem;">
                            <i class="fas fa-heart"></i> Compassion
                        </h3>
                        <p>We treat everyone with kindness, respect, and understanding.</p>
                    </div>
                    
                    <div style="padding: 1.5rem; background: var(--light); border-radius: 10px;">
                        <h3 style="color: var(--primary); margin-bottom: 0.5rem;">
                            <i class="fas fa-lightbulb"></i> Innovation
                        </h3>
                        <p>We embrace creativity and new ideas in teaching and learning.</p>
                    </div>
                    
                    <div style="padding: 1.5rem; background: var(--light); border-radius: 10px;">
                        <h3 style="color: var(--primary); margin-bottom: 0.5rem;">
                            <i class="fas fa-users"></i> Community
                        </h3>
                        <p>We build strong relationships and work together as a team.</p>
                    </div>
                </div>
                
                <h2 style="margin-bottom: 1.5rem; color: var(--primary);">Our History</h2>
                <p style="font-size: 1.125rem; line-height: 1.8; margin-bottom: 2rem;">
                    Founded in 1999, our school has been a cornerstone of quality education in the community for 
                    over 25 years. What started as a small institution with just 50 students has grown into a 
                    thriving educational center serving hundreds of students from diverse backgrounds. Throughout 
                    our journey, we have maintained our commitment to academic excellence, character development, 
                    and holistic education.
                </p>
                
                <h2 style="margin-bottom: 1.5rem; color: var(--primary);">Our Facilities</h2>
                <ul style="font-size: 1.125rem; line-height: 2; margin-bottom: 2rem;">
                    <li><i class="fas fa-check" style="color: var(--success);"></i> Modern, well-equipped classrooms</li>
                    <li><i class="fas fa-check" style="color: var(--success);"></i> State-of-the-art science laboratories</li>
                    <li><i class="fas fa-check" style="color: var(--success);"></i> Comprehensive library with digital resources</li>
                    <li><i class="fas fa-check" style="color: var(--success);"></i> Computer labs with latest technology</li>
                    <li><i class="fas fa-check" style="color: var(--success);"></i> Sports facilities and playground</li>
                    <li><i class="fas fa-check" style="color: var(--success);"></i> Auditorium for events and assemblies</li>
                    <li><i class="fas fa-check" style="color: var(--success);"></i> Cafeteria with nutritious meals</li>
                </ul>
            </div>
        </div>
    </section>
    
    <!-- Call to Action -->
    <section class="section" style="background: linear-gradient(135deg, var(--primary), var(--secondary)); color: white; text-align: center;">
        <div class="container">
            <h2 style="font-size: 2.5rem; margin-bottom: 1rem;">Join Our Community</h2>
            <p style="font-size: 1.25rem; margin-bottom: 2rem; opacity: 0.95;">
                Discover how we can help your child reach their full potential
            </p>
            <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                <a href="<?php echo BASE_URL; ?>/public/admissions.php" class="btn" style="background: white; color: var(--primary);">
                    Apply for Admission
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
