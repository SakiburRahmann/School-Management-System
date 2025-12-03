<?php
session_start();
require_once __DIR__ . '/../core/models/Student.php';
require_once __DIR__ . '/../core/models/ClassModel.php';

// Only admin access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit;
}

// GET student ID
if (!isset($_GET['id'])) {
    die("Invalid Student ID");
}

$student_id = $_GET['id'];
$studentModel = new Student();
$classModel = new ClassModel();

$student = $studentModel->find($student_id);

if (!$student) {
    die("Student not found!");
}

$message = "";
$error = "";

// Update student info
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $dob = $_POST['dob'];
    $gender = $_POST['gender'];
    $address = $_POST['address'];
    $class_id = $_POST['class_id'];
    $admission_date = $_POST['admission_date'];

    $data = [
        'first_name' => $first_name,
        'last_name' => $last_name,
        'email' => $email,
        'dob' => $dob,
        'gender' => $gender,
        'address' => $address,
        'class_id' => $class_id,
        'admission_date' => $admission_date
    ];

    if ($studentModel->update($student_id, $data)) {
        $message = "Student updated successfully!";
        // Refresh data
        $student = $studentModel->find($student_id);
    } else {
        $error = "Error updating student.";
    }
}

// Fetch classes
$classes = $classModel->findAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Student</title>
    <link rel="stylesheet" href="../style/style.css">
</head>
<body>

<?php include('../includes/header.php'); ?>

<main>
    <h2>Edit Student</h2>

    <?php if ($message != "") echo "<p class='success'>$message</p>"; ?>
    <?php if ($error != "") echo "<p class='error'>$error</p>"; ?>

    <form method="POST">

        <label>First Name:</label><br>
        <input type="text" name="first_name" value="<?= $student['first_name']; ?>" required><br><br>

        <label>Last Name:</label><br>
        <input type="text" name="last_name" value="<?= $student['last_name']; ?>" required><br><br>

        <label>Email:</label><br>
        <input type="email" name="email" value="<?= $student['email']; ?>"><br><br>

        <label>Date of Birth:</label><br>
        <input type="date" name="dob" value="<?= $student['dob']; ?>"><br><br>

        <label>Gender:</label><br>
        <select name="gender">
            <option value="Male" <?= $student['gender'] == 'Male' ? 'selected' : ''; ?>>Male</option>
            <option value="Female" <?= $student['gender'] == 'Female' ? 'selected' : ''; ?>>Female</option>
            <option value="Other" <?= $student['gender'] == 'Other' ? 'selected' : ''; ?>>Other</option>
        </select><br><br>

        <label>Address:</label><br>
        <textarea name="address"><?= $student['address']; ?></textarea><br><br>

        <label>Class:</label><br>
        <select name="class_id" required>
            <?php foreach ($classes as $c) { ?>
                <option value="<?= $c['class_id']; ?>"
                    <?= $c['class_id'] == $student['class_id'] ? 'selected' : '' ?>>
                    <?= $c['class_name'] . " (" . $c['section'] . ")"; ?>
                </option>
            <?php } ?>
        </select><br><br>

        <label>Admission Date:</label><br>
        <input type="date" name="admission_date" value="<?= $student['admission_date']; ?>"><br><br>

        <button type="submit">Update</button>
    </form>

</main>

<?php include('../includes/footer.php'); ?>

</body>
</html>
