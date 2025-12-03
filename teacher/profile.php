<?php
/**
 * Teacher - Profile Page
 */

$pageTitle = 'My Profile';
require_once __DIR__ . '/../includes/teacher_header.php';

$teacherId = $currentUser['related_id'];
$teacherModel = new Teacher();
$subjectModel = new Subject();

// Get teacher details
$teacher = $teacherModel->find($teacherId);
$mySubjects = $subjectModel->getByTeacher($teacherId);
$classTeacherSections = $teacherModel->getClassTeacherSections($teacherId);
?>

<div class="card">
    <div class="card-header">
        <h3>Teacher Profile</h3>
    </div>
    <div class="card-body">
        <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 3rem;">
            <!-- Profile Picture -->
            <div style="text-align: center;">
                <div class="user-avatar" style="width: 150px; height: 150px; font-size: 4rem; margin: 0 auto 1rem;">
                    <?php echo strtoupper(substr($teacher['name'], 0, 1)); ?>
                </div>
                <h3 style="margin: 0;"><?php echo htmlspecialchars($teacher['name']); ?></h3>
                <p style="color: #666;">Teacher</p>
            </div>
            
            <!-- Profile Details -->
            <div>
                <h4 style="margin-bottom: 1rem; color: var(--primary);">Personal Information</h4>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 2rem;">
                    <div>
                        <label style="font-weight: 600; color: #666; font-size: 0.875rem;">Full Name</label>
                        <p style="margin: 0.25rem 0 0 0;"><?php echo htmlspecialchars($teacher['name']); ?></p>
                    </div>
                    
                    <div>
                        <label style="font-weight: 600; color: #666; font-size: 0.875rem;">Specialization</label>
                        <p style="margin: 0.25rem 0 0 0;"><?php echo htmlspecialchars($teacher['subject_speciality'] ?? 'N/A'); ?></p>
                    </div>
                    
                    <div>
                        <label style="font-weight: 600; color: #666; font-size: 0.875rem;">Email</label>
                        <p style="margin: 0.25rem 0 0 0;"><?php echo htmlspecialchars($teacher['email'] ?? 'N/A'); ?></p>
                    </div>
                    
                    <div>
                        <label style="font-weight: 600; color: #666; font-size: 0.875rem;">Phone</label>
                        <p style="margin: 0.25rem 0 0 0;"><?php echo htmlspecialchars($teacher['phone'] ?? 'N/A'); ?></p>
                    </div>
                    
                    <div style="grid-column: 1 / -1;">
                        <label style="font-weight: 600; color: #666; font-size: 0.875rem;">Address</label>
                        <p style="margin: 0.25rem 0 0 0;"><?php echo htmlspecialchars($teacher['address'] ?? 'N/A'); ?></p>
                    </div>
                </div>
                
                <h4 style="margin-bottom: 1rem; color: var(--primary);">Teaching Information</h4>
                <div style="margin-bottom: 2rem;">
                    <label style="font-weight: 600; color: #666; font-size: 0.875rem;">Assigned Subjects</label>
                    <?php if (!empty($mySubjects)): ?>
                        <div style="display: flex; flex-wrap: wrap; gap: 0.5rem; margin-top: 0.5rem;">
                            <?php foreach ($mySubjects as $subject): ?>
                                <span class="badge badge-primary" style="font-size: 0.9rem; padding: 0.5rem 1rem;">
                                    <?php echo htmlspecialchars($subject['subject_name']); ?>
                                    (<?php echo htmlspecialchars($subject['class_name'] ?? 'All'); ?>)
                                </span>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p style="margin: 0.25rem 0 0 0; color: #999;">No subjects assigned</p>
                    <?php endif; ?>
                </div>
                
                <div>
                    <label style="font-weight: 600; color: #666; font-size: 0.875rem;">Class Teacher Of</label>
                    <?php if (!empty($classTeacherSections)): ?>
                        <div style="display: flex; flex-wrap: wrap; gap: 0.5rem; margin-top: 0.5rem;">
                            <?php foreach ($classTeacherSections as $section): ?>
                                <span class="badge badge-success" style="font-size: 0.9rem; padding: 0.5rem 1rem;">
                                    <?php echo htmlspecialchars($section['class_name']); ?> - 
                                    Section <?php echo htmlspecialchars($section['section_name']); ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p style="margin: 0.25rem 0 0 0; color: #999;">Not assigned as class teacher</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Account Information -->
<div class="card">
    <div class="card-header">
        <h3>Account Information</h3>
    </div>
    <div class="card-body">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
            <div>
                <label style="font-weight: 600; color: #666; font-size: 0.875rem;">Username</label>
                <p style="margin: 0.25rem 0 0 0;"><?php echo htmlspecialchars($currentUser['username']); ?></p>
            </div>
            
            <div>
                <label style="font-weight: 600; color: #666; font-size: 0.875rem;">Account Status</label>
                <p style="margin: 0.25rem 0 0 0;">
                    <span class="badge badge-success">Active</span>
                </p>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/teacher_footer.php'; ?>
