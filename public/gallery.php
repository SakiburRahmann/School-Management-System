<?php
/**
 * Public - Gallery Page
 */

require_once __DIR__ . '/../config.php';

$galleryModel = new Gallery();

// Get all gallery items
$images = $galleryModel->getAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gallery - <?php echo SITE_NAME; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/style.css?v=<?php echo time(); ?>">
    <style>
        /* Gallery touch support - always show captions on mobile */
        @media (max-width: 767px) {
            .gallery-item .gallery-caption {
                background: linear-gradient(to top, rgba(0,0,0,0.8), transparent);
            }
        }
    </style>
</head>
<body>
    <?php require_once __DIR__ . '/../includes/public_header.php'; ?>
    
    <!-- Page Header -->
    <section class="hero" style="padding: 4rem 0;">
        <div class="container">
            <h1>Photo Gallery</h1>
            <p>Glimpses of life at our school</p>
        </div>
    </section>
    
    <!-- Gallery Section -->
    <section class="section">
        <div class="container">
            <?php if (!empty($images)): ?>
                <div class="gallery-grid">
                    <?php foreach ($images as $image): ?>
                        <div class="gallery-item">
                            <img src="<?php echo BASE_URL . '/uploads/gallery/' . $image['image_path']; ?>" 
                                 alt="<?php echo htmlspecialchars($image['title']); ?>">
                            <div class="gallery-caption">
                                <h3 style="margin: 0 0 0.25rem 0; font-size: 1.1rem;"><?php echo htmlspecialchars($image['title']); ?></h3>
                                <p style="margin: 0; font-size: 0.9rem; opacity: 0.9;"><?php echo htmlspecialchars($image['category']); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <!-- Placeholder Gallery for Demo -->
                <div class="gallery-grid">
                    <div class="gallery-item">
                        <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; background: #ddd; color: #666; font-size: 3rem;">
                            <i class="fas fa-school"></i>
                        </div>
                        <div class="gallery-caption">
                            <h3>School Campus</h3>
                            <p>Campus</p>
                        </div>
                    </div>
                    <div class="gallery-item">
                        <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; background: #ddd; color: #666; font-size: 3rem;">
                            <i class="fas fa-flask"></i>
                        </div>
                        <div class="gallery-caption">
                            <h3>Science Lab</h3>
                            <p>Facilities</p>
                        </div>
                    </div>
                    <div class="gallery-item">
                        <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; background: #ddd; color: #666; font-size: 3rem;">
                            <i class="fas fa-book-reader"></i>
                        </div>
                        <div class="gallery-caption">
                            <h3>Library</h3>
                            <p>Facilities</p>
                        </div>
                    </div>
                    <div class="gallery-item">
                        <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; background: #ddd; color: #666; font-size: 3rem;">
                            <i class="fas fa-futbol"></i>
                        </div>
                        <div class="gallery-caption">
                            <h3>Sports Day</h3>
                            <p>Events</p>
                        </div>
                    </div>
                    <div class="gallery-item">
                        <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; background: #ddd; color: #666; font-size: 3rem;">
                            <i class="fas fa-graduation-cap"></i>
                        </div>
                        <div class="gallery-caption">
                            <h3>Graduation</h3>
                            <p>Events</p>
                        </div>
                    </div>
                    <div class="gallery-item">
                        <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; background: #ddd; color: #666; font-size: 3rem;">
                            <i class="fas fa-chalkboard-teacher"></i>
                        </div>
                        <div class="gallery-caption">
                            <h3>Classroom</h3>
                            <p>Academic</p>
                        </div>
                    </div>
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
