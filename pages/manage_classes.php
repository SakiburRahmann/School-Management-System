<?php
session_start();
include('../config.php');

// Only admin access
if ($_SESSION['role'] !== 'Admin') {
    die("Access Denied!");
}

$message = "";

// Add Class
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_class'])) {
    $class_name = trim($_POST['class_name']);
    if ($class_name != "") {
        $stmt = $conn->prepare("INSERT INTO classes (class_name) VALUES (?)");
        $stmt->bind_param("s", $class_name);
        if ($stmt->execute()) {
            $message = "Class added successfully!";
        } else {
            $message = "Error: " . $conn->error;
        }
    }
}

// Fetch all classes
$classes = $conn->query("SELECT * FROM classes ORDER BY class_id DESC");
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

    <?php if ($message != "") echo "<p style='color:green;'>$message</p>"; ?>

    <!-- Add Class Form -->
    <form method="POST">
        <label>Class Name:</label><br>
        <input type="text" name="class_name" required>
        <button type="submit" name="add_class">Add Class</button>
    </form>

    <hr>

    <!-- Class List -->
    <h3>All Classes</h3>
    <table border="1" cellpadding="10" style="width:100%; background:white;">
        <tr>
            <th>ID</th>
            <th>Class Name</th>
            <th>Actions</th>
        </tr>
        <?php while($row = $classes->fetch_assoc()) { ?>
            <tr>
                <td><?= $row['class_id']; ?></td>
                <td><?= $row['class_name']; ?></td>
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
