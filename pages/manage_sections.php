<?php
session_start();
include('../config.php');

if ($_SESSION['role'] !== 'Admin') die("Access Denied!");

$message = "";

// Fetch classes
$classes = $conn->query("SELECT * FROM classes");

// Fetch teachers
$teachers = $conn->query("SELECT * FROM teachers");

// Add Section
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_section'])) {
    $class_id = $_POST['class_id'];
    $section_name = trim($_POST['section_name']);
    $class_teacher_id = $_POST['class_teacher_id'];

    $stmt = $conn->prepare("INSERT INTO sections (class_id, section_name, class_teacher_id) VALUES (?, ?, ?)");
    $stmt->bind_param("isi", $class_id, $section_name, $class_teacher_id);
    if ($stmt->execute()) {
        $message = "Section added successfully!";
    } else {
        $message = "Error: " . $conn->error;
    }
}

// Fetch all sections
$sections = $conn->query("SELECT sections.*, classes.class_name, teachers.name AS teacher_name 
                          FROM sections 
                          LEFT JOIN classes ON sections.class_id = classes.class_id
                          LEFT JOIN teachers ON sections.class_teacher_id = teachers.teacher_id
                          ORDER BY section_id DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Sections</title>
    <link rel="stylesheet" href="../style/style.css">
</head>
<body>

<?php include('../includes/header.php'); ?>

<main>
    <h2>Manage Sections</h2>

    <?php if ($message != "") echo "<p style='color:green;'>$message</p>"; ?>

    <!-- Add Section Form -->
    <form method="POST">
        <label>Class:</label><br>
        <select name="class_id" required>
            <option value="">Select Class</option>
            <?php while($c=$classes->fetch_assoc()){ ?>
                <option value="<?= $c['class_id']; ?>"><?= $c['class_name']; ?></option>
            <?php } ?>
        </select><br><br>

        <label>Section Name:</label><br>
        <input type="text" name="section_name" required><br><br>

        <label>Class Teacher:</label><br>
        <select name="class_teacher_id">
            <option value="">None</option>
            <?php while($t=$teachers->fetch_assoc()){ ?>
                <option value="<?= $t['teacher_id']; ?>"><?= $t['name']; ?></option>
            <?php } ?>
        </select><br><br>

        <button type="submit" name="add_section">Add Section</button>
    </form>

    <hr>

    <!-- Section List -->
    <h3>All Sections</h3>
    <table border="1" cellpadding="10" style="width:100%; background:white;">
        <tr>
            <th>ID</th>
            <th>Class</th>
            <th>Section Name</th>
            <th>Class Teacher</th>
            <th>Actions</th>
        </tr>
        <?php while($row=$sections->fetch_assoc()){ ?>
            <tr>
                <td><?= $row['section_id']; ?></td>
                <td><?= $row['class_name']; ?></td>
                <td><?= $row['section_name']; ?></td>
                <td><?= $row['teacher_name']; ?></td>
                <td>
                    <a href="edit_section.php?id=<?= $row['section_id']; ?>">Edit</a> |
                    <a href="delete_section.php?id=<?= $row['section_id']; ?>" 
                       onclick="return confirm('Are you sure you want to delete this section?');">
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
