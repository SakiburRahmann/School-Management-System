<?php
/**
 * Admin - View Subject Details
 */

require_once __DIR__ . '/../../config.php';

// Check if logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    redirect(BASE_URL . '/login.php');
}

$subjectModel = new Subject();

// Get subject ID
$subjectId = $_GET['id'] ?? null;

if (!$subjectId) {
    setFlash('danger', 'Subject ID is required.');
    redirect(BASE_URL . '/admin/subjects/');
}

// Get subject details
$subject = $subjectModel->getFullDetails($subjectId);

if (!$subject) {
    setFlash('danger', 'Subject not found.');
    redirect(BASE_URL . '/admin/subjects/');
}

// Get statistics
$stats = $subjectModel->getStatistics($subjectId);

// Now include the header (after all redirects)
$pageTitle = 'Subject Details';
require_once __DIR__ . '/../../includes/admin_header.php';
?>

<style>
/* View Subject Styles */
.subject-detail-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    flex-wrap: wrap;
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.subject-title-section h2 {
    margin: 0 0 0.5rem 0;
    color: var(--dark);
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.subject-code-badge {
    font-family: monospace;
    background: linear-gradient(135deg, var(--primary), var(--secondary));
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 8px;
    font-size: 1rem;
}

.subject-actions {
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
}

.detail-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 1.5rem;
}

.detail-card {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.detail-card h4 {
    color: var(--primary);
    margin: 0 0 1rem 0;
    font-size: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding-bottom: 0.75rem;
    border-bottom: 2px solid var(--light);
}

.detail-item {
    display: flex;
    justify-content: space-between;
    padding: 0.75rem 0;
    border-bottom: 1px solid #f0f0f0;
}

.detail-item:last-child {
    border-bottom: none;
}

.detail-label {
    color: #666;
    font-weight: 500;
}

.detail-value {
    color: var(--dark);
    font-weight: 600;
    text-align: right;
}

.type-badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
}

