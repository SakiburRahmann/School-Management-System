<?php
/**
 * Admin - Add Student
 * Form to add new student
 */

require_once __DIR__ . '/../../config.php';

$studentModel = new Student();
$classModel = new ClassModel();
$userModel = new User();

$errors = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'])) {
        setFlash('danger', 'Invalid request.');
        redirect(BASE_URL . '/admin/students/add.php');
    }
    
    $studentIdCustom = trim(sanitize($_POST['student_id_custom']));
    $name = trim(sanitize($_POST['name']));
    
    $data = [
        'name' => $name,
        'student_id_custom' => $studentIdCustom,
        'class_id' => $_POST['class_id'] ?: null,
        'section_id' => $_POST['section_id'] ?: null,
        'roll_number' => $_POST['roll_number'] ?: null,
        'date_of_birth' => $_POST['date_of_birth'] ?: null,
        'gender' => $_POST['gender'] ?: null,
        'guardian_name' => sanitize($_POST['guardian_name']),
        'guardian_phone' => sanitize($_POST['guardian_phone']),
        'contact_details' => sanitize($_POST['contact_details']),
        'address' => sanitize($_POST['address'])
    ];
    
    // Validate required fields
    if (empty($data['name'])) {
        $errors['name'] = 'Student name is required.';
    }
    
    if (empty($data['student_id_custom'])) {
        $errors['student_id_custom'] = 'Student ID is required.';
    } elseif ($studentModel->studentIdExists($data['student_id_custom'])) {
        $errors['student_id_custom'] = 'This Student ID already exists. Please use a unique ID.';
    }
    
    // Check if roll number exists (if provided)
    if ($data['roll_number'] && $data['class_id'] && $data['section_id']) {
        if ($studentModel->rollNumberExists($data['class_id'], $data['section_id'], $data['roll_number'])) {
            $errors['roll_number'] = 'Roll number already exists in this class and section.';
        }
    }
    
    if (!empty($errors)) {
        // Store errors in session for display
        $_SESSION['form_errors'] = $errors;
        $_SESSION['form_data'] = $data;
        redirect(BASE_URL . '/admin/students/add.php');
    }
    
    // Create student
    $studentId = $studentModel->create($data);
    
    if ($studentId) {
        // Create user account if requested
        if (isset($_POST['create_account']) && $_POST['create_account'] == '1') {
            $username = strtolower(str_replace(' ', '', $data['name'])) . $studentId;
            $password = generatePassword();
            
            $userModel->createUser([
                'username' => $username,
                'password' => $password,
                'role' => 'Student',
                'related_id' => $studentId
            ]);
            
            setFlash('success', "Student added successfully! Login: {$username} / {$password}");
        } else {
            setFlash('success', 'Student added successfully!');
        }
        
        redirect(BASE_URL . '/admin/students/');
    } else {
        setFlash('danger', 'Failed to add student.');
    }
}

// Get any stored errors/data from previous submission
$errors = $_SESSION['form_errors'] ?? [];
$formData = $_SESSION['form_data'] ?? [];
unset($_SESSION['form_errors'], $_SESSION['form_data']);

// Get all classes
$classes = $classModel->getClassesWithSections();

$pageTitle = 'Add New Student';
require_once __DIR__ . '/../../includes/admin_header.php';
?>

