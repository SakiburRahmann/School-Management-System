<?php
/**
 * Admin - Edit Student
 * Form to edit student details
 */

require_once __DIR__ . '/../../config.php';

$studentModel = new Student();
$classModel = new ClassModel();

// Get student ID
$studentId = $_GET['id'] ?? null;

if (!$studentId) {
    setFlash('danger', 'Invalid student ID.');
    redirect(BASE_URL . '/admin/students/');
}

// Get student details
$student = $studentModel->find($studentId);

if (!$student) {
    setFlash('danger', 'Student not found.');
    redirect(BASE_URL . '/admin/students/');
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'])) {
        setFlash('danger', 'Invalid request.');
        redirect(BASE_URL . '/admin/students/edit.php?id=' . $studentId);
    }
    
    $data = [
        'name' => sanitize($_POST['name']),
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
        redirect(BASE_URL . '/admin/students/edit.php?id=' . $studentId);
    }
    
    // Check if roll number exists (exclude current student)
    if ($data['roll_number'] && $data['class_id'] && $data['section_id']) {
        if ($studentModel->rollNumberExists($data['class_id'], $data['section_id'], $data['roll_number'], $studentId)) {
            setFlash('danger', 'Roll number already exists in this class and section.');
            redirect(BASE_URL . '/admin/students/edit.php?id=' . $studentId);
        }
    }
    
    // Update student
    if ($studentModel->update($studentId, $data)) {
        setFlash('success', 'Student updated successfully!');
        redirect(BASE_URL . '/admin/students/view.php?id=' . $studentId);
    } else {
        setFlash('danger', 'Failed to update student.');
    }
}

// Get all classes
$classes = $classModel->getClassesWithSections();

$pageTitle = 'Edit Student';
require_once __DIR__ . '/../../includes/admin_header.php';
?>

<div class="card">
    <div class="card-header">
        <h3>Edit Student</h3>
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
                    <input type="text" id="name" name="name" class="form-control" 
                           value="<?php echo htmlspecialchars($student['name']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="date_of_birth">Date of Birth</label>
                    <input type="date" id="date_of_birth" name="date_of_birth" class="form-control"
                           value="<?php echo $student['date_of_birth']; ?>">
                </div>
                
                <div class="form-group">
                    <label for="gender">Gender</label>
                    <select id="gender" name="gender" class="form-control">
                        <option value="">Select Gender</option>
                        <option value="Male" <?php echo $student['gender'] == 'Male' ? 'selected' : ''; ?>>Male</option>
                        <option value="Female" <?php echo $student['gender'] == 'Female' ? 'selected' : ''; ?>>Female</option>
                        <option value="Other" <?php echo $student['gender'] == 'Other' ? 'selected' : ''; ?>>Other</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="contact_details">Email/Phone</label>
                    <input type="text" id="contact_details" name="contact_details" class="form-control"
                           value="<?php echo htmlspecialchars($student['contact_details']); ?>">
                </div>
            </div>
            
            <div class="form-group">
                <label for="address">Address</label>
                <textarea id="address" name="address" class="form-control" rows="3"><?php echo htmlspecialchars($student['address']); ?></textarea>
            </div>
            
            <h4 style="margin: 2rem 0 1rem; color: var(--primary);">Academic Information</h4>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1.5rem;">
                <div class="form-group">
                    <label for="class_id">Class</label>
                    <select id="class_id" name="class_id" class="form-control" onchange="loadSections(this.value)">
                        <option value="">Select Class</option>
                        <?php foreach ($classes as $class): ?>
                            <option value="<?php echo $class['class_id']; ?>" 
                                    <?php echo $student['class_id'] == $class['class_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($class['class_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="section_id">Section</label>
                    <select id="section_id" name="section_id" class="form-control">
                        <option value="">Select Section</option>
                        <!-- Sections will be loaded via JS, but we need to pre-select -->
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="roll_number">Roll Number</label>
                    <input type="number" id="roll_number" name="roll_number" class="form-control"
                           value="<?php echo htmlspecialchars($student['roll_number']); ?>">
                </div>
            </div>
            
            <h4 style="margin: 2rem 0 1rem; color: var(--primary);">Guardian Information</h4>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                <div class="form-group">
                    <label for="guardian_name">Guardian Name <span style="color: red;">*</span></label>
                    <input type="text" id="guardian_name" name="guardian_name" class="form-control" 
                           value="<?php echo htmlspecialchars($student['guardian_name']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="guardian_phone">Guardian Phone</label>
                    <input type="text" id="guardian_phone" name="guardian_phone" class="form-control"
                           value="<?php echo htmlspecialchars($student['guardian_phone']); ?>">
                </div>
            </div>
            
            <div style="margin-top: 2rem; display: flex; gap: 1rem;">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Update Student
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
function loadSections(classId, selectedSectionId = null) {
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
                if (selectedSectionId && section.section_id == selectedSectionId) {
                    option.selected = true;
                }
                sectionSelect.appendChild(option);
            });
        });
}

// Initial load
document.addEventListener('DOMContentLoaded', function() {
    const classId = '<?php echo $student['class_id']; ?>';
    const sectionId = '<?php echo $student['section_id']; ?>';
    if (classId) {
        loadSections(classId, sectionId);
    }
});
</script>

<?php require_once __DIR__ . '/../../includes/admin_footer.php'; ?>
