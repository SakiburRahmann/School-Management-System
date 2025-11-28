<?php
session_start();
include('../config.php');

// Only admin can access
if ($_SESSION['role'] !== 'Admin') {
    die("Access Denied!");
}

// Fetch classes
$classes = $conn->query("SELECT * FROM classes");

// Fetch sections
$sections = $conn->query("SELECT sections.*, classes.class_name 
                          FROM sections 
                          JOIN classes ON sections.class_id = classes.class_id");

// Add New Student
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $class_id = $_POST['class_id'];
    $section_id = $_POST['section_id'];
    $roll_number = $_POST['roll_number'];
    $guardian_name = $_POST['guardian_name'];
    $guardian_phone = $_POST['guardian_phone'];
    $contact = $_POST['contact_details'];

    $sql = "INSERT INTO students 
            (name, class_id, section_id, roll_number, guardian_name, guardian_phone, contact_details)
            VALUES (?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("siiisss", $name, $class_id, $section_id, $roll_number, $guardian_name, $guardian_phone, $contact);

    if ($stmt->execute()) {
        $message = "Student added successfully!";
    } else {
        $message = "Error: " . $conn->error;
    }
}

// Fetch all students
$students = $conn->query("SELECT students.*, classes.class_name, sections.section_name
    FROM students
    LEFT JOIN classes ON students.class_id = classes.class_id
    LEFT JOIN sections ON students.section_id = sections.section_id
    ORDER BY students.student_id DESC");
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

        <?php if ($message != "")
            echo "<p style='color:green;'>$message</p>"; ?>

        <!-- ADD STUDENT FORM -->
        <h3>Add New Student</h3>

        <form method="POST" action="">
            <label>Name:</label><br>
            <input type="text" name="name" required><br><br>

            <label>Class:</label><br>
            <select name="class_id" required>
                <option value="">Select Class</option>
                <?php while ($c = $classes->fetch_assoc()) { ?>
                    <option value="<?= $c['class_id']; ?>"><?= $c['class_name']; ?></option>
                <?php } ?>
            </select><br><br>

            <label>Section:</label><br>
            <select name="section_id" required>
                <option value="">Select Section</option>
                <?php while ($s = $sections->fetch_assoc()) { ?>
                    <option value="<?= $s['section_id']; ?>">
                        <?= $s['class_name'] . " - " . $s['section_name']; ?>
                    </option>
                <?php } ?>
            </select><br><br>

            <label>Roll Number:</label><br>
            <input type="number" name="roll_number" required><br><br>

            <label>Guardian Name:</label><br>
            <input type="text" name="guardian_name"><br><br>

            <label>Guardian Phone:</label><br>
            <input type="text" name="guardian_phone"><br><br>

            <label>Contact Details:</label><br>
            <textarea name="contact_details"></textarea><br><br>

            <button type="submit">Add Student</button>
        </form>

        <hr>

        <!-- STUDENT LIST -->
        <h3>All Students</h3>

        <table border="1" cellpadding="10" style="width:100%; background:white;">
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Class</th>
                <th>Section</th>
                <th>Roll</th>
                <th>Guardian</th>
                <th>Phone</th>
                <th>Actions</th>
            </tr>

            <?php while ($row = $students->fetch_assoc()) { ?>
                <tr>
                    <td><?= $row['student_id']; ?></td>
                    <td><?= $row['name']; ?></td>
                    <td><?= $row['class_name']; ?></td>
                    <td><?= $row['section_name']; ?></td>
                    <td><?= $row['roll_number']; ?></td>
                    <td><?= $row['guardian_name']; ?></td>
                    <td><?= $row['guardian_phone']; ?></td>

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