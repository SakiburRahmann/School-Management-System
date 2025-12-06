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
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/style.css?v=<?php echo time(); ?>">
</head>
<body>
    <?php require_once __DIR__ . '/../includes/public_header.php'; ?>
    
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
                <div class="alert-message <?php echo $flash['type']; ?>">
                    <?php echo $flash['message']; ?>
                </div>
            <?php endif; ?>
            
            <div class="contact-grid">
                <!-- Contact Form -->
                <div class="contact-form-card">
                    <h2><i class="fas fa-envelope-open-text"></i> Send us a Message</h2>
                    <form method="POST" action="" class="contact-form">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        
                        <div class="form-group">
                            <label>Your Name <span class="required">*</span></label>
                            <input type="text" name="name" required placeholder="Enter your full name">
                        </div>
                        
                        <div class="form-group">
                            <label>Your Email <span class="required">*</span></label>
                            <input type="email" name="email" required placeholder="Enter your email address">
                        </div>
                        
                        <div class="form-group">
                            <label>Subject</label>
                            <input type="text" name="subject" placeholder="What is this about?">
                        </div>
                        
                        <div class="form-group">
                            <label>Message <span class="required">*</span></label>
                            <textarea name="message" rows="5" required placeholder="Write your message here..."></textarea>
                        </div>
                        
                        <button type="submit" class="submit-btn">
                            <i class="fas fa-paper-plane"></i> Send Message
                        </button>
                    </form>
                </div>
                
                <!-- Contact Information -->
                <div class="contact-info">
                    <h2>Get in Touch</h2>
                    
                    <div class="info-cards">
                        <div class="info-card">
                            <div class="info-icon">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div class="info-content">
                                <h3>Address</h3>
                                <p>123 School Street, City, State 12345</p>
                            </div>
                        </div>
                        
                        <div class="info-card">
                            <div class="info-icon">
                                <i class="fas fa-phone"></i>
                            </div>
                            <div class="info-content">
                                <h3>Phone</h3>
                                <p>+1 234 567 8900</p>
                                <p>+1 234 567 8901</p>
                            </div>
                        </div>
                        
                        <div class="info-card">
                            <div class="info-icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div class="info-content">
                                <h3>Email</h3>
                                <p>info@school.com</p>
                                <p>admissions@school.com</p>
                            </div>
                        </div>
                        
                        <div class="info-card">
                            <div class="info-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="info-content">
                                <h3>Office Hours</h3>
                                <p>Monday - Friday: 8:00 AM - 4:00 PM</p>
                                <p>Saturday: 9:00 AM - 12:00 PM</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <style>
    /* Contact Page Styles */
    .alert-message {
        max-width: 800px;
        margin: 0 auto 2rem;
        padding: 1rem 1.5rem;
        border-radius: 12px;
        font-weight: 500;
    }
    .alert-message.success { background: #d4edda; color: #155724; }
    .alert-message.danger { background: #f8d7da; color: #721c24; }
    
    .contact-grid {
        display: grid;
        grid-template-columns: 1.2fr 1fr;
        gap: 3rem;
        max-width: 1100px;
        margin: 0 auto;
    }
    
    .contact-form-card {
        background: white;
        border-radius: 16px;
        padding: 2rem;
        box-shadow: 0 10px 40px rgba(0,0,0,0.1);
    }
    
    .contact-form-card h2,
    .contact-info h2 {
        color: var(--primary);
        margin-bottom: 1.5rem;
        font-size: 1.5rem;
    }
    
    .contact-form .form-group {
        margin-bottom: 1.25rem;
    }
    
    .contact-form label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 500;
        color: var(--dark);
        font-size: 0.95rem;
    }
    
    .contact-form .required {
        color: #ef4444;
    }
    
    .contact-form input,
    .contact-form textarea {
        width: 100%;
        padding: 0.875rem 1rem;
        border: 2px solid #e5e7eb;
        border-radius: 10px;
        font-size: 16px;
        transition: all 0.3s;
        background: #fafafa;
        font-family: inherit;
    }
    
    .contact-form input:focus,
    .contact-form textarea:focus {
        outline: none;
        border-color: var(--primary);
        background: white;
        box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
    }
    
    .contact-form textarea {
        resize: vertical;
        min-height: 120px;
    }
    
    .submit-btn {
        width: 100%;
        padding: 1rem 2rem;
        background: linear-gradient(135deg, var(--primary), var(--secondary));
        color: white;
        border: none;
        border-radius: 12px;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
    }
    
    .submit-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
    }
    
    .submit-btn i {
        margin-right: 0.5rem;
    }
    
    .info-cards {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }
    
    .info-card {
        display: flex;
        align-items: flex-start;
        gap: 1rem;
        padding: 1.25rem;
        background: var(--light);
        border-radius: 12px;
        transition: all 0.3s;
    }
    
    .info-card:hover {
        transform: translateX(5px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    }
    
    .info-icon {
        width: 48px;
        height: 48px;
        min-width: 48px;
        background: linear-gradient(135deg, var(--primary), var(--secondary));
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.25rem;
    }
    
    .info-content h3 {
        margin: 0 0 0.25rem 0;
        color: var(--primary);
        font-size: 0.95rem;
    }
    
    .info-content p {
        margin: 0;
        color: #666;
        font-size: 0.9rem;
        line-height: 1.6;
    }
    
    /* Mobile Responsive */
    @media (max-width: 900px) {
        .contact-grid {
            grid-template-columns: 1fr;
            gap: 2rem;
        }
    }
    
    @media (max-width: 600px) {
        .contact-form-card {
            padding: 1.5rem;
            border-radius: 12px;
        }
        
        .contact-form-card h2,
        .contact-info h2 {
            font-size: 1.25rem;
        }
        
        .info-card {
            padding: 1rem;
        }
        
        .info-icon {
            width: 44px;
            height: 44px;
            min-width: 44px;
            font-size: 1.1rem;
        }
        
        .info-content h3 {
            font-size: 0.9rem;
        }
        
        .info-content p {
            font-size: 0.85rem;
        }
    }
    </style>
    
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
