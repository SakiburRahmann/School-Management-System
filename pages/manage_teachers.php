<?php
session_start();
require_once __DIR__ . '/../core/models/Teacher.php';

// Only admin can access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit;
}

$teacherModel = new Teacher();
$message = "";
$error = "";

// Add New Teacher
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

    if ($teacherModel->insert($data)) {
        $message = "Teacher added successfully!";
    } else {
        $error = "Error adding teacher.";
    }
}

// Fetch all teachers
$teachers = $teacherModel->findAll();
?>

<!DOCTYPE html>
<html>

<head>
    <title>Manage Teachers</title>
    <link rel="stylesheet" href="../style/style.css">
</head>

<body>

    <?php include('../includes/header.php'); ?>

    <main>
        <h2>Manage Teachers</h2>

        <?php if ($message != "") echo "<p class='success'>$message</p>"; ?>
        <?php if ($error != "") echo "<p class='error'>$error</p>"; ?>

        <form method="POST" action="">
            <label>First Name:</label><br>
            <input type="text" name="first_name" required><br><br>

            <label>Last Name:</label><br>
            <input type="text" name="last_name" required><br><br>

            <label>Email:</label><br>
            <input type="email" name="email" required><br><br>

            <label>Phone:</label><br>
            <input type="text" name="phone"><br><br>

            <label>Qualification:</label><br>
            <input type="text" name="qualification"><br><br>

            <label>Hire Date:</label><br>
            <input type="date" name="hire_date" value="<?= date('Y-m-d'); ?>"><br><br>

            <button type="submit">Add Teacher</button>
        </form>

        <hr>

        <h3>All Teachers</h3>
        <table border="1" cellpadding="10" style="width:100%; background:white;">
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Qualification</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($teachers as $row) { ?>
                <tr>
                    <td><?= $row['teacher_id']; ?></td>
                    <td><?= $row['first_name'] . " " . $row['last_name']; ?></td>
                    <td><?= $row['email']; ?></td>
                    <td><?= $row['phone']; ?></td>
                    <td><?= $row['qualification']; ?></td>
                    <td>
                        <a href="edit_teacher.php?id=<?= $row['teacher_id']; ?>">Edit</a> |
                        <a href="delete_teacher.php?id=<?= $row['teacher_id']; ?>"
                            onclick="return confirm('Are you sure you want to delete this teacher?');">
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