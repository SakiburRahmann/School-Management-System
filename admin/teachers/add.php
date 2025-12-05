<?php
require_once __DIR__ . '/../../config.php';

/**
 * Admin - Add Teacher
 */

$pageTitle = 'Add New Teacher';

$teacherModel = new Teacher();
$userModel = new User();

$errors = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'])) {
        setFlash('danger', 'Invalid request.');
        redirect(BASE_URL . '/admin/teachers/add.php');
    }
    
    $teacherIdCustom = trim(sanitize($_POST['teacher_id_custom']));
    $name = trim(sanitize($_POST['name']));
    
    $data = [
        'name' => $name,
        'teacher_id_custom' => $teacherIdCustom,
        'qualification' => sanitize($_POST['qualification']),
        'subject_speciality' => sanitize($_POST['subject_speciality']),
        'email' => sanitize($_POST['email']),
        'phone' => sanitize($_POST['phone']),
        'contact_details' => sanitize($_POST['contact_details']),
        'address' => sanitize($_POST['address'])
    ];
    
    // Validate required fields
    if (empty($data['name'])) {
        $errors['name'] = 'Teacher name is required.';
    }
    
    if (empty($data['teacher_id_custom'])) {
        $errors['teacher_id_custom'] = 'Teacher ID is required.';
    } elseif ($teacherModel->teacherIdExists($data['teacher_id_custom'])) {
        $errors['teacher_id_custom'] = 'This Teacher ID already exists. Please use a unique ID.';
    }
    
    if (!empty($errors)) {
        // Store errors in session for display
        $_SESSION['form_errors'] = $errors;
        $_SESSION['form_data'] = $data;
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

// Get any stored errors/data from previous submission
$errors = $_SESSION['form_errors'] ?? [];
$formData = $_SESSION['form_data'] ?? [];
unset($_SESSION['form_errors'], $_SESSION['form_data']);

require_once __DIR__ . '/../../includes/admin_header.php';
?>

<div class="card">
    <div class="card-header">
        <h3>Add New Teacher</h3>
        <a href="<?php echo BASE_URL; ?>/admin/teachers/" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to List
        </a>
    </div>
    
    <div class="card-body">
        <?php if (!empty($errors)): ?>
        <div class="validation-alert validation-alert-danger">
            <div class="validation-alert-icon">
                <i class="fas fa-exclamation-circle"></i>
            </div>
            <div class="validation-alert-content">
                <strong>Please fix the following errors:</strong>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <button class="validation-alert-close" onclick="this.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <?php endif; ?>
        
        <form method="POST" action="" id="teacherForm" novalidate>
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            
            <h4 style="margin-bottom: 1rem; color: var(--primary);">Personal Information</h4>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                <div class="form-group <?php echo isset($errors['name']) ? 'has-error' : ''; ?>">
                    <label for="name">Full Name <span class="required-star">*</span></label>
                    <input type="text" id="name" name="name" class="form-control" 
                           value="<?php echo htmlspecialchars($formData['name'] ?? ''); ?>"
                           placeholder="Enter teacher's full name" required>
                    <div class="field-error" id="name-error">
                        <?php echo isset($errors['name']) ? htmlspecialchars($errors['name']) : ''; ?>
                    </div>
                </div>

                <div class="form-group <?php echo isset($errors['teacher_id_custom']) ? 'has-error' : ''; ?>">
                    <label for="teacher_id_custom">Teacher ID <span class="required-star">*</span></label>
                    <input type="text" id="teacher_id_custom" name="teacher_id_custom" class="form-control" 
                           value="<?php echo htmlspecialchars($formData['teacher_id_custom'] ?? ''); ?>"
                           placeholder="e.g., T001, TCH2024001" required>
                    <div class="field-error" id="teacher_id_custom-error">
                        <?php echo isset($errors['teacher_id_custom']) ? htmlspecialchars($errors['teacher_id_custom']) : ''; ?>
                    </div>
                    <small class="text-muted">Unique identifier for this teacher</small>
                </div>

                <div class="form-group">
                    <label for="qualification">Qualification</label>
                    <input type="text" id="qualification" name="qualification" class="form-control" 
                           value="<?php echo htmlspecialchars($formData['qualification'] ?? ''); ?>"
                           placeholder="e.g., MSc in Physics, BEd">
                </div>
                
                <div class="form-group">
                    <label for="subject_speciality">Subject Specialization</label>
                    <input type="text" id="subject_speciality" name="subject_speciality" class="form-control" 
                           value="<?php echo htmlspecialchars($formData['subject_speciality'] ?? ''); ?>"
                           placeholder="e.g., Mathematics, Science">
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-control"
                           value="<?php echo htmlspecialchars($formData['email'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="phone">Phone</label>
                    <input type="text" id="phone" name="phone" class="form-control"
                           value="<?php echo htmlspecialchars($formData['phone'] ?? ''); ?>">
                </div>
            </div>
            
            <div class="form-group">
                <label for="contact_details">Additional Contact Details</label>
                <input type="text" id="contact_details" name="contact_details" class="form-control"
                       value="<?php echo htmlspecialchars($formData['contact_details'] ?? ''); ?>">
            </div>
            
            <div class="form-group">
                <label for="address">Address</label>
                <textarea id="address" name="address" class="form-control" rows="3"><?php echo htmlspecialchars($formData['address'] ?? ''); ?></textarea>
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
                <button type="submit" class="btn btn-primary" id="submitBtn">
                    <i class="fas fa-save"></i> Add Teacher
                </button>
                <a href="<?php echo BASE_URL; ?>/admin/teachers/" class="btn btn-secondary">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('teacherForm');
    const requiredFields = [
        { id: 'name', label: 'Full Name' },
        { id: 'teacher_id_custom', label: 'Teacher ID' }
    ];
    
    // Real-time validation on input
    requiredFields.forEach(field => {
        const input = document.getElementById(field.id);
        const errorDiv = document.getElementById(field.id + '-error');
        
        input.addEventListener('input', function() {
            const formGroup = this.closest('.form-group');
            if (this.value.trim() !== '') {
                formGroup.classList.remove('has-error');
                formGroup.classList.add('is-valid');
                errorDiv.textContent = '';
            } else {
                formGroup.classList.remove('is-valid');
            }
        });
        
        input.addEventListener('blur', function() {
            validateField(this, field.label);
        });
    });
    
    // Form submission validation
    form.addEventListener('submit', function(e) {
        let isValid = true;
        
        requiredFields.forEach(field => {
            const input = document.getElementById(field.id);
            if (!validateField(input, field.label)) {
                isValid = false;
            }
        });
        
        if (!isValid) {
            e.preventDefault();
            showToast('Please fill in all required fields', 'danger');
            
            // Scroll to first error
            const firstError = form.querySelector('.has-error');
            if (firstError) {
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
    });
    
    function validateField(input, label) {
        const formGroup = input.closest('.form-group');
        const errorDiv = document.getElementById(input.id + '-error');
        
        if (input.value.trim() === '') {
            formGroup.classList.add('has-error');
            formGroup.classList.remove('is-valid');
            errorDiv.textContent = label + ' is required';
            
            // Add shake animation
            formGroup.classList.add('shake');
            setTimeout(() => formGroup.classList.remove('shake'), 500);
            
            return false;
        }
        
        formGroup.classList.remove('has-error');
        formGroup.classList.add('is-valid');
        errorDiv.textContent = '';
        return true;
    }
    
    // Show existing errors with animation on page load
    <?php if (!empty($errors)): ?>
    document.querySelectorAll('.has-error').forEach(el => {
        el.classList.add('shake');
        setTimeout(() => el.classList.remove('shake'), 500);
    });
    showToast('Please fix the errors below', 'danger');
    <?php endif; ?>
});
</script>

<?php require_once __DIR__ . '/../../includes/admin_footer.php'; ?>

