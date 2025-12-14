<?php
/**
 * Admin - View Admission Request Details
 */

require_once __DIR__ . '/../../config.php';

// Ensure user is logged in and is admin
requireRole('Admin');

$requestId = $_GET['id'] ?? null;

if (!$requestId) {
    setFlash('danger', 'Invalid request ID.');
    redirect(BASE_URL . '/admin/admissions/');
}

$admissionModel = new AdmissionRequest();
$request = $admissionModel->find($requestId);

if (!$request) {
    setFlash('danger', 'Admission request not found.');
    redirect(BASE_URL . '/admin/admissions/');
}

// Handle Status Updates
if (isset($_GET['action'])) {
    // Determine action
    $success = false;
    $message = '';
    
    if ($_GET['action'] === 'approve') {
        $result = $admissionModel->approve($requestId);
        if ($result) {
            $success = true;
            $message = 'Application approved successfully!';
        } else {
            setFlash('danger', 'Failed to approve application.');
        }
    } elseif ($_GET['action'] === 'reject') {
        $result = $admissionModel->reject($requestId);
        if ($result) {
            $success = true;
            $message = 'Application rejected successfully!';
        } else {
            setFlash('danger', 'Failed to reject application.');
        }
    }
    
    if ($success) {
        setFlash('success', $message);
        // Redirect to refresh page and clear GET params
        // Or redirect back to list if preferred, but usually staying on page is nice
        // To follow previous pattern:
        $request = $admissionModel->find($requestId); 
        // Force redirect to remove action param prevents accidental re-submission on refresh
        redirect(BASE_URL . '/admin/admissions/view.php?id=' . $requestId);
    }
}

$pageTitle = 'Application Details';
require_once __DIR__ . '/../../includes/admin_header.php';
?>

<style>
.application-view-container {
    max-width: 1000px;
    margin: 0 auto;
}

.app-header {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    padding: 2rem;
    border-radius: 16px 16px 0 0;
    margin-bottom: 0;
}

.app-header-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
}

.app-title h2 {
    margin: 0 0 0.5rem 0;
    font-size: 1.75rem;
    color: white;
}

.app-title p {
    margin: 0;
    opacity: 0.9;
    font-size: 1.1rem;
}

.app-status-large {
    display: inline-block;
    padding: 0.75rem 1.5rem;
    border-radius: 30px;
    font-size: 1rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    background: rgba(255,255,255,0.2);
    backdrop-filter: blur(10px);
}

.app-body {
    background: white;
    padding: 2.5rem;
    border-radius: 0 0 16px 16px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.1);
}

.info-section {
    margin-bottom: 2.5rem;
}

.info-section:last-child {
    margin-bottom: 0;
}

.section-title {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-size: 1.25rem;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 1.5rem;
    padding-bottom: 0.75rem;
    border-bottom: 3px solid #e5e7eb;
}

.section-title i {
    color: var(--primary);
    font-size: 1.5rem;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
}

