<?php
/**
 * Student - Profile Page
 */

$pageTitle = 'My Profile';
require_once __DIR__ . '/../includes/student_header.php';

$studentId = $currentUser['related_id'];
$studentModel = new Student();

// Get student details
$student = $studentModel->getStudentDetails($studentId);
?>

<div class="card">
    <div class="card-header">
        <h3>Student Profile</h3>
    </div>
    <div class="card-body">
        <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 3rem;">
            <!-- Profile Picture -->
            <div style="text-align: center;">
                <div class="user-avatar" style="width: 150px; height: 150px; font-size: 4rem; margin: 0 auto 1rem;">
                    <?php echo strtoupper(substr($student['name'], 0, 1)); ?>
                </div>
                <h3 style="margin: 0;"><?php echo htmlspecialchars($student['name']); ?></h3>
                <p style="color: #666;">Student</p>
            </div>
            
            <!-- Profile Details -->
            <div>
                <h4 style="margin-bottom: 1rem; color: var(--primary);">Personal Information</h4>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 2rem;">
                    <div>
                        <label style="font-weight: 600; color: #666; font-size: 0.875rem;">Full Name</label>
                        <p style="margin: 0.25rem 0 0 0;"><?php echo htmlspecialchars($student['name']); ?></p>
                    </div>
                    
                    <div>
                        <label style="font-weight: 600; color: #666; font-size: 0.875rem;">Roll Number</label>
                        <p style="margin: 0.25rem 0 0 0;"><?php echo htmlspecialchars($student['roll_number'] ?? 'N/A'); ?></p>
                    </div>
                    
                    <div>
                        <label style="font-weight: 600; color: #666; font-size: 0.875rem;">Date of Birth</label>
                        <p style="margin: 0.25rem 0 0 0;">
                            <?php echo $student['date_of_birth'] ? formatDate($student['date_of_birth']) : 'N/A'; ?>
                        </p>
                    </div>
                    
                    <div>
                        <label style="font-weight: 600; color: #666; font-size: 0.875rem;">Gender</label>
                        <p style="margin: 0.25rem 0 0 0;"><?php echo htmlspecialchars($student['gender'] ?? 'N/A'); ?></p>
                    </div>
                    
                    <div>
                        <label style="font-weight: 600; color: #666; font-size: 0.875rem;">Contact</label>
                        <p style="margin: 0.25rem 0 0 0;"><?php echo htmlspecialchars($student['contact_details'] ?? 'N/A'); ?></p>
                    </div>
                    
                    <div>
                        <label style="font-weight: 600; color: #666; font-size: 0.875rem;">Address</label>
                        <p style="margin: 0.25rem 0 0 0;"><?php echo htmlspecialchars($student['address'] ?? 'N/A'); ?></p>
                    </div>
                </div>
                
                <h4 style="margin-bottom: 1rem; color: var(--primary);">Academic Information</h4>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 2rem;">
                    <div>
                        <label style="font-weight: 600; color: #666; font-size: 0.875rem;">Class</label>
                        <p style="margin: 0.25rem 0 0 0;"><?php echo htmlspecialchars($student['class_name'] ?? 'N/A'); ?></p>
                    </div>
                    
                    <div>
                        <label style="font-weight: 600; color: #666; font-size: 0.875rem;">Section</label>
                        <p style="margin: 0.25rem 0 0 0;"><?php echo htmlspecialchars($student['section_name'] ?? 'N/A'); ?></p>
                    </div>
                </div>
                
                <h4 style="margin-bottom: 1rem; color: var(--primary);">Guardian Information</h4>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                    <div>
                        <label style="font-weight: 600; color: #666; font-size: 0.875rem;">Guardian Name</label>
                        <p style="margin: 0.25rem 0 0 0;"><?php echo htmlspecialchars($student['guardian_name'] ?? 'N/A'); ?></p>
                    </div>
                    
                    <div>
                        <label style="font-weight: 600; color: #666; font-size: 0.875rem;">Guardian Phone</label>
                        <p style="margin: 0.25rem 0 0 0;"><?php echo htmlspecialchars($student['guardian_phone'] ?? 'N/A'); ?></p>
                    </div>
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

<?php require_once __DIR__ . '/../includes/student_footer.php'; ?>
