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
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/style.css?v=<?php echo time(); ?>">
</head>
<body>
    <?php require_once __DIR__ . '/../includes/public_header.php'; ?>
    
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
                <div class="alert-message <?php echo $flash['type']; ?>">
                    <?php echo $flash['message']; ?>
                </div>
            <?php endif; ?>
            
            <div class="admission-grid">
                <!-- Information -->
                <div class="admission-info">
                    <h2>Admission Process</h2>
                    <div class="process-steps">
                        <div class="step-item">
                            <div class="step-number">1</div>
                            <div class="step-content">
                                <h3>Submit Application</h3>
                                <p>Fill out the online application form with accurate details.</p>
                            </div>
                        </div>
                        
                        <div class="step-item">
                            <div class="step-number">2</div>
                            <div class="step-content">
                                <h3>Document Verification</h3>
                                <p>Submit required documents at the school office.</p>
                            </div>
                        </div>
                        
                        <div class="step-item">
                            <div class="step-number">3</div>
                            <div class="step-content">
                                <h3>Admission Test/Interview</h3>
                                <p>Attend the scheduled admission test or interview.</p>
                            </div>
                        </div>
                        
                        <div class="step-item">
                            <div class="step-number">4</div>
                            <div class="step-content">
                                <h3>Final Selection</h3>
                                <p>Selected candidates will be notified via email/phone.</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="documents-box">
                        <h3><i class="fas fa-file-alt"></i> Required Documents</h3>
                        <ul>
                            <li><i class="fas fa-check"></i> Birth Certificate</li>
                            <li><i class="fas fa-check"></i> 2 Passport Size Photos</li>
                            <li><i class="fas fa-check"></i> Previous School Transfer Certificate</li>
                            <li><i class="fas fa-check"></i> Guardian's ID Copy</li>
                        </ul>
                    </div>
                </div>
                
                <!-- Application Form -->
                <div class="admission-form-card">
                    <h2><i class="fas fa-edit"></i> Online Application</h2>
                    <form method="POST" action="" class="admission-form">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        
                        <div class="form-section-title">
                            <i class="fas fa-user"></i> Student Information
                        </div>
                        
                        <div class="form-group">
                            <label>Full Name <span class="required">*</span></label>
                            <input type="text" name="student_name" required placeholder="Enter student's full name">
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Date of Birth <span class="required">*</span></label>
                                <input type="date" name="date_of_birth" required>
                            </div>
                            <div class="form-group">
                                <label>Gender <span class="required">*</span></label>
                                <select name="gender" required>
                                    <option value="">Select Gender</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Applying For Class <span class="required">*</span></label>
                            <select name="class_id" required>
                                <option value="">Select Class</option>
                                <?php foreach ($classes as $class): ?>
                                    <option value="<?php echo $class['class_id']; ?>">
                                        <?php echo htmlspecialchars($class['class_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-section-title">
                            <i class="fas fa-users"></i> Guardian Information
                        </div>
                        
                        <div class="form-group">
                            <label>Guardian Name <span class="required">*</span></label>
                            <input type="text" name="guardian_name" required placeholder="Enter guardian's name">
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Phone <span class="required">*</span></label>
                                <input type="tel" name="guardian_phone" required placeholder="Phone number">
                            </div>
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" name="guardian_email" placeholder="Email (optional)">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Address <span class="required">*</span></label>
                            <textarea name="address" required rows="2" placeholder="Complete address"></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label>Previous School (if any)</label>
                            <input type="text" name="previous_school" placeholder="Previous school name">
                        </div>
                        
                        <button type="submit" class="submit-btn">
                            <i class="fas fa-paper-plane"></i> Submit Application
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>
    
    <style>
    /* Admissions Page Styles */
    .alert-message {
        max-width: 800px;
        margin: 0 auto 2rem;
        padding: 1rem 1.5rem;
        border-radius: 12px;
        font-weight: 500;
    }
    .alert-message.success { background: #d4edda; color: #155724; }
    .alert-message.danger { background: #f8d7da; color: #721c24; }
    
    .admission-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 3rem;
        max-width: 1200px;
        margin: 0 auto;
    }
    
    .admission-info h2,
    .admission-form-card h2 {
        color: var(--primary);
        margin-bottom: 1.5rem;
        font-size: 1.5rem;
    }
    
    .process-steps {
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
    }
    
    .step-item {
        display: flex;
        gap: 1rem;
        align-items: flex-start;
    }
    
    .step-number {
        width: 40px;
        height: 40px;
        min-width: 40px;
        background: linear-gradient(135deg, var(--primary), var(--secondary));
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 1rem;
    }
    
    .step-content h3 {
        margin: 0 0 0.25rem 0;
        font-size: 1rem;
        color: var(--dark);
    }
    
    .step-content p {
        margin: 0;
        color: #666;
        font-size: 0.9rem;
        line-height: 1.5;
    }
    
    .documents-box {
        margin-top: 2rem;
        padding: 1.5rem;
        background: var(--light);
        border-radius: 12px;
    }
    
    .documents-box h3 {
        color: var(--primary);
        margin-bottom: 1rem;
        font-size: 1.1rem;
    }
    
    .documents-box ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    
    .documents-box li {
        padding: 0.5rem 0;
        color: #444;
        font-size: 0.95rem;
    }
    
    .documents-box li i {
        color: var(--success);
        margin-right: 0.5rem;
    }
    
    .admission-form-card {
        background: white;
        border-radius: 16px;
        padding: 2rem;
        box-shadow: 0 10px 40px rgba(0,0,0,0.1);
    }
    
    .form-section-title {
        color: #666;
        font-weight: 600;
        font-size: 0.9rem;
        margin: 1.5rem 0 1rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid var(--light);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .form-section-title:first-of-type {
        margin-top: 0;
    }
    
    .form-section-title i {
        margin-right: 0.5rem;
    }
    
    .admission-form .form-group {
        margin-bottom: 1rem;
    }
    
    .admission-form label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 500;
        color: var(--dark);
        font-size: 0.9rem;
    }
    
    .admission-form .required {
        color: #ef4444;
    }
    
    .admission-form input,
    .admission-form select,
    .admission-form textarea {
        width: 100%;
        padding: 0.875rem 1rem;
        border: 2px solid #e5e7eb;
        border-radius: 10px;
        font-size: 16px;
        transition: all 0.3s;
        background: #fafafa;
    }
    
    .admission-form input:focus,
    .admission-form select:focus,
    .admission-form textarea:focus {
        outline: none;
        border-color: var(--primary);
        background: white;
        box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
    }
    
    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
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
        margin-top: 1rem;
    }
    
    .submit-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
    }
    
    .submit-btn i {
        margin-right: 0.5rem;
    }
    
    /* Mobile Responsive */
    @media (max-width: 900px) {
        .admission-grid {
            grid-template-columns: 1fr;
            gap: 2rem;
        }
        
        .admission-form-card {
            order: -1;
        }
    }
    
    @media (max-width: 600px) {
        .admission-form-card {
            padding: 1.5rem;
            border-radius: 12px;
        }
        
        .form-row {
            grid-template-columns: 1fr;
            gap: 0;
        }
        
        .step-number {
            width: 36px;
            height: 36px;
            min-width: 36px;
            font-size: 0.9rem;
        }
        
        .step-content h3 {
            font-size: 0.95rem;
        }
        
        .step-content p {
            font-size: 0.85rem;
        }
        
        .documents-box {
            padding: 1.25rem;
        }
        
        .admission-info h2,
        .admission-form-card h2 {
            font-size: 1.25rem;
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