.info-item {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.info-label {
    font-size: 0.85rem;
    font-weight: 600;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.info-value {
    font-size: 1rem;
    color: #1f2937;
    font-weight: 500;
    padding: 0.75rem 1rem;
    background: #f9fafb;
    border-radius: 8px;
    border-left: 3px solid var(--primary);
}

.info-value.large {
    font-size: 1.1rem;
    font-weight: 600;
}

.action-section {
    margin-top: 2.5rem;
    padding: 2rem;
    background: linear-gradient(135deg, #f9fafb, #f3f4f6);
    border-radius: 12px;
    text-align: center;
}

.action-buttons {
    display: flex;
    gap: 1rem;
    justify-content: center;
    flex-wrap: wrap;
}

.btn-action-large {
    padding: 1rem 2rem;
    border-radius: 12px;
    border: none;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    color: white;
}

.btn-action-large:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.15);
    color: white;
    text-decoration: none;
}

.btn-approve-large {
    background: linear-gradient(135deg, #10b981, #059669);
}

.btn-reject-large {
    background: linear-gradient(135deg, #ef4444, #dc2626);
}

.btn-back {
    padding: 0.75rem 1.5rem;
    background: #6b7280;
    color: white;
    border-radius: 10px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.btn-back:hover {
    background: #4b5563;
    transform: translateY(-2px);
    color: white;
    text-decoration: none;
}

.remarks-box {
    background: #fef3c7;
    border-left: 4px solid #f59e0b;
    padding: 1.25rem 1.5rem;
    border-radius: 8px;
    margin-top: 1.5rem;
}

.remarks-box h4 {
    margin: 0 0 0.5rem 0;
    color: #92400e;
    font-size: 1rem;
    font-weight: 700;
}

.remarks-box p {
    margin: 0;
    color: #78350f;
    line-height: 1.6;
}

.timeline-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    background: #e0e7ff;
    color: #3730a3;
    border-radius: 8px;
    font-size: 0.9rem;
    font-weight: 600;
}

@media (max-width: 768px) {
    .app-header-content {
        flex-direction: column;
        text-align: center;
    }
    
    .info-grid {
        grid-template-columns: 1fr;
    }
    
    .action-buttons {
        flex-direction: column;
    }
    
    .btn-action-large {
        width: 100%;
        justify-content: center;
    }
}
</style>

<div class="application-view-container">
    <div class="card" style="border: none; box-shadow: 0 10px 40px rgba(0,0,0,0.1); border-radius: 16px; overflow: hidden; margin: 0;">
        <!-- Header -->
        <div class="app-header">
            <div class="app-header-content">
                <div>
                    <a href="<?php echo BASE_URL; ?>/admin/admissions/" class="btn-back">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                </div>
                <div class="app-status-large">
                    <i class="fas fa-<?php echo $request['status'] === 'Approved' ? 'check-circle' : ($request['status'] === 'Rejected' ? 'times-circle' : 'clock'); ?>"></i>
                    <?php echo $request['status']; ?>
                </div>
            </div>
            <div class="app-title" style="margin-top: 1.5rem;">
                <h2><i class="fas fa-user-graduate"></i> <?php echo htmlspecialchars($request['student_name']); ?></h2>
                <p>Applying for <strong><?php echo htmlspecialchars($request['class_applying_for']); ?></strong></p>
            </div>
        </div>
        
        <!-- Body -->
        <div class="app-body">
            <!-- Student Information -->
            <div class="info-section">
                <div class="section-title">
                    <i class="fas fa-user-graduate"></i>
                    <span>Student Information</span>
                </div>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Full Name</div>
                        <div class="info-value large"><?php echo htmlspecialchars($request['student_name']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Date of Birth</div>
                        <div class="info-value">
                            <i class="fas fa-calendar"></i>
                            <?php echo date('F d, Y', strtotime($request['date_of_birth'])); ?>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Gender</div>
                        <div class="info-value">
                            <i class="fas fa-<?php echo $request['gender'] === 'Male' ? 'mars' : 'venus'; ?>"></i>
                            <?php echo htmlspecialchars($request['gender']); ?>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Class Applying For</div>
                        <div class="info-value large">
                            <i class="fas fa-graduation-cap"></i>
                            <?php echo htmlspecialchars($request['class_applying_for']); ?>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Previous School</div>
                        <div class="info-value">
                            <i class="fas fa-school"></i>
                            <?php echo htmlspecialchars($request['previous_school'] ?: 'Not provided'); ?>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Address</div>
                        <div class="info-value">
                            <i class="fas fa-map-marker-alt"></i>
                            <?php echo nl2br(htmlspecialchars($request['address'])); ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Guardian Information -->
            <div class="info-section">
                <div class="section-title">
                    <i class="fas fa-users"></i>
                    <span>Guardian Information</span>
                </div>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Guardian Name</div>
                        <div class="info-value large">
                            <i class="fas fa-user"></i>
                            <?php echo htmlspecialchars($request['guardian_name']); ?>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Phone Number</div>
                        <div class="info-value">
                            <i class="fas fa-phone"></i>
                            <a href="tel:<?php echo htmlspecialchars($request['guardian_phone']); ?>" style="color: inherit; text-decoration: none;">
                                <?php echo htmlspecialchars($request['guardian_phone']); ?>
                            </a>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Email Address</div>
                        <div class="info-value">
                            <i class="fas fa-envelope"></i>
                            <?php if (!empty($request['guardian_email'])): ?>
                                <a href="mailto:<?php echo htmlspecialchars($request['guardian_email']); ?>" style="color: inherit; text-decoration: none;">
                                    <?php echo htmlspecialchars($request['guardian_email']); ?>
                                </a>
                            <?php else: ?>
                                Not provided
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Application Timeline -->
            <div class="info-section">
                <div class="section-title">
                    <i class="fas fa-history"></i>
                    <span>Application Timeline</span>
                </div>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Submitted On</div>
                        <div class="info-value">
                            <i class="fas fa-clock"></i>
                            <?php echo date('F d, Y - h:i A', strtotime($request['created_at'])); ?>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Application ID</div>
                        <div class="info-value">
                            <span class="timeline-badge">
                                <i class="fas fa-hashtag"></i>
                                <?php echo str_pad($request['request_id'], 5, '0', STR_PAD_LEFT); ?>
                            </span>
                        </div>
                    </div>
                </div>
                
                <?php if (!empty($request['remarks'])): ?>
                    <div class="remarks-box">
                        <h4><i class="fas fa-comment-alt"></i> Admin Remarks</h4>
                        <p><?php echo nl2br(htmlspecialchars($request['remarks'])); ?></p>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Action Buttons -->
            <?php if ($request['status'] === 'Pending'): ?>
                <div class="action-section">
                    <h3 style="margin: 0 0 1.5rem 0; color: #374151;">
                        <i class="fas fa-tasks"></i> Review Application
                    </h3>
                    <div class="action-buttons">
                        <a href="?id=<?php echo $request['request_id']; ?>&action=approve" 
                           class="btn-action-large btn-approve-large"
                           onclick="return confirm('Are you sure you want to APPROVE this application?\n\nStudent: <?php echo htmlspecialchars($request['student_name']); ?>\nClass: <?php echo htmlspecialchars($request['class_applying_for']); ?>');">
                            <i class="fas fa-check-circle"></i>
                            Approve Application
                        </a>
                        <a href="?id=<?php echo $request['request_id']; ?>&action=reject" 
                           class="btn-action-large btn-reject-large"
                           onclick="return confirm('Are you sure you want to REJECT this application?\n\nStudent: <?php echo htmlspecialchars($request['student_name']); ?>\nClass: <?php echo htmlspecialchars($request['class_applying_for']); ?>');">
                            <i class="fas fa-times-circle"></i>
                            Reject Application
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <div style="text-align: center; padding: 2rem; background: #f9fafb; border-radius: 12px; margin-top: 2rem;">
                    <p style="margin: 0; color: #6b7280; font-size: 1rem;">
                        <i class="fas fa-info-circle"></i>
                        This application has been <strong style="color: <?php echo $request['status'] === 'Approved' ? '#059669' : '#dc2626'; ?>;">
                            <?php echo strtoupper($request['status']); ?>
                        </strong>
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/admin_footer.php'; ?>
