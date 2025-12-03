<?php
/**
 * Public - Academics Page
 */

require_once __DIR__ . '/../config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Academics - <?php echo SITE_NAME; ?></title>
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
                        <li><a href="<?php echo BASE_URL; ?>/public/academics.php" style="opacity: 1; font-weight: 600;">Academics</a></li>
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
            <h1>Academic Excellence</h1>
            <p>Nurturing minds, building futures</p>
        </div>
    </section>
    
    <!-- Curriculum Section -->
    <section class="section">
        <div class="container">
            <div style="max-width: 900px; margin: 0 auto;">
                <h2 style="margin-bottom: 1.5rem; color: var(--primary);">Our Curriculum</h2>
                <p style="font-size: 1.125rem; line-height: 1.8; margin-bottom: 2rem;">
                    We follow a comprehensive curriculum designed to foster intellectual growth, creativity, and critical thinking. 
                    Our academic program is divided into three main levels, each tailored to the developmental needs of students.
                </p>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 2rem; margin-bottom: 3rem;">
                    <div class="card">
                        <div class="card-content">
                            <h3 style="color: var(--primary); margin-bottom: 1rem;">Primary Level (Class 1-5)</h3>
                            <p style="color: #666; margin-bottom: 1rem;">
                                Focus on foundational skills in literacy, numeracy, and social development.
                            </p>
                            <ul style="padding-left: 1.25rem; color: #666;">
                                <li>English & Language Arts</li>
                                <li>Mathematics</li>
                                <li>General Science</li>
                                <li>Social Studies</li>
                                <li>Arts & Crafts</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-content">
                            <h3 style="color: var(--primary); margin-bottom: 1rem;">Middle Level (Class 6-8)</h3>
                            <p style="color: #666; margin-bottom: 1rem;">
                                Introduction to specialized subjects and development of analytical skills.
                            </p>
                            <ul style="padding-left: 1.25rem; color: #666;">
                                <li>Advanced Mathematics</li>
                                <li>Physics, Chemistry, Biology</li>
                                <li>History & Geography</li>
                                <li>Computer Science</li>
                                <li>Foreign Languages</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-content">
                            <h3 style="color: var(--primary); margin-bottom: 1rem;">Secondary Level (Class 9-10)</h3>
                            <p style="color: #666; margin-bottom: 1rem;">
                                Preparation for board exams and higher education with in-depth study.
                            </p>
                            <ul style="padding-left: 1.25rem; color: #666;">
                                <li>Higher Mathematics</li>
                                <li>Pure Sciences</li>
                                <li>Economics & Commerce</li>
                                <li>Information Technology</li>
                                <li>Literature</li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <h2 style="margin-bottom: 1.5rem; color: var(--primary);">Academic Calendar</h2>
                <div class="table-responsive" style="margin-bottom: 3rem;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background: var(--light);">
                                <th style="padding: 1rem; text-align: left;">Term</th>
                                <th style="padding: 1rem; text-align: left;">Duration</th>
                                <th style="padding: 1rem; text-align: left;">Key Events</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr style="border-bottom: 1px solid #eee;">
                                <td style="padding: 1rem;">First Term</td>
                                <td style="padding: 1rem;">January - April</td>
                                <td style="padding: 1rem;">Sports Day, Science Fair, Mid-Term Exams</td>
                            </tr>
                            <tr style="border-bottom: 1px solid #eee;">
                                <td style="padding: 1rem;">Second Term</td>
                                <td style="padding: 1rem;">May - August</td>
                                <td style="padding: 1rem;">Cultural Fest, Summer Break, Assessment Tests</td>
                            </tr>
                            <tr>
                                <td style="padding: 1rem;">Final Term</td>
                                <td style="padding: 1rem;">September - December</td>
                                <td style="padding: 1rem;">Annual Sports, Final Exams, Result Publication</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <h2 style="margin-bottom: 1.5rem; color: var(--primary);">Co-Curricular Activities</h2>
                <p style="font-size: 1.125rem; line-height: 1.8; margin-bottom: 1.5rem;">
                    We believe in holistic development. Our students participate in various clubs and activities:
                </p>
                <div style="display: flex; flex-wrap: wrap; gap: 1rem;">
                    <span style="background: var(--light); padding: 0.5rem 1rem; border-radius: 20px; color: var(--primary); font-weight: 500;">Debate Club</span>
                    <span style="background: var(--light); padding: 0.5rem 1rem; border-radius: 20px; color: var(--primary); font-weight: 500;">Science Club</span>
                    <span style="background: var(--light); padding: 0.5rem 1rem; border-radius: 20px; color: var(--primary); font-weight: 500;">Art & Music</span>
                    <span style="background: var(--light); padding: 0.5rem 1rem; border-radius: 20px; color: var(--primary); font-weight: 500;">Sports Teams</span>
                    <span style="background: var(--light); padding: 0.5rem 1rem; border-radius: 20px; color: var(--primary); font-weight: 500;">Coding Club</span>
                    <span style="background: var(--light); padding: 0.5rem 1rem; border-radius: 20px; color: var(--primary); font-weight: 500;">Scouts</span>
                </div>
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
