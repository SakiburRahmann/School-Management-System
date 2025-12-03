<?php
/**
 * Admin - Admission Requests
 */

$pageTitle = 'Admission Requests';
require_once __DIR__ . '/../../includes/admin_header.php';

$admissionModel = new AdmissionRequest();

// Handle status updates
if (isset($_GET['action']) && isset($_GET['id'])) {
    $requestId = $_GET['id'];
    $status = $_GET['action'] === 'approve' ? 'Approved' : 'Rejected';
    
    if ($admissionModel->updateStatus($requestId, $status)) {
        setFlash('success', "Application $status successfully!");
    } else {
        setFlash('danger', 'Failed to update status.');
    }
    redirect(BASE_URL . '/admin/admissions/');
}

// Get all requests
$requests = $admissionModel->getAll();
?>

<div class="card">
    <div class="card-header">
        <h3>Online Admission Applications</h3>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Student Name</th>
                        <th>Class</th>
                        <th>Guardian</th>
                        <th>Contact</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($requests)): ?>
                        <?php foreach ($requests as $request): ?>
                            <tr>
                                <td><?php echo formatDate($request['created_at']); ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($request['student_name']); ?></strong>
                                    <br>
                                    <small><?php echo htmlspecialchars($request['gender']); ?>, <?php echo formatDate($request['date_of_birth']); ?></small>
                                </td>
                                <td><?php echo htmlspecialchars($request['class_name'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($request['guardian_name']); ?></td>
                                <td><?php echo htmlspecialchars($request['guardian_phone']); ?></td>
                                <td>
                                    <?php
                                    $badgeClass = match($request['status']) {
                                        'Approved' => 'success',
                                        'Rejected' => 'danger',
                                        default => 'warning'
                                    };
                                    ?>
                                    <span class="badge badge-<?php echo $badgeClass; ?>">
                                        <?php echo $request['status']; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($request['status'] === 'Pending'): ?>
                                        <div style="display: flex; gap: 0.5rem;">
                                            <a href="<?php echo BASE_URL; ?>/admin/admissions/?action=approve&id=<?php echo $request['request_id']; ?>" 
                                               class="btn btn-success btn-sm"
                                               onclick="return confirm('Approve this application?');">
                                                <i class="fas fa-check"></i>
                                            </a>
                                            <a href="<?php echo BASE_URL; ?>/admin/admissions/?action=reject&id=<?php echo $request['request_id']; ?>" 
                                               class="btn btn-danger btn-sm"
                                               onclick="return confirm('Reject this application?');">
                                                <i class="fas fa-times"></i>
                                            </a>
                                        </div>
                                    <?php else: ?>
                                        <span style="color: #999;">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 2rem;">
                                No admission requests found.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/admin_footer.php'; ?>
