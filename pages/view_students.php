<?php
session_start();
include('../config.php');

// Redirect if not logged in
if (!isset($_SESSION['username'])) {
    header("Location: ../login.php");
    exit;
}

// Fetch all students
$students = $conn->query("SELECT * FROM students");
?>

<!DOCTYPE html>
<html>

<head>
    <title>View Students</title>
    <link rel="stylesheet" href="../style/style.css">
</head>

<body>

    <?php include('../includes/header.php'); ?>

    <main>
        <h2>All Students</h2>
        <table border="1" cellpadding="10" style="width:100%; background:white;">
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Actions</th>
            </tr>
            <?php while ($row = $students->fetch_assoc()) { ?>
                <tr>
                    <td><?= $row['student_id']; ?></td>
                    <td><?= $row['name']; ?></td>
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