<?php
/**
 * Admin - Admission Requests Management
 * Modern dashboard with statistics, search, and filters
 */

require_once __DIR__ . '/../../config.php';
requireRole('Admin');

$admissionModel = new AdmissionRequest();

// Handle status updates
if (isset($_GET['action']) && isset($_GET['id'])) {
    $requestId = intval($_GET['id']);
    
    if ($_GET['action'] === 'approve') {
        $result = $admissionModel->approve($requestId);
        if ($result) {
            setFlash('success', 'Application approved successfully!');
        } else {
            setFlash('danger', 'Failed to approve application.');
        }
    } elseif ($_GET['action'] === 'reject') {
        $result = $admissionModel->reject($requestId);
        if ($result) {
            setFlash('success', 'Application rejected successfully!');
        } else {
            setFlash('danger', 'Failed to reject application.');
        }
    }
    
    redirect(BASE_URL . '/admin/admissions/');
}

// Get filter parameters
$searchQuery = $_GET['search'] ?? '';
$statusFilter = $_GET['status'] ?? '';
$classFilter = $_GET['class'] ?? '';
$dateFrom = $_GET['date_from'] ?? '';
$dateTo = $_GET['date_to'] ?? '';

// Get requests based on filters
if ($searchQuery) {
    $requests = $admissionModel->search($searchQuery, $statusFilter ?: null, $classFilter ?: null);
} elseif ($statusFilter || $classFilter || $dateFrom || $dateTo) {
    $requests = $admissionModel->getFiltered(
        $statusFilter ?: null,
        $classFilter ?: null,
        $dateFrom ?: null,
        $dateTo ?: null
    );
} else {
    $requests = $admissionModel->getAllRequests();
}

// Get statistics for dashboard
$stats = $admissionModel->getStatistics();

// Get unique classes for filter
$classes = $admissionModel->getUniqueClasses();

$pageTitle = 'Admission Requests';
require_once __DIR__ . '/../../includes/admin_header.php';
?>

<style>
/* Modern Admissions Dashboard Styles */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: linear-gradient(135deg, var(--primary), var(--secondary));
    padding: 1.5rem;
    border-radius: 16px;
    color: white;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    transition: transform 0.3s, box-shadow 0.3s;
    position: relative;
    overflow: hidden;
}

.stat-card::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
    pointer-events: none;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 40px rgba(0,0,0,0.15);
}

