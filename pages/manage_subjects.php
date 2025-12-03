<?php
session_start();
require_once __DIR__ . '/../core/models/Subject.php';
require_once __DIR__ . '/../core/models/ClassModel.php';
require_once __DIR__ . '/../core/models/Teacher.php';

// Only admin can access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit;
}

$subjectModel = new Subject();
$classModel = new ClassModel();
$teacherModel = new Teacher();

$message = "";
$error = "";

// Add New Subject
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $subject_name = $_POST['subject_name'];
    $subject_code = $_POST['subject_code'];
    $class_id = $_POST['class_id'];
    $teacher_id = $_POST['teacher_id'];

    $data = [
        'subject_name' => $subject_name,
        'subject_code' => $subject_code,
        'class_id' => $class_id,
        'teacher_id' => $teacher_id
    ];

    if ($subjectModel->insert($data)) {
        $message = "Subject added successfully!";
    } else {
        $error = "Error adding subject.";
    }
}

// Fetch all subjects with class and teacher info
$subjects = $subjectModel->query("
    SELECT subjects.*, classes.class_name, classes.section, teachers.first_name, teachers.last_name 
    FROM subjects 
    LEFT JOIN classes ON subjects.class_id = classes.class_id 
    LEFT JOIN teachers ON subjects.teacher_id = teachers.teacher_id 
    ORDER BY subjects.subject_id DESC
");

$classes = $classModel->findAll();
$teachers = $teacherModel->findAll();
?>

<!DOCTYPE html>
<html>

<head>
    <title>Manage Subjects</title>
    <link rel="stylesheet" href="../style/style.css">
</head>

<body>

    <?php include('../includes/header.php'); ?>

    <main>
        <h2>Manage Subjects</h2>

        <?php if ($message != "") echo "<p class='success'>$message</p>"; ?>
        <?php if ($error != "") echo "<p class='error'>$error</p>"; ?>

        <form method="POST" action="">
            <label>Subject Name:</label><br>
            <input type="text" name="subject_name" required><br><br>

            <label>Subject Code:</label><br>
            <input type="text" name="subject_code" required><br><br>

            <label>Class:</label><br>
            <select name="class_id" required>
                <option value="">Select Class</option>
                <?php foreach ($classes as $c) { ?>
                    <option value="<?= $c['class_id']; ?>">
                        <?= $c['class_name'] . " (" . $c['section'] . ")"; ?>
                    </option>
                <?php } ?>
            </select><br><br>

            <label>Teacher:</label><br>
            <select name="teacher_id">
                <option value="">Select Teacher</option>
                <?php foreach ($teachers as $t) { ?>
                    <option value="<?= $t['teacher_id']; ?>">
                        <?= $t['first_name'] . " " . $t['last_name']; ?>
                    </option>
                <?php } ?>
            </select><br><br>

            <button type="submit">Add Subject</button>
        </form>

        <hr>

        <h3>All Subjects</h3>
        <table border="1" cellpadding="10" style="width:100%; background:white;">
            <tr>
                <th>ID</th>
                <th>Subject Name</th>
                <th>Code</th>
                <th>Class</th>
                <th>Teacher</th>
                <th>Actions</th>
            </tr>
            <?php while ($row = $subjects->fetch_assoc()) { ?>
                <tr>
                    <td><?= $row['subject_id']; ?></td>
                    <td><?= $row['subject_name']; ?></td>
                    <td><?= $row['subject_code']; ?></td>
                    <td><?= $row['class_name'] . " (" . $row['section'] . ")"; ?></td>
                    <td><?= $row['first_name'] . " " . $row['last_name']; ?></td>
                    <td>
                        <a href="edit_subject.php?id=<?= $row['subject_id']; ?>">Edit</a> |
                        <a href="delete_subject.php?id=<?= $row['subject_id']; ?>"
                            onclick="return confirm('Are you sure you want to delete this subject?');">
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