<header>
    <div class="header-container">
        <h1><a href="/dashboard.php" style="text-decoration:none; color:inherit;">SchoolMS</a></h1>
        <nav>
            <?php if (isset($_SESSION['username'])): ?>
                <a href="/dashboard.php">Dashboard</a>
                <?php if ($_SESSION['role'] === 'Admin'): ?>
                    <a href="/pages/manage_students.php">Students</a>
                    <a href="/pages/manage_teachers.php">Teachers</a>
                    <a href="/pages/manage_classes.php">Classes</a>
                <?php elseif ($_SESSION['role'] === 'Teacher'): ?>
                    <a href="/pages/view_students.php">My Students</a>
                    <a href="/pages/attendance.php">Attendance</a>
                <?php elseif ($_SESSION['role'] === 'Student'): ?>
                    <a href="/pages/my_results.php">Results</a>
                <?php endif; ?>
                <a href="/logout.php">Logout (<?= htmlspecialchars($_SESSION['username']); ?>)</a>
            <?php else: ?>
                <a href="/index.php">Home</a>
                <a href="/login.php">Login</a>
                <a href="/signup.php">Signup</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
<hr>