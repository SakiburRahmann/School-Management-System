<?php
/**
 * Admin - View Student
 * Display student details
 */

$pageTitle = 'View Student Details';
require_once __DIR__ . '/../../includes/admin_header.php';

$studentModel = new Student();

// Get student ID
$studentId = $_GET['id'] ?? null;

if (!$studentId) {
    setFlash('danger', 'Invalid student ID.');
    redirect(BASE_URL . '/admin/students/');
}

// Get student details
$student = $studentModel->getStudentDetails($studentId);

if (!$student) {
    setFlash('danger', 'Student not found.');
    redirect(BASE_URL . '/admin/students/');
}
?>

<div class="card">
    <div class="card-header">
        <h3>Student Details</h3>
        <div>
            <a href="<?php echo BASE_URL; ?>/admin/students/edit.php?id=<?php echo $student['student_id']; ?>" class="btn btn-warning">
                <i class="fas fa-edit"></i> Edit
            </a>
            <a href="<?php echo BASE_URL; ?>/admin/students/" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
        </div>
    </div>
    
    <div class="card-body">
        <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 2rem;">
            <!-- Left Column: Basic Info -->
            <div>
                <div style="background: #f8f9fa; padding: 1.5rem; border-radius: 8px; text-align: center; margin-bottom: 1.5rem;">
                    <div style="width: 120px; height: 120px; background: #e9ecef; border-radius: 50%; margin: 0 auto 1rem; display: flex; align-items: center; justify-content: center; font-size: 3rem; color: #adb5bd;">
                        <i class="fas fa-user"></i>
                    </div>
                    <h4 style="margin: 0;"><?php echo htmlspecialchars($student['name']); ?></h4>
                    <p style="color: #6c757d; margin: 0.5rem 0 0;">Roll No: <?php echo htmlspecialchars($student['roll_number'] ?? 'N/A'); ?></p>
                </div>
                
                <div class="info-group">
                    <label>Class</label>
                    <p><?php echo htmlspecialchars($student['class_name'] ?? 'N/A'); ?></p>
                </div>
                
                <div class="info-group">
                    <label>Section</label>
                    <p><?php echo htmlspecialchars($student['section_name'] ?? 'N/A'); ?></p>
                </div>
                
                <div class="info-group">
                    <label>Class Teacher</label>
                    <p><?php echo htmlspecialchars($student['class_teacher'] ?? 'N/A'); ?></p>
                </div>
            </div>
            
            <!-- Right Column: Detailed Info -->
            <div>
                <h4 style="color: var(--primary); border-bottom: 2px solid #eee; padding-bottom: 0.5rem; margin-bottom: 1.5rem;">Personal Information</h4>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                    <div class="info-group">
                        <label>Date of Birth</label>
                        <p><?php echo $student['date_of_birth'] ? date('d M, Y', strtotime($student['date_of_birth'])) : 'N/A'; ?></p>
                    </div>
                    
                    <div class="info-group">
                        <label>Gender</label>
                        <p><?php echo htmlspecialchars($student['gender'] ?? 'N/A'); ?></p>
                    </div>
                    
                    <div class="info-group">
                        <label>Email/Phone</label>
                        <p><?php echo htmlspecialchars($student['contact_details'] ?? 'N/A'); ?></p>
                    </div>
                    
                    <div class="info-group">
                        <label>Address</label>
                        <p><?php echo nl2br(htmlspecialchars($student['address'] ?? 'N/A')); ?></p>
                    </div>
                </div>
                
                <h4 style="color: var(--primary); border-bottom: 2px solid #eee; padding-bottom: 0.5rem; margin: 2rem 0 1.5rem;">Guardian Information</h4>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                    <div class="info-group">
                        <label>Guardian Name</label>
                        <p><?php echo htmlspecialchars($student['guardian_name'] ?? 'N/A'); ?></p>
                    </div>
                    
                    <div class="info-group">
                        <label>Guardian Phone</label>
                        <p><?php echo htmlspecialchars($student['guardian_phone'] ?? 'N/A'); ?></p>
                    </div>
                </div>
                
                <h4 style="color: var(--primary); border-bottom: 2px solid #eee; padding-bottom: 0.5rem; margin: 2rem 0 1.5rem;">System Information</h4>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                    <div class="info-group">
                        <label>Student ID</label>
                        <p><?php echo $student['student_id']; ?></p>
                    </div>
                    
                    <div class="info-group">
                        <label>Admission Date</label>
                        <p><?php echo isset($student['created_at']) ? date('d M, Y', strtotime($student['created_at'])) : 'N/A'; ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.info-group {
    margin-bottom: 1rem;
}
.info-group label {
    display: block;
    font-size: 0.85rem;
    color: #6c757d;
    margin-bottom: 0.25rem;
    font-weight: 600;
}
.info-group p {
    font-size: 1rem;
    color: #212529;
    margin: 0;
    font-weight: 500;
}
</style>

<?php require_once __DIR__ . '/../../includes/admin_footer.php'; ?>
