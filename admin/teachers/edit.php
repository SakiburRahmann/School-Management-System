<?php
/**
 * Admin - Edit Teacher
 * Form to edit teacher details
 */

require_once __DIR__ . '/../../config.php';

$teacherModel = new Teacher();

// Get teacher ID
$teacherId = $_GET['id'] ?? null;

if (!$teacherId) {
    setFlash('danger', 'Invalid teacher ID.');
    redirect(BASE_URL . '/admin/teachers/');
}

// Get teacher details
$teacher = $teacherModel->find($teacherId);

if (!$teacher) {
    setFlash('danger', 'Teacher not found.');
    redirect(BASE_URL . '/admin/teachers/');
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'])) {
        setFlash('danger', 'Invalid request.');
        redirect(BASE_URL . '/admin/teachers/edit.php?id=' . $teacherId);
    }
    
    $data = [
        'name' => sanitize($_POST['name']),
        'teacher_id_custom' => sanitize($_POST['teacher_id_custom']),
        'qualification' => sanitize($_POST['qualification']),
        'subject_speciality' => sanitize($_POST['subject_speciality']),
        'email' => sanitize($_POST['email']),
        'phone' => sanitize($_POST['phone']),
        'contact_details' => sanitize($_POST['contact_details']),
        'address' => sanitize($_POST['address'])
    ];
    
    // Validate required fields
    if (empty($data['name'])) {
        setFlash('danger', 'Teacher name is required.');
        redirect(BASE_URL . '/admin/teachers/edit.php?id=' . $teacherId);
    }
    
    // Check if teacher ID already exists (excluding current teacher)
    if (!empty($data['teacher_id_custom']) && $teacherModel->teacherIdExists($data['teacher_id_custom'], $teacherId)) {
        setFlash('danger', 'Teacher ID already exists. Please use a unique ID.');
        redirect(BASE_URL . '/admin/teachers/edit.php?id=' . $teacherId);
    }
    
    // Update teacher
    if ($teacherModel->update($teacherId, $data)) {
        setFlash('success', 'Teacher updated successfully!');
        redirect(BASE_URL . '/admin/teachers/view.php?id=' . $teacherId);
    } else {
        setFlash('danger', 'Failed to update teacher.');
    }
}

$pageTitle = 'Edit Teacher';
require_once __DIR__ . '/../../includes/admin_header.php';
?>

<div class="card">
    <div class="card-header">
        <h3>Edit Teacher</h3>
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
                    <input type="text" id="name" name="name" class="form-control" required 
                           value="<?php echo htmlspecialchars($teacher['name']); ?>">
                </div>

                <div class="form-group">
                    <label for="teacher_id_custom">Teacher ID</label>
                    <input type="text" id="teacher_id_custom" name="teacher_id_custom" class="form-control" 
                           placeholder="e.g., T001, TCH2024001"
                           value="<?php echo htmlspecialchars($teacher['teacher_id_custom'] ?? ''); ?>">
                    <small class="text-muted">Optional unique identifier for this teacher</small>
                </div>

                <div class="form-group">
                    <label for="qualification">Qualification</label>
                    <input type="text" id="qualification" name="qualification" class="form-control" 
                           placeholder="e.g., MSc in Physics, BEd"
                           value="<?php echo htmlspecialchars($teacher['qualification'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="subject_speciality">Subject Specialization</label>
                    <input type="text" id="subject_speciality" name="subject_speciality" class="form-control" 
                           placeholder="e.g., Mathematics, Science"
                           value="<?php echo htmlspecialchars($teacher['subject_speciality'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-control"
                           value="<?php echo htmlspecialchars($teacher['email'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="phone">Phone</label>
                    <input type="text" id="phone" name="phone" class="form-control"
                           value="<?php echo htmlspecialchars($teacher['phone'] ?? ''); ?>">
                </div>
            </div>
            
            <div class="form-group">
                <label for="contact_details">Additional Contact Details</label>
                <input type="text" id="contact_details" name="contact_details" class="form-control"
                       value="<?php echo htmlspecialchars($teacher['contact_details'] ?? ''); ?>">
            </div>
            
            <div class="form-group">
                <label for="address">Address</label>
                <textarea id="address" name="address" class="form-control" rows="3"><?php echo htmlspecialchars($teacher['address'] ?? ''); ?></textarea>
            </div>
            
            <div style="margin-top: 2rem; display: flex; gap: 1rem;">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Update Teacher
                </button>
                <a href="<?php echo BASE_URL; ?>/admin/teachers/" class="btn btn-secondary">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/admin_footer.php'; ?>