.stat-card.pending {
    background: linear-gradient(135deg, #f59e0b, #d97706);
}

.stat-card.approved {
    background: linear-gradient(135deg, #10b981, #059669);
}

.stat-card.rejected {
    background: linear-gradient(135deg, #ef4444, #dc2626);
}

.stat-card.total {
    background: linear-gradient(135deg, #667eea, #764ba2);
}

.stat-icon {
    font-size: 2.5rem;
    margin-bottom: 0.5rem;
    opacity: 0.9;
}

.stat-value {
    font-size: 2.5rem;
    font-weight: 700;
    margin: 0.5rem 0;
    text-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.stat-label {
    font-size: 0.9rem;
    opacity: 0.95;
    font-weight: 500;
    letter-spacing: 0.5px;
}

.filters-section {
    background: white;
    padding: 1.5rem;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    margin-bottom: 2rem;
}

.filters-grid {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr 1fr 1fr auto;
    gap: 1rem;
    align-items: end;
}

.filter-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.filter-group label {
    font-size: 0.85rem;
    font-weight: 600;
    color: #4b5563;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.filter-input,
.filter-select {
    padding: 0.75rem 1rem;
    border: 2px solid #e5e7eb;
    border-radius: 10px;
    font-size: 0.95rem;
    transition: all 0.3s;
    background: #fafafa;
}

.filter-input:focus,
.filter-select:focus {
    outline: none;
    border-color: var(--primary);
    background: white;
    box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
}

.btn-filter {
    padding: 0.75rem 1.5rem;
    background: linear-gradient(135deg, var(--primary), var(--secondary));
    color: white;
    border: none;
    border-radius: 10px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    height: fit-content;
}

.btn-filter:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
}

.btn-clear {
    padding: 0.75rem 1.5rem;
    background: #6b7280;
    color: white;
    border: none;
    border-radius: 10px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    text-decoration: none;
    display: inline-block;
    height: fit-content;
}

.btn-clear:hover {
    background: #4b5563;
    color: white;
    text-decoration: none;
}

.applications-table {
    background: white;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    overflow: hidden;
}

.applications-table table {
    width: 100%;
    border-collapse: collapse;
}

.applications-table thead {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
}

.applications-table th {
    padding: 1.25rem 1rem;
    text-align: left;
    font-weight: 600;
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.applications-table td {
    padding: 1.25rem 1rem;
    border-bottom: 1px solid #e5e7eb;
    vertical-align: middle;
}

.applications-table tbody tr {
    transition: background 0.2s;
}

.applications-table tbody tr:hover {
    background: #f9fafb;
}

.status-badge {
    display: inline-block;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.status-badge.pending {
    background: #fef3c7;
    color: #92400e;
}

.status-badge.approved {
    background: #d1fae5;
    color: #065f46;
}

.status-badge.rejected {
    background: #fee2e2;
    color: #991b1b;
}

.action-buttons {
    display: flex;
    gap: 0.5rem;
}

.btn-action {
    padding: 0.5rem 0.75rem;
    border-radius: 8px;
    border: none;
    cursor: pointer;
    transition: all 0.3s;
    font-size: 0.9rem;
    color: white;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.btn-action:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    color: white;
    text-decoration: none;
}

.btn-view {
    background: #3b82f6;
}

.btn-approve {
    background: #10b981;
}

.btn-reject {
    background: #ef4444;
}

.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    color: #6b7280;
}

.empty-state i {
    font-size: 4rem;
    margin-bottom: 1rem;
    opacity: 0.3;
}

.empty-state h3 {
    font-size: 1.5rem;
    margin-bottom: 0.5rem;
    color: #374151;
}

.student-info {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.student-name {
    font-weight: 600;
    color: #1f2937;
}

.student-meta {
    font-size: 0.85rem;
    color: #6b7280;
}

.contact-info {
    font-size: 0.9rem;
    color: #4b5563;
}

.contact-info i {
    width: 16px;
    color: #9ca3af;
    margin-right: 0.25rem;
}

@media (max-width: 1200px) {
    .filters-grid {
        grid-template-columns: 1fr 1fr;
    }
    
    .filter-group:first-child {
        grid-column: 1 / -1;
    }
}

@media (max-width: 768px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .filters-grid {
        grid-template-columns: 1fr;
    }
    
    .filter-group:first-child {
        grid-column: auto;
    }
}
</style>

<!-- Statistics Dashboard -->
<div class="stats-grid">
    <div class="stat-card total">
        <div class="stat-icon"><i class="fas fa-file-alt"></i></div>
        <div class="stat-value"><?php echo $stats['total']; ?></div>
        <div class="stat-label">Total Applications</div>
    </div>
    
    <div class="stat-card pending">
        <div class="stat-icon"><i class="fas fa-clock"></i></div>
        <div class="stat-value"><?php echo $stats['pending']; ?></div>
        <div class="stat-label">Pending Review</div>
    </div>
    
    <div class="stat-card approved">
        <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
        <div class="stat-value"><?php echo $stats['approved']; ?></div>
        <div class="stat-label">Approved</div>
    </div>
    
    <div class="stat-card rejected">
        <div class="stat-icon"><i class="fas fa-times-circle"></i></div>
        <div class="stat-value"><?php echo $stats['rejected']; ?></div>
        <div class="stat-label">Rejected</div>
    </div>
</div>

<!-- Filters Section -->
<div class="filters-section">
    <form method="GET" action="">
        <div class="filters-grid">
            <div class="filter-group">
                <label><i class="fas fa-search"></i> Search</label>
                <input type="text" 
                       name="search" 
                       class="filter-input" 
                       placeholder="Search by name, email, or phone..."
                       value="<?php echo htmlspecialchars($searchQuery); ?>">
            </div>
            
            <div class="filter-group">
                <label><i class="fas fa-filter"></i> Status</label>
                <select name="status" class="filter-select">
                    <option value="">All Status</option>
                    <option value="Pending" <?php echo $statusFilter === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="Approved" <?php echo $statusFilter === 'Approved' ? 'selected' : ''; ?>>Approved</option>
                    <option value="Rejected" <?php echo $statusFilter === 'Rejected' ? 'selected' : ''; ?>>Rejected</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label><i class="fas fa-graduation-cap"></i> Class</label>
                <select name="class" class="filter-select">
                    <option value="">All Classes</option>
                    <?php foreach ($classes as $class): ?>
                        <option value="<?php echo htmlspecialchars($class['class_applying_for']); ?>"
                                <?php echo $classFilter === $class['class_applying_for'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($class['class_applying_for']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="filter-group">
                <label><i class="fas fa-calendar"></i> From Date</label>
                <input type="date" name="date_from" class="filter-input" value="<?php echo htmlspecialchars($dateFrom); ?>">
            </div>
            
            <div class="filter-group">
                <label><i class="fas fa-calendar"></i> To Date</label>
                <input type="date" name="date_to" class="filter-input" value="<?php echo htmlspecialchars($dateTo); ?>">
            </div>
            
            <div class="filter-group">
                <label>&nbsp;</label>
                <button type="submit" class="btn-filter">
                    <i class="fas fa-search"></i> Search
                </button>
            </div>
            
            <?php if ($searchQuery || $statusFilter || $classFilter || $dateFrom || $dateTo): ?>
                <div class="filter-group">
                    <label>&nbsp;</label>
                    <a href="<?php echo BASE_URL; ?>/admin/admissions/" class="btn-clear">
                        <i class="fas fa-times"></i> Clear
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Applications Table -->
<div class="applications-table">
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Student</th>
                <th>Class</th>
                <th>Guardian</th>
                <th>Contact</th>
                <th>Status</th>
                <th style="text-align: center;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($requests)): ?>
                <?php foreach ($requests as $request): ?>
                    <tr>
                        <td>
                            <div style="font-size: 0.9rem; color: #4b5563;">
                                <?php echo date('M d, Y', strtotime($request['created_at'])); ?>
                            </div>
                        </td>
                        <td>
                            <div class="student-info">
                                <span class="student-name"><?php echo htmlspecialchars($request['student_name']); ?></span>
                                <span class="student-meta">
                                    <?php echo htmlspecialchars($request['gender']); ?> • 
                                    <?php echo date('M d, Y', strtotime($request['date_of_birth'])); ?>
                                </span>
                            </div>
                        </td>
                        <td>
                            <strong><?php echo htmlspecialchars($request['class_applying_for'] ?? 'N/A'); ?></strong>
                        </td>
                        <td>
                            <div style="font-weight: 500; color: #1f2937;">
                                <?php echo htmlspecialchars($request['guardian_name']); ?>
                            </div>
                        </td>
                        <td>
                            <div class="contact-info">
                                <div><i class="fas fa-phone"></i><?php echo htmlspecialchars($request['guardian_phone']); ?></div>
                                <?php if (!empty($request['guardian_email'])): ?>
                                    <div><i class="fas fa-envelope"></i><?php echo htmlspecialchars($request['guardian_email']); ?></div>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <span class="status-badge <?php echo strtolower($request['status']); ?>">
                                <?php echo $request['status']; ?>
                            </span>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <a href="<?php echo BASE_URL; ?>/admin/admissions/view.php?id=<?php echo $request['request_id']; ?>" 
                                   class="btn-action btn-view" 
                                   title="View Details">
                                    <i class="fas fa-eye"></i>
                                </a>
                                
                                <?php if ($request['status'] === 'Pending'): ?>
                                    <a href="?action=approve&id=<?php echo $request['request_id']; ?>" 
                                       class="btn-action btn-approve"
                                       onclick="return confirm('Are you sure you want to approve this application?');" 
                                       title="Approve">
                                        <i class="fas fa-check"></i>
                                    </a>
                                    <a href="?action=reject&id=<?php echo $request['request_id']; ?>" 
                                       class="btn-action btn-reject"
                                       onclick="return confirm('Are you sure you want to reject this application?');" 
                                       title="Reject">
                                        <i class="fas fa-times"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7">
                        <div class="empty-state">
                            <i class="fas fa-inbox"></i>
                            <h3>No Applications Found</h3>
                            <p>
                                <?php if ($searchQuery || $statusFilter || $classFilter): ?>
                                    No applications match your search criteria. <a href="<?php echo BASE_URL; ?>/admin/admissions/">Clear filters</a> to see all applications.
                                <?php else: ?>
                                    There are no admission applications yet.
                                <?php endif; ?>
                            </p>
                        </div>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php if (!empty($requests)): ?>
    <div style="margin-top: 1.5rem; text-align: center; color: #6b7280;">
        <strong>Showing <?php echo count($requests); ?> application(s)</strong>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../../includes/admin_footer.php'; ?>
