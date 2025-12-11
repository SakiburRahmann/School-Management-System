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
            
            // Update password directly
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

// Filter and Search
$role = $_GET['role'] ?? 'All';
$search = $_GET['search'] ?? '';

// Build Query
$db = Database::getInstance()->getConnection();
$query = "SELECT * FROM users WHERE 1=1";
$params = [];

if ($role !== 'All') {
    $query .= " AND role = :role";
    $params['role'] = $role;
}

if ($search) {
    $query .= " AND (username LIKE :search OR role LIKE :search)";
    $params['search'] = "%$search%";
}

// Sorting
// Sort by role first, then by username (which often contains the ID)
$query .= " ORDER BY role ASC, username ASC";

$stmt = $db->prepare($query);
$stmt->execute($params);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);


?>

<div class="card">
    <div class="card-header">
        <h3>User Management</h3>
    </div>
    <div class="card-body">
        
        <!-- Filters and Search -->
        <div class="row" style="margin-bottom: 1.5rem; display: flex; gap: 1rem; flex-wrap: wrap; align-items: center; justify-content: space-between;">
            <div class="filters">
                <div class="btn-group">
                    <a href="?role=All" class="btn <?php echo $role === 'All' ? 'btn-primary' : 'btn-secondary'; ?>">All</a>
                    <a href="?role=Student" class="btn <?php echo $role === 'Student' ? 'btn-primary' : 'btn-secondary'; ?>">Students</a>
                    <a href="?role=Teacher" class="btn <?php echo $role === 'Teacher' ? 'btn-primary' : 'btn-secondary'; ?>">Teachers</a>
                    <a href="?role=Admin" class="btn <?php echo $role === 'Admin' ? 'btn-primary' : 'btn-secondary'; ?>">Admins</a>
                </div>
            </div>
            
            <div class="search-box" style="flex: 1; max-width: 300px;">
                <form method="GET" style="display: flex; gap: 0.5rem;">
                    <?php if ($role !== 'All'): ?><input type="hidden" name="role" value="<?php echo htmlspecialchars($role); ?>"><?php endif; ?>
                    <input type="text" name="search" class="form-control" placeholder="Search users..." value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
                </form>
            </div>
        </div>

        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Username / ID</th>
                        <th>Role</th>
                        <th>Password</th>
                        <th>Status</th>
                        <th>Last Login</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                        <tr><td colspan="7" class="text-center">No users found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($user['username']); ?></strong>
                                </td>
                                <td>
                                    <span class="badge badge-<?php echo $user['role'] === 'Admin' ? 'danger' : ($user['role'] === 'Teacher' ? 'primary' : 'success'); ?>">
                                        <?php echo $user['role']; ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="text-muted" title="Hashed for security">********</span>
                                </td>
                                <td>
                                    <?php echo getStatusBadge($user['status'] ?? null); ?>
                                </td>
                                <td>
                                    <?php echo isset($user['last_login']) && $user['last_login'] ? formatDate($user['last_login'], 'd M Y, h:i A') : '<span class="text-muted">Never</span>'; ?>
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
                    <?php endif; ?>
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
                <div class="input-group" style="display: flex;">
                    <input type="password" name="new_password" id="resetNewPassword" class="form-control" required minlength="6" style="border-top-right-radius: 0; border-bottom-right-radius: 0;">
                    <div class="input-group-append">
                        <button type="button" class="btn btn-outline-secondary" onclick="togglePasswordVisibility()" style="border-top-left-radius: 0; border-bottom-left-radius: 0; border: 1px solid #ced4da; border-left: none;">
                            <i class="fas fa-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                </div>
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

function togglePasswordVisibility() {
    const passwordInput = document.getElementById('resetNewPassword');
    const toggleIcon = document.getElementById('toggleIcon');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleIcon.classList.remove('fa-eye');
        toggleIcon.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        toggleIcon.classList.remove('fa-eye-slash');
        toggleIcon.classList.add('fa-eye');
    }
}
</script>

<?php require_once __DIR__ . '/../../includes/admin_footer.php'; ?>
