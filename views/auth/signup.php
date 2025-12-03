<!DOCTYPE html>
<html>

<head>
    <title>Signup - SchoolMS</title>
    <link rel="stylesheet" href="/style/style.css">
</head>

<body>
    <div class="auth-container">
        <h2>Create an Account</h2>

        <?php if (!empty($error)): ?>
            <p class="error"><?= htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <?php if (!empty($message)): ?>
            <p class="success"><?= $message; ?></p>
        <?php endif; ?>

        <form method="POST" action="/signup.php">
            <label>Username</label>
            <input type="text" name="username" required>

            <label>Password</label>
            <input type="password" name="password" required>

            <label>Role</label>
            <select name="role" required>
                <option value="Student">Student</option>
                <option value="Teacher">Teacher</option>
                <option value="Admin">Admin</option>
            </select>

            <button type="submit">Signup</button>
        </form>

        <p class="auth-link">
            Already have an account? <a href="/login.php">Login</a>
        </p>
    </div>
</body>

</html>
