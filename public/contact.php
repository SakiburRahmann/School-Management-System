<?php
/**
 * Public - Contact Page
 */

require_once __DIR__ . '/../config.php';

$contactModel = new ContactMessage();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (verifyCSRFToken($_POST['csrf_token'])) {
        $data = [
            'name' => sanitize($_POST['name']),
            'email' => sanitize($_POST['email']),
            'subject' => sanitize($_POST['subject']),
            'message' => sanitize($_POST['message'])
        ];
        
        if (!empty($data['name']) && !empty($data['email']) && !empty($data['message'])) {
            $messageId = $contactModel->create($data);
            if ($messageId) {
                setFlash('success', 'Thank you! Your message has been sent successfully. We will get back to you soon.');
            } else {
                setFlash('danger', 'Failed to send message. Please try again.');
            }
        } else {
            setFlash('danger', 'Please fill in all required fields.');
        }
        redirect(BASE_URL . '/public/contact.php');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - <?php echo SITE_NAME; ?></title>
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
                        <li><a href="<?php echo BASE_URL; ?>/public/contact.php" style="opacity: 1; font-weight: 600;">Contact</a></li>
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
            <h1>Contact Us</h1>
            <p>We'd love to hear from you</p>
        </div>
    </section>
    
    <!-- Contact Section -->
    <section class="section">
        <div class="container">
            <?php if ($flash = getFlash()): ?>
                <div style="max-width: 800px; margin: 0 auto 2rem; padding: 1rem; background: <?php echo $flash['type'] === 'success' ? '#d4edda' : '#f8d7da'; ?>; border-radius: 8px; color: <?php echo $flash['type'] === 'success' ? '#155724' : '#721c24'; ?>;">
                    <?php echo $flash['message']; ?>
                </div>
            <?php endif; ?>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 3rem; max-width: 1200px; margin: 0 auto;">
                <!-- Contact Form -->
                <div>
                    <h2 style="margin-bottom: 1.5rem; color: var(--primary);">Send us a Message</h2>
                    <form method="POST" action="">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        
                        <div style="margin-bottom: 1.5rem;">
                            <label style="display: block; margin-bottom: 0.5rem; font-weight: 500;">
                                Your Name <span style="color: red;">*</span>
                            </label>
                            <input type="text" name="name" required
                                   style="width: 100%; padding: 0.75rem; border: 2px solid var(--light); border-radius: 8px; font-size: 1rem;">
                        </div>
                        
                        <div style="margin-bottom: 1.5rem;">
                            <label style="display: block; margin-bottom: 0.5rem; font-weight: 500;">
                                Your Email <span style="color: red;">*</span>
                            </label>
                            <input type="email" name="email" required
                                   style="width: 100%; padding: 0.75rem; border: 2px solid var(--light); border-radius: 8px; font-size: 1rem;">
                        </div>
                        
                        <div style="margin-bottom: 1.5rem;">
                            <label style="display: block; margin-bottom: 0.5rem; font-weight: 500;">
                                Subject
                            </label>
                            <input type="text" name="subject"
                                   style="width: 100%; padding: 0.75rem; border: 2px solid var(--light); border-radius: 8px; font-size: 1rem;">
                        </div>
                        
                        <div style="margin-bottom: 1.5rem;">
                            <label style="display: block; margin-bottom: 0.5rem; font-weight: 500;">
                                Message <span style="color: red;">*</span>
                            </label>
                            <textarea name="message" rows="6" required
                                      style="width: 100%; padding: 0.75rem; border: 2px solid var(--light); border-radius: 8px; font-size: 1rem; resize: vertical;"></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-primary" style="width: 100%;">
                            <i class="fas fa-paper-plane"></i> Send Message
                        </button>
                    </form>
                </div>
                
                <!-- Contact Information -->
                <div>
                    <h2 style="margin-bottom: 1.5rem; color: var(--primary);">Get in Touch</h2>
                    
                    <div style="display: flex; flex-direction: column; gap: 2rem;">
                        <div style="padding: 1.5rem; background: var(--light); border-radius: 10px;">
                            <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 0.5rem;">
                                <div style="width: 50px; height: 50px; background: linear-gradient(135deg, var(--primary), var(--secondary)); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem;">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                                <div>
                                    <h3 style="margin: 0; color: var(--primary);">Address</h3>
                                    <p style="margin: 0; color: #666;">123 School Street, City, State 12345</p>
                                </div>
                            </div>
                        </div>
                        
                        <div style="padding: 1.5rem; background: var(--light); border-radius: 10px;">
                            <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 0.5rem;">
                                <div style="width: 50px; height: 50px; background: linear-gradient(135deg, var(--primary), var(--secondary)); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem;">
                                    <i class="fas fa-phone"></i>
                                </div>
                                <div>
                                    <h3 style="margin: 0; color: var(--primary);">Phone</h3>
                                    <p style="margin: 0; color: #666;">+1 234 567 8900</p>
                                    <p style="margin: 0; color: #666;">+1 234 567 8901</p>
                                </div>
                            </div>
                        </div>
                        
                        <div style="padding: 1.5rem; background: var(--light); border-radius: 10px;">
                            <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 0.5rem;">
                                <div style="width: 50px; height: 50px; background: linear-gradient(135deg, var(--primary), var(--secondary)); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem;">
                                    <i class="fas fa-envelope"></i>
                                </div>
                                <div>
                                    <h3 style="margin: 0; color: var(--primary);">Email</h3>
                                    <p style="margin: 0; color: #666;">info@school.com</p>
                                    <p style="margin: 0; color: #666;">admissions@school.com</p>
                                </div>
                            </div>
                        </div>
                        
                        <div style="padding: 1.5rem; background: var(--light); border-radius: 10px;">
                            <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 0.5rem;">
                                <div style="width: 50px; height: 50px; background: linear-gradient(135deg, var(--primary), var(--secondary)); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem;">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <div>
                                    <h3 style="margin: 0; color: var(--primary);">Office Hours</h3>
                                    <p style="margin: 0; color: #666;">Monday - Friday: 8:00 AM - 4:00 PM</p>
                                    <p style="margin: 0; color: #666;">Saturday: 9:00 AM - 12:00 PM</p>
                                </div>
                            </div>
                        </div>
                    </div>
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
