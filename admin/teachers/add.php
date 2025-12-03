<?php
/**
 * Admin - Add Teacher
 */

$pageTitle = 'Add New Teacher';
require_once __DIR__ . '/../../includes/admin_header.php';

$teacherModel = new Teacher();
$userModel = new User();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'])) {
        setFlash('danger', 'Invalid request.');
        redirect(BASE_URL . '/admin/teachers/add.php');
    }
    
    $data = [
        'name' => sanitize($_POST['name']),
        'subject_speciality' => sanitize($_POST['subject_speciality']),
        'email' => sanitize($_POST['email']),
        'phone' => sanitize($_POST['phone']),
        'contact_details' => sanitize($_POST['contact_details']),
        'address' => sanitize($_POST['address'])
    ];
    
    if (empty($data['name'])) {
        setFlash('danger', 'Teacher name is required.');
        redirect(BASE_URL . '/admin/teachers/add.php');
    }
    
    $teacherId = $teacherModel->create($data);
    
    if ($teacherId) {
        // Create user account if requested
        if (isset($_POST['create_account']) && $_POST['create_account'] == '1') {
            $username = strtolower(str_replace(' ', '', $data['name'])) . $teacherId;
            $password = generatePassword();
            
            $userModel->createUser([
                'username' => $username,
                'password' => $password,
                'role' => 'Teacher',
                'related_id' => $teacherId
            ]);
            
            setFlash('success', "Teacher added successfully! Login: {$username} / {$password}");
        } else {
            setFlash('success', 'Teacher added successfully!');
        }
        
        redirect(BASE_URL . '/admin/teachers/');
    } else {
        setFlash('danger', 'Failed to add teacher.');
    }
}
?>

<div class="card">
    <div class="card-header">
        <h3>Add New Teacher</h3>
        <a href="<?php echo BASE_URL; ?>/admin/teachers/" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to List
        </a>
    </div>
    
    <div class="card-body">
        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            
            <h4 style="margin-bottom: 1rem; color: var(--primary);">Personal Information</h4>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                <div class="form-group">
                    <label for="name">Full Name <span style="color: red;">*</span></label>
                    <input type="text" id="name" name="name" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="subject_speciality">Subject Specialization</label>
                    <input type="text" id="subject_speciality" name="subject_speciality" class="form-control" 
                           placeholder="e.g., Mathematics, Science">
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-control">
                </div>
                
                <div class="form-group">
                    <label for="phone">Phone</label>
                    <input type="text" id="phone" name="phone" class="form-control">
                </div>
            </div>
            
            <div class="form-group">
                <label for="contact_details">Additional Contact Details</label>
                <input type="text" id="contact_details" name="contact_details" class="form-control">
            </div>
            
            <div class="form-group">
                <label for="address">Address</label>
                <textarea id="address" name="address" class="form-control" rows="3"></textarea>
            </div>
            
            <h4 style="margin: 2rem 0 1rem; color: var(--primary);">User Account</h4>
            
            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="create_account" value="1">
                    <span>Create user account for teacher portal access</span>
                </label>
                <small style="display: block; margin-top: 0.5rem; color: #666;">
                    If checked, a username and password will be automatically generated.
                </small>
            </div>
            
            <div style="margin-top: 2rem; display: flex; gap: 1rem;">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Add Teacher
                </button>
                <a href="<?php echo BASE_URL; ?>/admin/teachers/" class="btn btn-secondary">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/admin_footer.php'; ?>
