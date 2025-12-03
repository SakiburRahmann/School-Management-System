<?php
session_start();
include('../config.php');

// Redirect if not logged in
if (!isset($_SESSION['username'])) {
    header("Location: ../login.php");
    exit;
}

$error = "";
$fees = null;

// Fetch fee status for the logged-in student
$username = $_SESSION['username'];
$sql = "SELECT * FROM students WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();

if ($student) {
    $fees = $conn->query("SELECT * FROM fees WHERE student_id = " . (int) $student['student_id']);
} else {
    $error = "No student profile is linked to this account.";
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>My Fees - SchoolMS</title>
    <link rel="stylesheet" href="../style/style.css">
</head>

<body>

    <?php include('../includes/header.php'); ?>

    <main>
        <h2>My Fees</h2>

        <?php if ($error) { ?>
            <p style="color:red;"><?= $error; ?></p>
        <?php } elseif ($fees) { ?>
            <table border="1" cellpadding="10" style="width:100%; background:white;">
                <tr>
                    <th>Fee Type</th>
                    <th>Amount</th>
                    <th>Status</th>
                </tr>
                <?php while ($row = $fees->fetch_assoc()) { ?>
                    <tr>
                        <td><?= $row['fee_type']; ?></td>
                        <td><?= $row['amount']; ?></td>
                        <td><?= $row['status']; ?></td>
                    </tr>
                <?php } ?>
            </table>
        <?php } ?>
    </main>

    <?php include('../includes/footer.php'); ?>

</body>

</html>