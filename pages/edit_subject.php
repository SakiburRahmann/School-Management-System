<?php
session_start();
require_once __DIR__ . '/../core/models/Subject.php';
require_once __DIR__ . '/../core/models/ClassModel.php';
require_once __DIR__ . '/../core/models/Teacher.php';

// Only admin access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit;
}

if (!isset($_GET['id'])) die("Invalid request");

$subject_id = $_GET['id'];
$subjectModel = new Subject();
$classModel = new ClassModel();
$teacherModel = new Teacher();

$subject = $subjectModel->find($subject_id);
if (!$subject) die("Subject not found");

$message = "";
$error = "";

// Update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $subject_name = trim($_POST['subject_name']);
    $subject_code = trim($_POST['subject_code']);
    $class_id = $_POST['class_id'];
    $teacher_id = $_POST['teacher_id'];

    $data = [
        'subject_name' => $subject_name,
        'subject_code' => $subject_code,
        'class_id' => $class_id,
        'teacher_id' => $teacher_id
    ];

    if ($subjectModel->update($subject_id, $data)) {
        $message = "Subject updated successfully!";
        $subject = $subjectModel->find($subject_id);
    } else {
        $error = "Error updating subject.";
    }
}

$classes = $classModel->findAll();
$teachers = $teacherModel->findAll();
?>

<!DOCTYPE html>
<html>

<head>
    <title>Edit Subject</title>
    <link rel="stylesheet" href="../style/style.css">
</head>

<body>

    <?php include('../includes/header.php'); ?>

    <main>
        <h2>Edit Subject</h2>

        <?php if ($message != "") echo "<p class='success'>$message</p>"; ?>
        <?php if ($error != "") echo "<p class='error'>$error</p>"; ?>

        <form method="POST">
            <label>Subject Name:</label><br>
            <input type="text" name="subject_name" value="<?= $subject['subject_name']; ?>" required>
            <br><br>

            <label>Subject Code:</label><br>
            <input type="text" name="subject_code" value="<?= $subject['subject_code']; ?>" required>
            <br><br>

            <label>Class:</label><br>
            <select name="class_id" required>
                <?php foreach ($classes as $c) { ?>
                    <option value="<?= $c['class_id']; ?>" <?= $c['class_id'] == $subject['class_id'] ? 'selected' : ''; ?>>
                        <?= $c['class_name'] . " (" . $c['section'] . ")"; ?>
                    </option>
                <?php } ?>
            </select><br><br>

            <label>Teacher:</label><br>
            <select name="teacher_id">
                <option value="">Select Teacher</option>
                <?php foreach ($teachers as $t) { ?>
                    <option value="<?= $t['teacher_id']; ?>" <?= $t['teacher_id'] == $subject['teacher_id'] ? 'selected' : ''; ?>>
                        <?= $t['first_name'] . " " . $t['last_name']; ?>
                    </option>
                <?php } ?>
            </select><br><br>

            <button type="submit">Update Subject</button>
        </form>

    </main>

    <?php include('../includes/footer.php'); ?>

</body>

</html>