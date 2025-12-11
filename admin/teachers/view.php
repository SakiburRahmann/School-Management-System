<?php
/**
 * Admin - View Teacher
 * Display teacher details
 */

require_once __DIR__ . '/../../config.php';

$pageTitle = 'View Teacher Details';
require_once __DIR__ . '/../../includes/admin_header.php';

$teacherModel = new Teacher();

// Get teacher ID
$teacherId = $_GET['id'] ?? null;

if (!$teacherId) {
    setFlash('danger', 'Invalid teacher ID.');
    redirect(BASE_URL . '/admin/teachers/');
}

// Get teacher details with subjects
$teacher = $teacherModel->getWithSubjects($teacherId);

if (!$teacher) {
    setFlash('danger', 'Teacher not found.');
    redirect(BASE_URL . '/admin/teachers/');
}

// Get assigned classes (where they teach)
$assignedClasses = $teacherModel->getAssignedClasses($teacherId);

// Get sections where they are class teacher
$classTeacherSections = $teacherModel->getClassTeacherSections($teacherId);
?>

<div class="card">
    <div class="card-header">
        <h3>Teacher Details</h3>
        <div>
            <a href="<?php echo BASE_URL; ?>/admin/teachers/edit.php?id=<?php echo $teacher['teacher_id']; ?>" class="btn btn-warning">
                <i class="fas fa-edit"></i> Edit
            </a>
            <a href="<?php echo BASE_URL; ?>/admin/teachers/" class="btn btn-secondary">
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
                        <i class="fas fa-chalkboard-teacher"></i>
                    </div>
                    <h4 style="margin: 0;"><?php echo htmlspecialchars($teacher['name']); ?></h4>
                    <p style="color: #6c757d; margin: 0.5rem 0 0;"><?php echo htmlspecialchars($teacher['subject_speciality'] ?? 'Teacher'); ?></p>
                </div>
                
                <div class="info-group">
                    <label>Teacher ID</label>
                    <p>
                        <?php if (!empty($teacher['teacher_id_custom'])): ?>
                            <span class="badge badge-primary" style="font-size: 0.9rem;"><?php echo htmlspecialchars($teacher['teacher_id_custom']); ?></span>
                        <?php else: ?>
                            <span class="text-muted">Not assigned</span>
                        <?php endif; ?>
                    </p>
                </div>
                
                <div class="info-group">
                    <label>Email</label>
                    <p><?php echo htmlspecialchars($teacher['email'] ?? 'N/A'); ?></p>
                </div>
                
                <div class="info-group">
                    <label>Phone</label>
                    <p><?php echo htmlspecialchars($teacher['phone'] ?? 'N/A'); ?></p>
                </div>
                
                <div class="info-group">
                    <label>Joining Date</label>
                    <p><?php echo isset($teacher['joining_date']) ? date('d M, Y', strtotime($teacher['joining_date'])) : 'N/A'; ?></p>
                </div>

                <div class="info-group">
                    <label>Creation Date</label>
                    <p><?php echo isset($teacher['created_at']) ? date('d M, Y h:i A', strtotime($teacher['created_at'])) : 'N/A'; ?></p>
                </div>
            </div>
            
            <!-- Right Column: Detailed Info -->
            <div>
                <h4 style="color: var(--primary); border-bottom: 2px solid #eee; padding-bottom: 0.5rem; margin-bottom: 1.5rem;">Academic Responsibilities</h4>
                
                <div style="margin-bottom: 2rem;">
                    <h5 style="margin-bottom: 1rem;">Class Teacher For</h5>
                    <?php if (!empty($classTeacherSections)): ?>
                        <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                            <?php foreach ($classTeacherSections as $section): ?>
                                <span class="badge badge-success" style="font-size: 0.9rem; padding: 0.5rem 0.8rem;">
                                    <?php echo htmlspecialchars($section['class_name']); ?> - <?php echo htmlspecialchars($section['section_name']); ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">Not assigned as class teacher for any section.</p>
                    <?php endif; ?>
                </div>
                
                <div style="margin-bottom: 2rem;">
                    <h5 style="margin-bottom: 1rem;">Subjects Taught</h5>
                    <?php if (!empty($teacher['subjects'])): ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm">
                                <thead>
                                    <tr>
                                        <th>Subject</th>
                                        <th>Class</th>
                                        <th>Code</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($teacher['subjects'] as $subject): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($subject['subject_name'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($subject['class_name'] ?? 'All Classes'); ?></td>
                                            <td><?php echo htmlspecialchars($subject['subject_code'] ?? ''); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No subjects assigned yet.</p>
                    <?php endif; ?>
                </div>
                
                <h4 style="color: var(--primary); border-bottom: 2px solid #eee; padding-bottom: 0.5rem; margin: 2rem 0 1.5rem;">Personal Information</h4>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                    <div class="info-group">
                        <label>Qualification</label>
                        <p><?php echo htmlspecialchars($teacher['qualification'] ?? 'N/A'); ?></p>
                    </div>
                    
                    <div class="info-group">
                        <label>Address</label>
                        <p><?php echo nl2br(htmlspecialchars($teacher['address'] ?? 'N/A')); ?></p>
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
