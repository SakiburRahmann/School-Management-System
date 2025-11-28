<?php
session_start();
include('../config.php');

// Only admin access
if ($_SESSION['role'] !== 'Admin') {
    die("Access Denied!");
}

if (!isset($_GET['id'])) {
    die("Invalid request!");
}

$student_id = $_GET['id'];

$sql = "DELETE FROM students WHERE student_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();

header("Location: manage_students.php");
exit;
?>
