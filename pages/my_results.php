<?php
session_start();
include('../config.php');

// Redirect if not logged in
if (!isset($_SESSION['username'])) {
    header("Location: ../login.php");
    exit;
}

$error = "";
$results = null;

// Fetch results for the logged-in student
$username = $_SESSION['username'];
$sql = "SELECT * FROM students WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();

if ($student) {
    $results = $conn->query("SELECT * FROM results WHERE student_id = " . (int) $student['student_id']);
} else {
    $error = "No student profile is linked to this account.";
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>My Results - SchoolMS</title>
    <link rel="stylesheet" href="../style/style.css">
</head>

<body>

    <?php include('../includes/header.php'); ?>

    <main>
        <h2>My Results</h2>

        <?php if ($error) { ?>
            <p style="color:red;"><?= $error; ?></p>
        <?php } elseif ($results) { ?>
            <table border="1" cellpadding="10" style="width:100%; background:white;">
                <tr>
                    <th>Subject</th>
                    <th>Marks</th>
                </tr>
                <?php while ($row = $results->fetch_assoc()) { ?>
                    <tr>
                        <td><?= $row['subject']; ?></td>
                        <td><?= $row['marks']; ?></td>
                    </tr>
                <?php } ?>
            </table>
        <?php } ?>
    </main>

    <?php include('../includes/footer.php'); ?>

</body>

</html>