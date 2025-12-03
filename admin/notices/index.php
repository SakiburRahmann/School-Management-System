<?php
/**
 * Admin - Notice Management
 */

$pageTitle = 'Manage Notices';
require_once __DIR__ . '/../../includes/admin_header.php';

$noticeModel = new Notice();

// Handle notice creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_notice') {
    if (verifyCSRFToken($_POST['csrf_token'])) {
        $data = [
            'title' => sanitize($_POST['title']),
            'content' => sanitize($_POST['content']),
            'is_public' => isset($_POST['is_public']) ? 1 : 0,
            'priority' => $_POST['priority'],
            'created_by' => getUserId()
        ];
        
        if (!empty($data['title']) && !empty($data['content'])) {
            $noticeId = $noticeModel->create($data);
            if ($noticeId) {
                setFlash('success', 'Notice added successfully!');
            } else {
                setFlash('danger', 'Failed to add notice.');
            }
        }
    }
    redirect(BASE_URL . '/admin/notices/');
}

// Handle notice deletion
if (isset($_GET['delete']) && isset($_GET['id'])) {
    $noticeId = $_GET['id'];
    if ($noticeModel->delete($noticeId)) {
        setFlash('success', 'Notice deleted successfully!');
    } else {
        setFlash('danger', 'Failed to delete notice.');
    }
    redirect(BASE_URL . '/admin/notices/');
}

// Get all notices
$notices = $noticeModel->getNoticesWithDetails();
?>

<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-header">
        <h3>Add New Notice</h3>
    </div>
    <div class="card-body">
        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            <input type="hidden" name="action" value="add_notice">
            
            <div class="form-group">
                <label for="title">Notice Title <span style="color: red;">*</span></label>
                <input type="text" id="title" name="title" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label for="content">Notice Content <span style="color: red;">*</span></label>
                <textarea id="content" name="content" class="form-control" rows="4" required></textarea>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label for="priority">Priority</label>
                    <select id="priority" name="priority" class="form-control">
                        <option value="Low">Low</option>
                        <option value="Medium" selected>Medium</option>
                        <option value="High">High</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="is_public" value="1" checked>
                        <span>Show on public website</span>
                    </label>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add Notice
            </button>
        </form>
    </div>
</div>

<!-- Notices List -->
<div class="card">
    <div class="card-header">
        <h3>All Notices</h3>
    </div>
    <div class="card-body">
        <?php if (!empty($notices)): ?>
            <div style="display: flex; flex-direction: column; gap: 1rem;">
                <?php foreach ($notices as $notice): ?>
                    <div style="border: 2px solid var(--light); border-radius: 10px; padding: 1.5rem;">
                        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.75rem;">
                            <div style="flex: 1;">
                                <h4 style="margin: 0 0 0.5rem 0; color: var(--primary);">
                                    <?php echo htmlspecialchars($notice['title']); ?>
                                    <?php if ($notice['priority'] === 'High'): ?>
                                        <span class="badge badge-danger">Important</span>
                                    <?php endif; ?>
                                    <?php if ($notice['is_public']): ?>
                                        <span class="badge badge-success">Public</span>
                                    <?php endif; ?>
                                </h4>
                                <p style="margin: 0; color: #666;">
                                    <?php echo nl2br(htmlspecialchars($notice['content'])); ?>
                                </p>
                            </div>
                            <div style="display: flex; gap: 0.5rem;">
                                <a href="<?php echo BASE_URL; ?>/admin/notices/?delete=1&id=<?php echo $notice['notice_id']; ?>" 
                                   class="btn btn-danger btn-sm"
                                   onclick="return confirmDelete('Delete this notice?');">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </div>
                        <div style="font-size: 0.875rem; color: #999;">
                            <i class="fas fa-user"></i> <?php echo htmlspecialchars($notice['created_by_name'] ?? 'System'); ?>
                            &nbsp;|&nbsp;
                            <i class="fas fa-calendar"></i> <?php echo formatDateTime($notice['created_at']); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p style="text-align: center; padding: 2rem; color: #999;">No notices found.</p>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/admin_footer.php'; ?>
