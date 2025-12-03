<!DOCTYPE html>
<html>

<head>
    <title>Login - SchoolMS</title>
    <link rel="stylesheet" href="/style/style.css">
</head>

<body>
    <div class="auth-container">
        <h2>Login to School Management System</h2>

        <?php if (!empty($error)): ?>
            <p class="error"><?= htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <form method="POST" action="/login.php">
            <label>Username</label>
            <input type="text" name="username" required>

            <label>Password</label>
            <input type="password" name="password" required>

            <button type="submit">Login</button>
        </form>

        <p class="auth-link">
            Don't have an account? <a href="/signup.php">Signup</a>
        </p>
    </div>
</body>

</html>