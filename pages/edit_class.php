<?php
session_start();
include('../config.php');

if ($_SESSION['role'] !== 'Admin') {
    die("Access Denied!");
}

if (!isset($_GET['id'])) die("Invalid request");

$class_id = $_GET['id'];

// Fetch class
$stmt = $conn->prepare("SELECT * FROM classes WHERE class_id=?");
$stmt->bind_param("i", $class_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows == 0) die("Class not found");
$class = $result->fetch_assoc();

$message = "";

// Update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $class_name = trim($_POST['class_name']);
    if ($class_name != "") {
        $stmt = $conn->prepare("UPDATE classes SET class_name=? WHERE class_id=?");
        $stmt->bind_param("si", $class_name, $class_id);
        if ($stmt->execute()) {
            $message = "Class updated successfully!";
        } else {
            $message = "Error: " . $conn->error;
        }
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

    <?php if($message != "") echo "<p style='color:green;'>$message</p>"; ?>

    <form method="POST">
        <label>Class Name:</label><br>
        <input type="text" name="class_name" value="<?= $class['class_name']; ?>" required>
        <br><br>
        <button type="submit">Update Class</button>
    </form>

</main>

<?php include('../includes/footer.php'); ?>

</body>
</html>
