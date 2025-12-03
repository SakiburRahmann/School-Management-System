<?php
session_start();
require_once __DIR__ . '/../core/models/Student.php';
require_once __DIR__ . '/../core/models/ClassModel.php';

// Only admin can access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit;
}

$studentModel = new Student();
$classModel = new ClassModel();

$message = "";
$error = "";

// Add New Student
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

    if ($studentModel->insert($data)) {
        $message = "Student added successfully!";
    } else {
        $error = "Error adding student.";
    }
}

// Fetch all classes for dropdown
$classes = $classModel->findAll();

// Fetch all students with class info
$students = $studentModel->query("
    SELECT students.*, classes.class_name, classes.section 
    FROM students 
    LEFT JOIN classes ON students.class_id = classes.class_id 
    ORDER BY students.student_id DESC
");
?>

<!DOCTYPE html>
<html>

<head>
    <title>Manage Students</title>
    <link rel="stylesheet" href="../style/style.css">
</head>

<body>

    <?php include('../includes/header.php'); ?>

    <main>
        <h2>Manage Students</h2>

        <?php if ($message != "") echo "<p class='success'>$message</p>"; ?>
        <?php if ($error != "") echo "<p class='error'>$error</p>"; ?>

        <!-- ADD STUDENT FORM -->
        <h3>Add New Student</h3>

        <form method="POST" action="">
            <label>First Name:</label><br>
            <input type="text" name="first_name" required><br><br>

            <label>Last Name:</label><br>
            <input type="text" name="last_name" required><br><br>

            <label>Email:</label><br>
            <input type="email" name="email"><br><br>

            <label>Date of Birth:</label><br>
            <input type="date" name="dob"><br><br>

            <label>Gender:</label><br>
            <select name="gender">
                <option value="Male">Male</option>
                <option value="Female">Female</option>
                <option value="Other">Other</option>
            </select><br><br>

            <label>Address:</label><br>
            <textarea name="address"></textarea><br><br>

            <label>Class:</label><br>
            <select name="class_id" required>
                <option value="">Select Class</option>
                <?php foreach ($classes as $c) { ?>
                    <option value="<?= $c['class_id']; ?>">
                        <?= $c['class_name'] . " (" . $c['section'] . ")"; ?>
                    </option>
                <?php } ?>
            </select><br><br>

            <label>Admission Date:</label><br>
            <input type="date" name="admission_date" value="<?= date('Y-m-d'); ?>"><br><br>

            <button type="submit">Add Student</button>
        </form>

        <hr>

        <!-- STUDENT LIST -->
        <h3>All Students</h3>

        <table border="1" cellpadding="10" style="width:100%; background:white;">
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Class</th>
                <th>DOB</th>
                <th>Gender</th>
                <th>Actions</th>
            </tr>

            <?php while ($row = $students->fetch_assoc()) { ?>
                <tr>
                    <td><?= $row['student_id']; ?></td>
                    <td><?= $row['first_name'] . " " . $row['last_name']; ?></td>
                    <td><?= $row['email']; ?></td>
                    <td><?= $row['class_name'] . " (" . $row['section'] . ")"; ?></td>
                    <td><?= $row['dob']; ?></td>
                    <td><?= $row['gender']; ?></td>

                    <td>
                        <a href="edit_student.php?id=<?= $row['student_id']; ?>">Edit</a> |
                        <a href="delete_student.php?id=<?= $row['student_id']; ?>"
                            onclick="return confirm('Are you sure you want to delete this student?');">
                            Delete
                        </a>
                    </td>
                </tr>

            <?php } ?>
        </table>

    </main>

    <?php include('../includes/footer.php'); ?>

</body>

</html>