<?php
session_start();
include('config.php');

// Redirect if not logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

// Current user info
$username = $_SESSION['username'];
$role = $_SESSION['role'];
?>

<!DOCTYPE html>
<html>
<head>
    <title><?php echo $role; ?> Dashboard - SchoolMS</title>
    <link rel="stylesheet" href="style/style.css">
</head>
<body>

<?php include('includes/header.php'); ?>

<main>
    <h2>Welcome, <?php echo $username; ?> (<?php echo $role; ?>)</h2>

    <p>This is your dashboard. Choose an option below:</p>

    <?php if ($role == "Admin"): ?>
        <h3>Admin Panel</h3>
        <ul>
            <li><a href="pages/manage_students.php">Manage Students</a></li>
            <li><a href="pages/manage_teachers.php">Manage Teachers</a></li>
            <li><a href="pages/manage_classes.php">Manage Classes</a></li>
            <li><a href="pages/manage_sections.php">Manage Sections</a></li>
            <li><a href="pages/manage_subjects.php">Manage Subjects</a></li>
        </ul>

    <?php elseif ($role == "Teacher"): ?>
        <h3>Teacher Panel</h3>
        <ul>
            <li><a href="pages/view_students.php">View My Students</a></li>
            <li><a href="pages/attendance.php">Take Attendance</a></li>
            <li><a href="pages/marks.php">Enter Marks</a></li>
        </ul>

    <?php elseif ($role == "Student"): ?>
        <h3>Student Panel</h3>
        <ul>
            <li><a href="pages/my_attendance.php">My Attendance</a></li>
            <li><a href="pages/my_results.php">My Results</a></li>
            <li><a href="pages/my_fees.php">My Fees</a></li>
        </ul>

    <?php endif; ?>

    <br>
    <a href="logout.php">Logout</a>

</main>

<?php include('includes/footer.php'); ?>

</body>
</html>
