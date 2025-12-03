<?php
session_start();
include('../config.php');

// Redirect if not logged in or not a teacher
if (
    !isset($_SESSION['username']) ||
    !isset($_SESSION['role']) ||
    $_SESSION['role'] !== 'Teacher'
) {
    header("Location: ../login.php");
    exit;
}

$message = "";

// Handle attendance submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_id = $_POST['student_id'];
    $status = $_POST['status']; // Present or Absent
    $date = date('Y-m-d');

    $sql = "INSERT INTO attendance (student_id, status, date) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iss", $student_id, $status, $date);

    if ($stmt->execute()) {
        $message = "Attendance recorded successfully!";
    } else {
        $message = "Error: " . $conn->error;
    }
}

// Fetch students for attendance
$students = $conn->query("SELECT * FROM students");
?>

<!DOCTYPE html>
<html>

<head>
    <title>Take Attendance</title>
    <link rel="stylesheet" href="../style/style.css">
</head>

<body>

    <?php include('../includes/header.php'); ?>

    <main>
        <h2>Take Attendance</h2>

        <?php if ($message != "")
            echo "<p style='color:green;'>$message</p>"; ?>

        <form method="POST" action="">
            <label>Select Student:</label><br>
            <select name="student_id" required>
                <?php while ($row = $students->fetch_assoc()) { ?>
                    <option value="<?= $row['student_id']; ?>"><?= $row['name']; ?></option>
                <?php } ?>
            </select><br><br>

            <label>Status:</label><br>
            <select name="status" required>
                <option value="Present">Present</option>
                <option value="Absent">Absent</option>
            </select><br><br>

            <button type="submit">Submit Attendance</button>
        </form>
    </main>

    <?php include('../includes/footer.php'); ?>

</body>

</html>