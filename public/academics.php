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
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/style.css?v=<?php echo time(); ?>">
</head>
<body>
    <?php require_once __DIR__ . '/../includes/public_header.php'; ?>
    
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
            <div class="page-content">
                <h2><i class="fas fa-book-open"></i> Our Curriculum</h2>
                <p>
                    We follow a comprehensive curriculum designed to foster intellectual growth, creativity, and critical thinking. 
                    Our academic program is divided into three main levels, each tailored to the developmental needs of students.
                </p>
                
                <div class="cards-grid" style="margin-bottom: 2.5rem;">
                    <div class="card">
                        <div class="card-content">
                            <h3 style="color: var(--primary); margin-bottom: 1rem;"><i class="fas fa-seedling"></i> Primary Level (Class 1-5)</h3>
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
                            <h3 style="color: var(--primary); margin-bottom: 1rem;"><i class="fas fa-cogs"></i> Middle Level (Class 6-8)</h3>
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
                            <h3 style="color: var(--primary); margin-bottom: 1rem;"><i class="fas fa-graduation-cap"></i> Secondary Level (Class 9-10)</h3>
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
                
                <h2><i class="fas fa-calendar-alt"></i> Academic Calendar</h2>
                <div class="table-responsive" style="margin-bottom: 2.5rem;">
                    <table class="academic-table">
                        <thead>
                            <tr>
                                <th>Term</th>
                                <th>Duration</th>
                                <th>Key Events</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td data-label="Term">First Term</td>
                                <td data-label="Duration">January - April</td>
                                <td data-label="Events">Sports Day, Science Fair, Mid-Term Exams</td>
                            </tr>
                            <tr>
                                <td data-label="Term">Second Term</td>
                                <td data-label="Duration">May - August</td>
                                <td data-label="Events">Cultural Fest, Summer Break, Assessment Tests</td>
                            </tr>
                            <tr>
                                <td data-label="Term">Final Term</td>
                                <td data-label="Duration">September - December</td>
                                <td data-label="Events">Annual Sports, Final Exams, Result Publication</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <h2><i class="fas fa-puzzle-piece"></i> Co-Curricular Activities</h2>
                <p>
                    We believe in holistic development. Our students participate in various clubs and activities:
                </p>
                <div class="activity-tags">
                    <span class="activity-tag"><i class="fas fa-microphone"></i> Debate Club</span>
                    <span class="activity-tag"><i class="fas fa-flask"></i> Science Club</span>
                    <span class="activity-tag"><i class="fas fa-palette"></i> Art & Music</span>
                    <span class="activity-tag"><i class="fas fa-futbol"></i> Sports Teams</span>
                    <span class="activity-tag"><i class="fas fa-laptop-code"></i> Coding Club</span>
                    <span class="activity-tag"><i class="fas fa-campground"></i> Scouts</span>
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
