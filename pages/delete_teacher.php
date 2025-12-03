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

$teacher_id = $_GET['id'];

$sql = "DELETE FROM teachers WHERE teacher_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $teacher_id);
$stmt->execute();

header("Location: manage_teachers.php");
exit;
?>