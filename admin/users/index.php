<?php
/**
 * Admin - User Management
 */

$pageTitle = 'User Management';
require_once __DIR__ . '/../../includes/admin_header.php';

$userModel = new User();

// Handle password reset
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'reset_password') {
    if (verifyCSRFToken($_POST['csrf_token'])) {
        $userId = $_POST['user_id'];
        $newPassword = $_POST['new_password'];
        
        if ($userId && $newPassword) {
            // Hash password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            
            // Update password directly (quick fix for now, ideally should use model method)
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("UPDATE users SET password = :password WHERE user_id = :user_id");
            if ($stmt->execute(['password' => $hashedPassword, 'user_id' => $userId])) {
                setFlash('success', 'Password reset successfully!');
            } else {
                setFlash('danger', 'Failed to reset password.');
            }
        }
    }
    redirect(BASE_URL . '/admin/users/');
}

// Get all users
$users = $userModel->findAll();
?>

<div class="card">
    <div class="card-header">
        <h3>All Users</h3>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($user['username']); ?></strong></td>
                            <td>
                                <span class="badge badge-<?php echo $user['role'] === 'Admin' ? 'danger' : ($user['role'] === 'Teacher' ? 'primary' : 'success'); ?>">
                                    <?php echo $user['role']; ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-<?php echo $user['status'] === 'Active' ? 'success' : 'secondary'; ?>">
                                    <?php echo $user['status']; ?>
                                </span>
                            </td>
                            <td><?php echo formatDate($user['created_at']); ?></td>
                            <td>
                                <button type="button" class="btn btn-warning btn-sm" 
                                        onclick="showResetModal(<?php echo $user['user_id']; ?>, '<?php echo $user['username']; ?>')">
                                    <i class="fas fa-key"></i> Reset Password
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Reset Password Modal -->
<div id="resetModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div style="background: white; padding: 2rem; border-radius: 10px; width: 100%; max-width: 400px;">
        <h3 style="margin-top: 0;">Reset Password</h3>
        <p>Reset password for user: <strong id="modalUsername"></strong></p>
        
        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            <input type="hidden" name="action" value="reset_password">
            <input type="hidden" name="user_id" id="modalUserId">
            
            <div class="form-group">
                <label>New Password</label>
                <input type="password" name="new_password" class="form-control" required minlength="6">
            </div>
            
            <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                <button type="submit" class="btn btn-primary" style="flex: 1;">Save</button>
                <button type="button" class="btn btn-secondary" style="flex: 1;" onclick="hideResetModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function showResetModal(userId, username) {
    document.getElementById('modalUserId').value = userId;
    document.getElementById('modalUsername').textContent = username;
    document.getElementById('resetModal').style.display = 'flex';
}

function hideResetModal() {
    document.getElementById('resetModal').style.display = 'none';
}
</script>

<?php require_once __DIR__ . '/../../includes/admin_footer.php'; ?>
