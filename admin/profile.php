<?php
/**
 * Admin Profile Management
 * Allows admin to update username and password
 */

require_once __DIR__ . '/../config.php';

$userModel = new User();
$currentUser = $userModel->find($_SESSION['user_id']);
$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'])) {
        $errors[] = 'Invalid request token.';
    } else {
        $username = trim(sanitize($_POST['username']));
        $newPassword = $_POST['new_password'];
        $confirmPassword = $_POST['confirm_password'];
        
        // Validate Username
        if (empty($username)) {
            $errors[] = 'Username cannot be empty.';
        } elseif ($userModel->usernameExists($username, $_SESSION['user_id'])) {
            $errors[] = 'Username already taken.';
        }
        
        // Validate Password if provided
        if (!empty($newPassword)) {
            if (strlen($newPassword) < 6) {
                $errors[] = 'Password must be at least 6 characters long.';
            }
            if ($newPassword !== $confirmPassword) {
                $errors[] = 'Passwords do not match.';
            }
        }
        
        if (empty($errors)) {
            $updateData = ['username' => $username];
            if (!empty($newPassword)) {
                $updateData['password'] = password_hash($newPassword, PASSWORD_BCRYPT);
            }
            
            if ($userModel->update($_SESSION['user_id'], $updateData)) {
                $success = 'Profile updated successfully!';
                // Refresh user data
                $currentUser = $userModel->find($_SESSION['user_id']);
                $_SESSION['username'] = $currentUser['username']; // Update session
            } else {
                $errors[] = 'Failed to update profile.';
            }
        }
    }
}

$pageTitle = 'My Profile';
require_once __DIR__ . '/../includes/admin_header.php';
?>

<div class="profile-container">
    <div class="profile-header-card">
        <div class="profile-cover"></div>
        <div class="profile-info">
            <div class="profile-avatar">
                <i class="fas fa-user-shield"></i>
            </div>
            <div class="profile-text">
                <h2><?php echo htmlspecialchars($currentUser['username']); ?></h2>
                <span class="role-badge"><i class="fas fa-crown"></i> Administrator</span>
            </div>
        </div>
    </div>

    <div class="profile-content">
        <div class="card profile-edit-card">
            <div class="card-header">
                <h3><i class="fas fa-user-edit"></i> Edit Profile</h3>
                <p class="text-muted">Update your account credentials</p>
            </div>
            <div class="card-body">
                <?php if (!empty($errors)): ?>
                    <div class="validation-alert validation-alert-danger">
                        <div class="validation-alert-icon">
                            <i class="fas fa-exclamation-circle"></i>
                        </div>
                        <div class="validation-alert-content">
                            <ul>
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="validation-alert validation-alert-success">
                        <div class="validation-alert-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="validation-alert-content">
                            <?php echo htmlspecialchars($success); ?>
                        </div>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" class="modern-form">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    
                    <div class="form-group">
                        <label for="username">Username</label>
                        <div class="input-group">
                            <span class="input-icon"><i class="fas fa-user"></i></span>
                            <input type="text" id="username" name="username" class="form-control" 
                                   value="<?php echo htmlspecialchars($currentUser['username']); ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group half">
                            <label for="new_password">New Password</label>
                            <div class="input-group">
                                <span class="input-icon"><i class="fas fa-lock"></i></span>
                                <input type="password" id="new_password" name="new_password" class="form-control" 
                                       placeholder="Leave blank to keep current">
                            </div>
                        </div>
                        
                        <div class="form-group half">
                            <label for="confirm_password">Confirm Password</label>
                            <div class="input-group">
                                <span class="input-icon"><i class="fas fa-check-double"></i></span>
                                <input type="password" id="confirm_password" name="confirm_password" class="form-control" 
                                       placeholder="Confirm new password">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save"></i> Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.profile-container {
    max-width: 900px;
    margin: 0 auto;
}

.profile-header-card {
    background: white;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0,0,0,0.05);
    margin-bottom: 2rem;
    position: relative;
}

.profile-cover {
    height: 150px;
    background: linear-gradient(135deg, var(--primary), var(--secondary));
    position: relative;
}

.profile-cover::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 50%;
    background: linear-gradient(to top, rgba(0,0,0,0.2), transparent);
}

.profile-info {
    padding: 0 2rem 2rem;
    display: flex;
    align-items: flex-end;
    margin-top: -50px;
    position: relative;
    z-index: 2;
}

.profile-avatar {
    width: 100px;
    height: 100px;
    background: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 3rem;
    color: var(--primary);
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    border: 4px solid white;
    margin-right: 1.5rem;
}

.profile-text h2 {
    margin: 0;
    font-size: 1.75rem;
    font-weight: 700;
    color: #333;
}

.role-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    background: #f3f4f6;
    color: #4b5563;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
    margin-top: 0.5rem;
}

.profile-edit-card {
    border: none;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.05);
}

.profile-edit-card .card-header {
    background: white;
    border-bottom: 1px solid #f0f0f0;
    padding: 1.5rem 2rem;
    border-radius: 16px 16px 0 0;
}

.profile-edit-card .card-header h3 {
    margin: 0;
    font-size: 1.25rem;
    color: var(--primary);
}

.profile-edit-card .card-header p {
    margin: 0.25rem 0 0;
    font-size: 0.9rem;
}

.profile-edit-card .card-body {
    padding: 2rem;
}

.modern-form .form-group {
    margin-bottom: 1.5rem;
}

.modern-form label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: #374151;
}

.input-group {
    position: relative;
    display: flex;
    align-items: center;
}

.input-icon {
    position: absolute;
    left: 1rem;
    color: #9ca3af;
    z-index: 10;
}

.modern-form .form-control {
    padding-left: 2.75rem;
    height: 48px;
    border-radius: 8px;
    border: 1px solid #e5e7eb;
    transition: all 0.2s;
}

.modern-form .form-control:focus {
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

.form-row {
    display: flex;
    gap: 1.5rem;
}

.form-group.half {
    flex: 1;
}

.form-actions {
    margin-top: 2rem;
    display: flex;
    justify-content: flex-end;
}

.btn-lg {
    padding: 0.75rem 2rem;
    font-size: 1rem;
    border-radius: 8px;
}

@media (max-width: 768px) {
    .profile-info {
        flex-direction: column;
        align-items: center;
        text-align: center;
        margin-top: -50px;
    }
    
    .profile-avatar {
        margin-right: 0;
        margin-bottom: 1rem;
    }
    
    .form-row {
        flex-direction: column;
        gap: 0;
    }
}
</style>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