<div class="card">
    <div class="card-header">
        <h3>Add New Student</h3>
        <a href="<?php echo BASE_URL; ?>/admin/students/" class="btn btn-secondary">
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
        
        <form method="POST" action="" id="studentForm" novalidate>
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            
            <h4 style="margin-bottom: 1rem; color: var(--primary);">Personal Information</h4>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                <div class="form-group <?php echo isset($errors['name']) ? 'has-error' : ''; ?>">
                    <label for="name">Full Name <span class="required-star">*</span></label>
                    <input type="text" id="name" name="name" class="form-control" 
                           value="<?php echo htmlspecialchars($formData['name'] ?? ''); ?>"
                           placeholder="Enter student's full name" required>
                    <div class="field-error" id="name-error">
                        <?php echo isset($errors['name']) ? htmlspecialchars($errors['name']) : ''; ?>
                    </div>
                </div>

                <div class="form-group <?php echo isset($errors['student_id_custom']) ? 'has-error' : ''; ?>">
                    <label for="student_id_custom">Student ID <span class="required-star">*</span></label>
                    <input type="text" id="student_id_custom" name="student_id_custom" class="form-control" 
                           value="<?php echo htmlspecialchars($formData['student_id_custom'] ?? ''); ?>"
                           placeholder="e.g., S001, STU2024001" required>
                    <div class="field-error" id="student_id_custom-error">
                        <?php echo isset($errors['student_id_custom']) ? htmlspecialchars($errors['student_id_custom']) : ''; ?>
                    </div>
                    <small class="text-muted">Unique identifier for this student</small>
                </div>
                
                <div class="form-group">
                    <label for="date_of_birth">Date of Birth</label>
                    <input type="date" id="date_of_birth" name="date_of_birth" class="form-control"
                           value="<?php echo htmlspecialchars($formData['date_of_birth'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="gender">Gender</label>
                    <select id="gender" name="gender" class="form-control">
                        <option value="">Select Gender</option>
                        <option value="Male" <?php echo ($formData['gender'] ?? '') === 'Male' ? 'selected' : ''; ?>>Male</option>
                        <option value="Female" <?php echo ($formData['gender'] ?? '') === 'Female' ? 'selected' : ''; ?>>Female</option>
                        <option value="Other" <?php echo ($formData['gender'] ?? '') === 'Other' ? 'selected' : ''; ?>>Other</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="contact_details">Email/Phone</label>
                    <input type="text" id="contact_details" name="contact_details" class="form-control"
                           value="<?php echo htmlspecialchars($formData['contact_details'] ?? ''); ?>">
                </div>
            </div>
            
            <div class="form-group">
                <label for="address">Address</label>
                <textarea id="address" name="address" class="form-control" rows="3"><?php echo htmlspecialchars($formData['address'] ?? ''); ?></textarea>
            </div>
            
            <h4 style="margin: 2rem 0 1rem; color: var(--primary);">Academic Information</h4>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1.5rem;">
                <div class="form-group">
                    <label for="class_id">Class</label>
                    <select id="class_id" name="class_id" class="form-control" onchange="loadSections(this.value)">
                        <option value="">Select Class</option>
                        <?php foreach ($classes as $class): ?>
                            <option value="<?php echo $class['class_id']; ?>" 
                                    <?php echo ($formData['class_id'] ?? '') == $class['class_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($class['class_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="section_id">Section</label>
                    <select id="section_id" name="section_id" class="form-control">
                        <option value="">Select Section</option>
                    </select>
                </div>
                
                <div class="form-group <?php echo isset($errors['roll_number']) ? 'has-error' : ''; ?>">
                    <label for="roll_number">Roll Number</label>
                    <input type="number" id="roll_number" name="roll_number" class="form-control"
                           value="<?php echo htmlspecialchars($formData['roll_number'] ?? ''); ?>">
                    <div class="field-error" id="roll_number-error">
                        <?php echo isset($errors['roll_number']) ? htmlspecialchars($errors['roll_number']) : ''; ?>
                    </div>
                </div>
            </div>
            
            <h4 style="margin: 2rem 0 1rem; color: var(--primary);">Guardian Information</h4>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                <div class="form-group">
                    <label for="guardian_name">Guardian Name</label>
                    <input type="text" id="guardian_name" name="guardian_name" class="form-control"
                           value="<?php echo htmlspecialchars($formData['guardian_name'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="guardian_phone">Guardian Phone</label>
                    <input type="text" id="guardian_phone" name="guardian_phone" class="form-control"
                           value="<?php echo htmlspecialchars($formData['guardian_phone'] ?? ''); ?>">
                </div>
            </div>
            
            <h4 style="margin: 2rem 0 1rem; color: var(--primary);">User Account</h4>
            
            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="create_account" value="1">
                    <span>Create user account for student portal access</span>
                </label>
                <small style="display: block; margin-top: 0.5rem; color: #666;">
                    If checked, a username and password will be automatically generated.
                </small>
            </div>
            
            <div style="margin-top: 2rem; display: flex; gap: 1rem;">
                <button type="submit" class="btn btn-primary" id="submitBtn">
                    <i class="fas fa-save"></i> Add Student
                </button>
                <a href="<?php echo BASE_URL; ?>/admin/students/" class="btn btn-secondary">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<script>
// Load sections when class is selected
function loadSections(classId) {
    const sectionSelect = document.getElementById('section_id');
    sectionSelect.innerHTML = '<option value="">Select Section</option>';
    
    if (!classId) return;
    
    // Fetch sections via AJAX
    fetch('<?php echo BASE_URL; ?>/admin/students/get_sections.php?class_id=' + classId)
        .then(response => response.json())
        .then(sections => {
            sections.forEach(section => {
                const option = document.createElement('option');
                option.value = section.section_id;
                option.textContent = section.section_name;
                <?php if (isset($formData['section_id'])): ?>
                if (section.section_id == <?php echo (int)($formData['section_id'] ?? 0); ?>) {
                    option.selected = true;
                }
                <?php endif; ?>
                sectionSelect.appendChild(option);
            });
        });
}

// Load sections if class was previously selected
<?php if (!empty($formData['class_id'])): ?>
document.addEventListener('DOMContentLoaded', function() {
    loadSections(<?php echo (int)$formData['class_id']; ?>);
});
<?php endif; ?>

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('studentForm');
    const requiredFields = [
        { id: 'name', label: 'Full Name' },
        { id: 'student_id_custom', label: 'Student ID' }
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
