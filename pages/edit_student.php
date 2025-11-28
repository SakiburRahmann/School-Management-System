<?php
session_start();
include('../config.php');

// Only admin access
if ($_SESSION['role'] !== 'Admin') {
    die("Access Denied!");
}

// GET student ID
if (!isset($_GET['id'])) {
    die("Invalid Student ID");
}

$student_id = $_GET['id'];

// Fetch student info
$sql = "SELECT * FROM students WHERE student_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Student not found!");
}

$student = $result->fetch_assoc();

// Fetch classes
$classes = $conn->query("SELECT * FROM classes");

// Fetch sections
$sections = $conn->query("SELECT * FROM sections");

$message = "";

// Update student info
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = $_POST['name'];
    $class_id = $_POST['class_id'];
    $section_id = $_POST['section_id'];
    $roll = $_POST['roll_number'];
    $gname = $_POST['guardian_name'];
    $gphone = $_POST['guardian_phone'];
    $contact = $_POST['contact_details'];

    $update = "UPDATE students 
               SET name=?, class_id=?, section_id=?, roll_number=?, 
                   guardian_name=?, guardian_phone=?, contact_details=?
               WHERE student_id=?";

    $stmt = $conn->prepare($update);
    $stmt->bind_param(
        "siiisssi",
        $name, $class_id, $section_id, $roll, $gname, $gphone, $contact, $student_id
    );

    if ($stmt->execute()) {
        $message = "Student updated successfully!";
    } else {
        $message = "Error updating student: " . $conn->error;
    }
}
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

    <?php if ($message != "") echo "<p style='color:green;'>$message</p>"; ?>

    <form method="POST">

        <label>Name:</label><br>
        <input type="text" name="name" value="<?= $student['name']; ?>" required><br><br>

        <label>Class:</label><br>
        <select name="class_id" required>
            <?php while ($c = $classes->fetch_assoc()) { ?>
                <option value="<?= $c['class_id']; ?>"
                    <?= $c['class_id'] == $student['class_id'] ? 'selected' : '' ?>>
                    <?= $c['class_name']; ?>
                </option>
            <?php } ?>
        </select><br><br>

        <label>Section:</label><br>
        <select name="section_id" required>
            <?php while ($s = $sections->fetch_assoc()) { ?>
                <option value="<?= $s['section_id']; ?>"
                    <?= $s['section_id'] == $student['section_id'] ? 'selected' : '' ?>>
                    <?= $s['section_name']; ?>
                </option>
            <?php } ?>
        </select><br><br>

        <label>Roll Number:</label><br>
        <input type="number" name="roll_number" value="<?= $student['roll_number']; ?>" required><br><br>

        <label>Guardian Name:</label><br>
        <input type="text" name="guardian_name" value="<?= $student['guardian_name']; ?>"><br><br>

        <label>Guardian Phone:</label><br>
        <input type="text" name="guardian_phone" value="<?= $student['guardian_phone']; ?>"><br><br>

        <label>Contact Details:</label><br>
        <textarea name="contact_details"><?= $student['contact_details']; ?></textarea><br><br>

        <button type="submit">Update</button>
    </form>

</main>

<?php include('../includes/footer.php'); ?>

</body>
</html>