.type-badge.core { background: #dbeafe; color: #1d4ed8; }
.type-badge.elective { background: #fef3c7; color: #b45309; }
.type-badge.lab { background: #d1fae5; color: #047857; }

.status-badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
}

.status-badge.active { background: #d1fae5; color: #047857; }
.status-badge.inactive { background: #fee2e2; color: #dc2626; }

/* Teachers list */
.teacher-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.teacher-list-item {
    display: flex;
    align-items: center;
    padding: 0.75rem;
    background: var(--light);
    border-radius: 8px;
    margin-bottom: 0.5rem;
    transition: all 0.2s;
}

.teacher-list-item:hover {
    background: #e8e8f0;
    transform: translateX(5px);
}

.teacher-avatar {
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, var(--primary), var(--secondary));
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 600;
    margin-right: 0.75rem;
}

.teacher-info {
    flex: 1;
}

.teacher-name {
    font-weight: 600;
    color: var(--dark);
    margin: 0;
}

.teacher-email {
    font-size: 0.85rem;
    color: #666;
    margin: 0;
}

.empty-teachers {
    text-align: center;
    padding: 2rem;
    color: #999;
}

.empty-teachers i {
    font-size: 2.5rem;
    margin-bottom: 0.75rem;
    display: block;
}

/* Stats cards */
.stat-mini {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 1rem;
    background: var(--light);
    border-radius: 8px;
    margin-bottom: 0.5rem;
}

.stat-mini-icon {
    width: 45px;
    height: 45px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
}

.stat-mini-icon.teachers { background: #dbeafe; color: #1d4ed8; }
.stat-mini-icon.results { background: #d1fae5; color: #047857; }

.stat-mini-value {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--dark);
    line-height: 1;
}

.stat-mini-label {
    font-size: 0.85rem;
    color: #666;
}

/* Description box */
.description-box {
    background: var(--light);
    border-radius: 8px;
    padding: 1rem;
    line-height: 1.7;
    color: #444;
}

/* Mobile adjustments */
@media (max-width: 768px) {
    .subject-detail-header {
        flex-direction: column;
    }
    
    .subject-actions {
        width: 100%;
    }
    
    .subject-actions .btn {
        flex: 1;
        text-align: center;
    }
}
</style>

<!-- Back Button and Actions -->
<div class="subject-detail-header">
    <div class="subject-title-section">
        <h2>
            <span class="subject-code-badge"><?php echo htmlspecialchars($subject['subject_code']); ?></span>
            <?php echo htmlspecialchars($subject['subject_name']); ?>
        </h2>
        <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
            <span class="type-badge <?php echo strtolower($subject['subject_type'] ?? 'core'); ?>">
                <?php echo htmlspecialchars($subject['subject_type'] ?? 'Core'); ?>
            </span>
            <span class="status-badge <?php echo strtolower($subject['status'] ?? 'active'); ?>">
                <?php echo htmlspecialchars($subject['status'] ?? 'Active'); ?>
            </span>
        </div>
    </div>
    <div class="subject-actions">
        <a href="<?php echo BASE_URL; ?>/admin/subjects/" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back
        </a>
        <a href="<?php echo BASE_URL; ?>/admin/subjects/edit.php?id=<?php echo $subjectId; ?>" class="btn btn-warning">
            <i class="fas fa-edit"></i> Edit
        </a>
        <a href="<?php echo BASE_URL; ?>/admin/subjects/delete.php?id=<?php echo $subjectId; ?>" 
           class="btn btn-danger delete-btn"
           data-delete-url="<?php echo BASE_URL; ?>/admin/subjects/delete.php?id=<?php echo $subjectId; ?>"
           data-delete-message="Are you sure you want to delete subject '<?php echo htmlspecialchars($subject['subject_name']); ?>'?">
            <i class="fas fa-trash"></i> Delete
        </a>
    </div>
</div>

<div class="detail-grid">
    <!-- Subject Information -->
    <div class="detail-card">
        <h4><i class="fas fa-info-circle"></i> Subject Information</h4>
        
        <div class="detail-item">
            <span class="detail-label">Subject Name</span>
            <span class="detail-value"><?php echo htmlspecialchars($subject['subject_name']); ?></span>
        </div>
        
        <div class="detail-item">
            <span class="detail-label">Subject Code</span>
            <span class="detail-value"><code><?php echo htmlspecialchars($subject['subject_code']); ?></code></span>
        </div>
        
        <div class="detail-item">
            <span class="detail-label">Type</span>
            <span class="detail-value">
                <span class="type-badge <?php echo strtolower($subject['subject_type'] ?? 'core'); ?>">
                    <?php echo htmlspecialchars($subject['subject_type'] ?? 'Core'); ?>
                </span>
            </span>
        </div>
        
        <div class="detail-item">
            <span class="detail-label">Credits / Hours</span>
            <span class="detail-value"><?php echo (int)($subject['credits_hours'] ?? 0); ?> per week</span>
        </div>
        
        <div class="detail-item">
            <span class="detail-label">Assigned Class</span>
            <span class="detail-value"><?php echo htmlspecialchars($subject['class_name'] ?? 'All Classes'); ?></span>
        </div>
        
        <div class="detail-item">
            <span class="detail-label">Status</span>
            <span class="detail-value">
                <span class="status-badge <?php echo strtolower($subject['status'] ?? 'active'); ?>">
                    <?php echo htmlspecialchars($subject['status'] ?? 'Active'); ?>
                </span>
            </span>
        </div>
        
        <div class="detail-item">
            <span class="detail-label">Created</span>
            <span class="detail-value"><?php echo formatDate($subject['created_at']); ?></span>
        </div>
    </div>
    
    <!-- Statistics -->
    <div class="detail-card">
        <h4><i class="fas fa-chart-bar"></i> Statistics</h4>
        
        <div class="stat-mini">
            <div class="stat-mini-icon teachers">
                <i class="fas fa-chalkboard-teacher"></i>
            </div>
            <div>
                <div class="stat-mini-value"><?php echo $stats['teacher_count']; ?></div>
                <div class="stat-mini-label">Teachers Assigned</div>
            </div>
        </div>
        
        <div class="stat-mini">
            <div class="stat-mini-icon results">
                <i class="fas fa-clipboard-check"></i>
            </div>
            <div>
                <div class="stat-mini-value"><?php echo $stats['result_count']; ?></div>
                <div class="stat-mini-label">Result Records</div>
            </div>
        </div>
        
        <?php if (!empty($subject['description'])): ?>
            <h4 style="margin-top: 1.5rem;"><i class="fas fa-align-left"></i> Description</h4>
            <div class="description-box">
                <?php echo nl2br(htmlspecialchars($subject['description'])); ?>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Assigned Teachers -->
    <div class="detail-card" style="grid-column: 1 / -1;">
        <h4><i class="fas fa-users"></i> Assigned Teachers (<?php echo count($subject['teachers']); ?>)</h4>
        
        <?php if (!empty($subject['teachers'])): ?>
            <ul class="teacher-list">
                <?php foreach ($subject['teachers'] as $teacher): ?>
                    <li class="teacher-list-item">
                        <div class="teacher-avatar">
                            <?php echo strtoupper(substr($teacher['name'], 0, 1)); ?>
                        </div>
                        <div class="teacher-info">
                            <p class="teacher-name"><?php echo htmlspecialchars($teacher['name']); ?></p>
                            <p class="teacher-email">
                                <?php echo htmlspecialchars($teacher['email'] ?? 'No email'); ?>
                                <?php if ($teacher['phone']): ?>
                                    &bull; <?php echo htmlspecialchars($teacher['phone']); ?>
                                <?php endif; ?>
                            </p>
                        </div>
                        <a href="<?php echo BASE_URL; ?>/admin/teachers/view.php?id=<?php echo $teacher['teacher_id']; ?>" 
                           class="btn btn-info btn-sm">
                            <i class="fas fa-eye"></i>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <div class="empty-teachers">
                <i class="fas fa-user-slash"></i>
                <p>No teachers assigned to this subject yet.</p>
                <a href="<?php echo BASE_URL; ?>/admin/subjects/edit.php?id=<?php echo $subjectId; ?>" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Assign Teachers
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/admin_footer.php'; ?>
