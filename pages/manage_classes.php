<?php
session_start();
require_once __DIR__ . '/../core/models/ClassModel.php';

// Only admin access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit;
}

$classModel = new ClassModel();
$message = "";
$error = "";

// Add Class
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_class'])) {
    $class_name = trim($_POST['class_name']);
    $section = trim($_POST['section']);

    if ($class_name != "" && $section != "") {
        if ($classModel->insert(['class_name' => $class_name, 'section' => $section])) {
            $message = "Class added successfully!";
        } else {
            $error = "Error adding class.";
        }
    } else {
        $error = "Please fill in all fields.";
    }
}

// Fetch all classes
$classes = $classModel->findAll();
?>

<!DOCTYPE html>
<html>

<head>
    <title>Manage Classes</title>
    <link rel="stylesheet" href="../style/style.css">
</head>

<body>

    <?php include('../includes/header.php'); ?>

    <main>
        <h2>Manage Classes</h2>

        <?php if ($message != "") echo "<p class='success'>$message</p>"; ?>
        <?php if ($error != "") echo "<p class='error'>$error</p>"; ?>

        <!-- Add Class Form -->
        <form method="POST">
            <label>Class Name:</label><br>
            <input type="text" name="class_name" required placeholder="e.g. Class 10"><br><br>
            
            <label>Section:</label><br>
            <input type="text" name="section" required placeholder="e.g. A"><br><br>

            <button type="submit" name="add_class">Add Class</button>
        </form>

        <hr>

        <!-- Class List -->
        <h3>All Classes</h3>
        <table border="1" cellpadding="10" style="width:100%; background:white;">
            <tr>
                <th>ID</th>
                <th>Class Name</th>
                <th>Section</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($classes as $row) { ?>
                <tr>
                    <td><?= $row['class_id']; ?></td>
                    <td><?= $row['class_name']; ?></td>
                    <td><?= $row['section']; ?></td>
                    <td>
                        <a href="edit_class.php?id=<?= $row['class_id']; ?>">Edit</a> |
                        <a href="delete_class.php?id=<?= $row['class_id']; ?>"
                            onclick="return confirm('Are you sure you want to delete this class?');">
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