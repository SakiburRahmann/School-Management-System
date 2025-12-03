<?php
session_start();
include('../config.php');

if ($_SESSION['role'] !== 'Admin')
    die("Access Denied!");
if (!isset($_GET['id']))
    die("Invalid request");

$subject_id = $_GET['id'];

$stmt = $conn->prepare("DELETE FROM subjects WHERE subject_id=?");
$stmt->bind_param("i", $subject_id);
$stmt->execute();

header("Location: manage_subjects.php");
exit;
?>