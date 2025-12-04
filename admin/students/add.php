<?php
/**
 * Admin - Add Student
 * Form to add new student
 */

require_once __DIR__ . '/../../config.php';

$studentModel = new Student();
$classModel = new ClassModel();
$userModel = new User();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'])) {
        setFlash('danger', 'Invalid request.');
        redirect(BASE_URL . '/admin/students/add.php');
    }
    
    $data = [
        'name' => sanitize($_POST['name']),
        'student_id_custom' => sanitize($_POST['student_id_custom']),
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
    if (empty($data['name']) || empty($data['guardian_name'])) {
        setFlash('danger', 'Please fill in all required fields.');
        redirect(BASE_URL . '/admin/students/add.php');
    }
    
    // Check if student ID already exists
    if (!empty($data['student_id_custom']) && $studentModel->studentIdExists($data['student_id_custom'])) {
        setFlash('danger', 'Student ID already exists. Please use a unique ID.');
        redirect(BASE_URL . '/admin/students/add.php');
    }
    
    // Check if roll number exists
    if ($data['roll_number'] && $data['class_id'] && $data['section_id']) {
        if ($studentModel->rollNumberExists($data['class_id'], $data['section_id'], $data['roll_number'])) {
            setFlash('danger', 'Roll number already exists in this class and section.');
            redirect(BASE_URL . '/admin/students/add.php');
        }
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
        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            
            <h4 style="margin-bottom: 1rem; color: var(--primary);">Personal Information</h4>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                <div class="form-group">
                    <label for="name">Full Name <span style="color: red;">*</span></label>
                    <input type="text" id="name" name="name" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="student_id_custom">Student ID</label>
                    <input type="text" id="student_id_custom" name="student_id_custom" class="form-control" 
                           placeholder="e.g., S001, STU2024001">
                    <small class="text-muted">Optional unique identifier for this student</small>
                </div>
                
                <div class="form-group">
                    <label for="date_of_birth">Date of Birth</label>
                    <input type="date" id="date_of_birth" name="date_of_birth" class="form-control">
                </div>
                
                <div class="form-group">
                    <label for="gender">Gender</label>
                    <select id="gender" name="gender" class="form-control">
                        <option value="">Select Gender</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="contact_details">Email/Phone</label>
                    <input type="text" id="contact_details" name="contact_details" class="form-control">
                </div>
            </div>
            
            <div class="form-group">
                <label for="address">Address</label>
                <textarea id="address" name="address" class="form-control" rows="3"></textarea>
            </div>
            
            <h4 style="margin: 2rem 0 1rem; color: var(--primary);">Academic Information</h4>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1.5rem;">
                <div class="form-group">
                    <label for="class_id">Class</label>
                    <select id="class_id" name="class_id" class="form-control" onchange="loadSections(this.value)">
                        <option value="">Select Class</option>
                        <?php foreach ($classes as $class): ?>
                            <option value="<?php echo $class['class_id']; ?>">
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
                
                <div class="form-group">
                    <label for="roll_number">Roll Number</label>
                    <input type="number" id="roll_number" name="roll_number" class="form-control">
                </div>
            </div>
            
            <h4 style="margin: 2rem 0 1rem; color: var(--primary);">Guardian Information</h4>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                <div class="form-group">
                    <label for="guardian_name">Guardian Name <span style="color: red;">*</span></label>
                    <input type="text" id="guardian_name" name="guardian_name" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="guardian_phone">Guardian Phone</label>
                    <input type="text" id="guardian_phone" name="guardian_phone" class="form-control">
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
                <button type="submit" class="btn btn-primary">
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
                sectionSelect.appendChild(option);
            });
        });
}
</script>

<?php require_once __DIR__ . '/../../includes/admin_footer.php'; ?>
