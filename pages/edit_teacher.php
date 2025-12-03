<?php
session_start();
require_once __DIR__ . '/../core/models/Teacher.php';

// Only admin access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit;
}

// GET teacher ID
if (!isset($_GET['id'])) {
    die("Invalid Teacher ID");
}

$teacher_id = $_GET['id'];
$teacherModel = new Teacher();

$teacher = $teacherModel->find($teacher_id);

if (!$teacher) {
    die("Teacher not found!");
}

$message = "";
$error = "";

// Update teacher info
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $qualification = $_POST['qualification'];
    $hire_date = $_POST['hire_date'];

    $data = [
        'first_name' => $first_name,
        'last_name' => $last_name,
        'email' => $email,
        'phone' => $phone,
        'qualification' => $qualification,
        'hire_date' => $hire_date
    ];

    if ($teacherModel->update($teacher_id, $data)) {
        $message = "Teacher updated successfully!";
        // Refresh data
        $teacher = $teacherModel->find($teacher_id);
    } else {
        $error = "Error updating teacher.";
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Edit Teacher</title>
    <link rel="stylesheet" href="../style/style.css">
</head>

<body>

    <?php include('../includes/header.php'); ?>

    <main>
        <h2>Edit Teacher</h2>

        <?php if ($message != "") echo "<p class='success'>$message</p>"; ?>
        <?php if ($error != "") echo "<p class='error'>$error</p>"; ?>

        <form method="POST">
            <label>First Name:</label><br>
            <input type="text" name="first_name" value="<?= $teacher['first_name']; ?>" required><br><br>

            <label>Last Name:</label><br>
            <input type="text" name="last_name" value="<?= $teacher['last_name']; ?>" required><br><br>

            <label>Email:</label><br>
            <input type="email" name="email" value="<?= $teacher['email']; ?>" required><br><br>

            <label>Phone:</label><br>
            <input type="text" name="phone" value="<?= $teacher['phone']; ?>"><br><br>

            <label>Qualification:</label><br>
            <input type="text" name="qualification" value="<?= $teacher['qualification']; ?>"><br><br>

            <label>Hire Date:</label><br>
            <input type="date" name="hire_date" value="<?= $teacher['hire_date']; ?>"><br><br>

            <button type="submit">Update Teacher</button>
        </form>

    </main>

    <?php include('../includes/footer.php'); ?>

</body>

</html>