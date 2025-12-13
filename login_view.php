<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo SITE_NAME; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/auth.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h1>School Management System</h1>
                <p>Sign in to continue</p>
            </div>
            
            <?php if ($flash = getFlash()): ?>
                <div class="alert alert-<?php echo $flash['type']; ?>">
                    <?php echo $flash['message']; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="<?php echo BASE_URL; ?>/login.php" class="auth-form">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required autofocus>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required autocomplete="current-password">
                </div>
                
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="remember_me">
                        <span>Remember me</span>
                    </label>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">Sign In</button>
            </form>
            
            <div class="auth-footer">
                <a href="<?php echo BASE_URL; ?>/public/index.php">← Back to Website</a>
            </div>
        </div>
        
        <div class="auth-info">
            <h2>Welcome Back!</h2>
            <p>Access your dashboard to manage students, teachers, classes, and more.</p>
            <div class="demo-credentials">
                <h3>Demo Credentials</h3>
                <p><strong>Admin:</strong> admin / admin123</p>
            </div>
        </div>
    </div>
</body>
</html>
