<?php
session_start();
require_once __DIR__ . '/../core/models/ClassModel.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit;
}

if (!isset($_GET['id'])) die("Invalid request");

$class_id = $_GET['id'];
$classModel = new ClassModel();

$class = $classModel->find($class_id);
if (!$class) die("Class not found");

$message = "";
$error = "";

// Update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $class_name = trim($_POST['class_name']);
    $section = trim($_POST['section']);

    if ($class_name != "" && $section != "") {
        if ($classModel->update($class_id, ['class_name' => $class_name, 'section' => $section])) {
            $message = "Class updated successfully!";
            $class = $classModel->find($class_id);
        } else {
            $error = "Error updating class.";
        }
    } else {
        $error = "Please fill in all fields.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Class</title>
    <link rel="stylesheet" href="../style/style.css">
</head>
<body>

<?php include('../includes/header.php'); ?>

<main>
    <h2>Edit Class</h2>

    <?php if ($message != "") echo "<p class='success'>$message</p>"; ?>
    <?php if ($error != "") echo "<p class='error'>$error</p>"; ?>

    <form method="POST">
        <label>Class Name:</label><br>
        <input type="text" name="class_name" value="<?= $class['class_name']; ?>" required>
        <br><br>
        
        <label>Section:</label><br>
        <input type="text" name="section" value="<?= $class['section']; ?>" required>
        <br><br>

        <button type="submit">Update Class</button>
    </form>

</main>

<?php include('../includes/footer.php'); ?>

</body>
</html>
