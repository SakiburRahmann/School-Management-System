<?php
/**
 * Public - Admissions Page
 */

require_once __DIR__ . '/../config.php';

$admissionModel = new AdmissionRequest();
$classModel = new ClassModel();

// Get classes for dropdown
$classes = $classModel->findAll('class_name');

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (verifyCSRFToken($_POST['csrf_token'])) {
        $data = [
            'student_name' => sanitize($_POST['student_name']),
            'date_of_birth' => $_POST['date_of_birth'],
            'gender' => $_POST['gender'],
            'class_id' => $_POST['class_id'],
            'guardian_name' => sanitize($_POST['guardian_name']),
            'guardian_phone' => sanitize($_POST['guardian_phone']),
            'guardian_email' => sanitize($_POST['guardian_email']),
            'address' => sanitize($_POST['address']),
            'previous_school' => sanitize($_POST['previous_school'])
        ];
        
        if ($admissionModel->create($data)) {
            setFlash('success', 'Application submitted successfully! We will contact you soon.');
            redirect(BASE_URL . '/public/admissions.php');
        } else {
            setFlash('danger', 'Failed to submit application. Please try again.');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admissions - <?php echo SITE_NAME; ?></title>
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
                        <li><a href="<?php echo BASE_URL; ?>/public/admissions.php" style="opacity: 1; font-weight: 600;">Admissions</a></li>
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
            <h1>Admissions Open</h1>
            <p>Join our academic community for the upcoming session</p>
        </div>
    </section>
    
    <!-- Admission Content -->
    <section class="section">
        <div class="container">
            <?php if ($flash = getFlash()): ?>
                <div style="max-width: 800px; margin: 0 auto 2rem; padding: 1rem; background: <?php echo $flash['type'] === 'success' ? '#d4edda' : '#f8d7da'; ?>; border-radius: 8px; color: <?php echo $flash['type'] === 'success' ? '#155724' : '#721c24'; ?>;">
                    <?php echo $flash['message']; ?>
                </div>
            <?php endif; ?>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 3rem; max-width: 1200px; margin: 0 auto;">
                <!-- Information -->
                <div>
                    <h2 style="margin-bottom: 1.5rem; color: var(--primary);">Admission Process</h2>
                    <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                        <div style="display: flex; gap: 1rem;">
                            <div style="width: 40px; height: 40px; background: var(--primary); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold;">1</div>
                            <div>
                                <h3 style="margin: 0 0 0.5rem 0;">Submit Application</h3>
                                <p style="margin: 0; color: #666;">Fill out the online application form with accurate details.</p>
                            </div>
                        </div>
                        
                        <div style="display: flex; gap: 1rem;">
                            <div style="width: 40px; height: 40px; background: var(--primary); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold;">2</div>
                            <div>
                                <h3 style="margin: 0 0 0.5rem 0;">Document Verification</h3>
                                <p style="margin: 0; color: #666;">Submit required documents (Birth Certificate, Previous Report Card, etc.) at the school office.</p>
                            </div>
                        </div>
                        
                        <div style="display: flex; gap: 1rem;">
                            <div style="width: 40px; height: 40px; background: var(--primary); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold;">3</div>
                            <div>
                                <h3 style="margin: 0 0 0.5rem 0;">Admission Test/Interview</h3>
                                <p style="margin: 0; color: #666;">Attend the scheduled admission test or interview.</p>
                            </div>
                        </div>
                        
                        <div style="display: flex; gap: 1rem;">
                            <div style="width: 40px; height: 40px; background: var(--primary); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold;">4</div>
                            <div>
                                <h3 style="margin: 0 0 0.5rem 0;">Final Selection</h3>
                                <p style="margin: 0; color: #666;">Selected candidates will be notified via email/phone.</p>
                            </div>
                        </div>
                    </div>
                    
                    <div style="margin-top: 3rem; padding: 2rem; background: var(--light); border-radius: 10px;">
                        <h3 style="margin-bottom: 1rem; color: var(--primary);">Required Documents</h3>
                        <ul style="list-style: none; padding: 0;">
                            <li style="margin-bottom: 0.5rem;"><i class="fas fa-check" style="color: var(--success); margin-right: 0.5rem;"></i> Birth Certificate</li>
                            <li style="margin-bottom: 0.5rem;"><i class="fas fa-check" style="color: var(--success); margin-right: 0.5rem;"></i> 2 Passport Size Photos</li>
                            <li style="margin-bottom: 0.5rem;"><i class="fas fa-check" style="color: var(--success); margin-right: 0.5rem;"></i> Previous School Transfer Certificate</li>
                            <li style="margin-bottom: 0.5rem;"><i class="fas fa-check" style="color: var(--success); margin-right: 0.5rem;"></i> Guardian's ID Copy</li>
                        </ul>
                    </div>
                </div>
                
                <!-- Application Form -->
                <div class="card">
                    <div class="card-content">
                        <h2 style="margin-bottom: 1.5rem; color: var(--primary);">Online Application</h2>
                        <form method="POST" action="">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                            
                            <h4 style="margin-bottom: 1rem; color: #666;">Student Information</h4>
                            <div class="form-group" style="margin-bottom: 1rem;">
                                <label>Full Name <span style="color: red;">*</span></label>
                                <input type="text" name="student_name" required class="form-control" style="width: 100%; padding: 0.5rem;">
                            </div>
                            
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                                <div class="form-group">
                                    <label>Date of Birth <span style="color: red;">*</span></label>
                                    <input type="date" name="date_of_birth" required class="form-control" style="width: 100%; padding: 0.5rem;">
                                </div>
                                <div class="form-group">
                                    <label>Gender <span style="color: red;">*</span></label>
                                    <select name="gender" required class="form-control" style="width: 100%; padding: 0.5rem;">
                                        <option value="">Select</option>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="form-group" style="margin-bottom: 1rem;">
                                <label>Applying For Class <span style="color: red;">*</span></label>
                                <select name="class_id" required class="form-control" style="width: 100%; padding: 0.5rem;">
                                    <option value="">Select Class</option>
                                    <?php foreach ($classes as $class): ?>
                                        <option value="<?php echo $class['class_id']; ?>">
                                            <?php echo htmlspecialchars($class['class_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <h4 style="margin: 1.5rem 0 1rem 0; color: #666;">Guardian Information</h4>
                            <div class="form-group" style="margin-bottom: 1rem;">
                                <label>Guardian Name <span style="color: red;">*</span></label>
                                <input type="text" name="guardian_name" required class="form-control" style="width: 100%; padding: 0.5rem;">
                            </div>
                            
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                                <div class="form-group">
                                    <label>Phone <span style="color: red;">*</span></label>
                                    <input type="tel" name="guardian_phone" required class="form-control" style="width: 100%; padding: 0.5rem;">
                                </div>
                                <div class="form-group">
                                    <label>Email</label>
                                    <input type="email" name="guardian_email" class="form-control" style="width: 100%; padding: 0.5rem;">
                                </div>
                            </div>
                            
                            <div class="form-group" style="margin-bottom: 1rem;">
                                <label>Address <span style="color: red;">*</span></label>
                                <textarea name="address" required rows="2" class="form-control" style="width: 100%; padding: 0.5rem;"></textarea>
                            </div>
                            
                            <div class="form-group" style="margin-bottom: 1.5rem;">
                                <label>Previous School (if any)</label>
                                <input type="text" name="previous_school" class="form-control" style="width: 100%; padding: 0.5rem;">
                            </div>
                            
                            <button type="submit" class="btn btn-primary" style="width: 100%;">
                                Submit Application
                            </button>
                        </form>
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
