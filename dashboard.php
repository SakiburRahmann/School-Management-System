<?php
session_start();
include('config.php');
require_once __DIR__ . '/core/models/Student.php';
require_once __DIR__ . '/core/models/Teacher.php';
require_once __DIR__ . '/core/models/ClassModel.php';
require_once __DIR__ . '/core/models/Attendance.php';

// Redirect if not logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

// Current user info
$username = $_SESSION['username'];
$role = $_SESSION['role'];

// Basic stats for admin dashboard
$studentCount = 0;
$teacherCount = 0;
$classCount = 0;
$todayAttendance = 0;

if ($role === 'Admin') {
    $studentModel = new Student();
    $teacherModel = new Teacher();
    $classModel = new ClassModel();
    $attendanceModel = new Attendance();

    $studentCount = $studentModel->countAll();
    $teacherCount = $teacherModel->countAll();
    $classCount = $classModel->countAll();
    $todayAttendance = $attendanceModel->countToday();
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $role; ?> Dashboard - SchoolMS</title>
    <link rel="stylesheet" href="style/style.css">
</head>

<body>

    <?php include('includes/header.php'); ?>

    <main>
        <h2>Dashboard</h2>
        <p>Welcome, <?= $_SESSION['username']; ?>! You are logged in as <?= $_SESSION['role']; ?>.</p>

        <?php if ($role == "Admin"): ?>
            <section class="dashboard-grid">
                <article class="stat-card">
                    <h3>Total Students</h3>
                    <div class="stat-value"><?= $studentCount; ?></div>
                </article>
                <article class="stat-card" style="background: linear-gradient(135deg, #10b981, #059669);">
                    <h3>Total Teachers</h3>
                    <div class="stat-value"><?= $teacherCount; ?></div>
                </article>
                <article class="stat-card" style="background: linear-gradient(135deg, #f59e0b, #d97706);">
                    <h3>Total Classes</h3>
                    <div class="stat-value"><?= $classCount; ?></div>
                </article>
                <article class="stat-card" style="background: linear-gradient(135deg, #8b5cf6, #7c3aed);">
                    <h3>Today's Attendance</h3>
                    <div class="stat-value"><?= $todayAttendance; ?></div>
                </article>
            </section>

            <section class="section">
                <div class="section-header">
                    <h3 class="section-title">Admin Panel</h3>
                </div>
                <div class="grid grid-cols-3">
                    <a href="pages/manage_students.php" class="card" style="text-decoration: none;">
                        <h4>👨‍🎓 Manage Students</h4>
                        <p>Add, edit, and manage student records</p>
                    </a>
                    <a href="pages/manage_teachers.php" class="card" style="text-decoration: none;">
                        <h4>👨‍🏫 Manage Teachers</h4>
                        <p>Add, edit, and manage teacher profiles</p>
                    </a>
                    <a href="pages/manage_classes.php" class="card" style="text-decoration: none;">
                        <h4>🏫 Manage Classes</h4>
                        <p>Create and organize classes</p>
                    </a>
                    <a href="pages/manage_subjects.php" class="card" style="text-decoration: none;">
                        <h4>📚 Manage Subjects</h4>
                        <p>Add and assign subjects</p>
                    </a>
                    <a href="pages/attendance.php" class="card" style="text-decoration: none;">
                        <h4>📋 Attendance</h4>
                        <p>View and manage attendance records</p>
                    </a>
                    <a href="pages/marks.php" class="card" style="text-decoration: none;">
                        <h4>📊 Marks & Results</h4>
                        <p>Enter and view student marks</p>
                    </a>
                </div>
            </section>

        <?php elseif ($role == "Teacher"): ?>
            <section class="section">
                <div class="section-header">
                    <h3 class="section-title">Teacher Panel</h3>
                </div>
                <div class="grid grid-cols-2">
                    <a href="pages/view_students.php" class="card" style="text-decoration: none;">
                        <h4>👨‍🎓 View My Students</h4>
                        <p>See students in your classes</p>
                    </a>
                    <a href="pages/attendance.php" class="card" style="text-decoration: none;">
                        <h4>📋 Take Attendance</h4>
                        <p>Mark student attendance</p>
                    </a>
                    <a href="pages/marks.php" class="card" style="text-decoration: none;">
                        <h4>📝 Enter Marks</h4>
                        <p>Record student grades</p>
                    </a>
                </div>
            </section>

        <?php elseif ($role == "Student"): ?>
            <section class="section">
                <div class="section-header">
                    <h3 class="section-title">Student Panel</h3>
                </div>
                <div class="grid grid-cols-2">
                    <a href="pages/my_attendance.php" class="card" style="text-decoration: none;">
                        <h4>📋 My Attendance</h4>
                        <p>View your attendance record</p>
                    </a>
                    <a href="pages/my_results.php" class="card" style="text-decoration: none;">
                        <h4>📊 My Results</h4>
                        <p>Check your exam results</p>
                    </a>
                    <a href="pages/my_fees.php" class="card" style="text-decoration: none;">
                        <h4>💰 My Fees</h4>
                        <p>View fee payment status</p>
                    </a>
                </div>
            </section>

        <?php endif; ?>

    </main>

    <?php include('includes/footer.php'); ?>

</body>

</html>