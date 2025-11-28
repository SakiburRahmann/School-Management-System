<?php
session_start();
include('../config.php');

if ($_SESSION['role'] !== 'Admin') die("Access Denied!");
if (!isset($_GET['id'])) die("Invalid request");

$class_id = $_GET['id'];

$stmt = $conn->prepare("DELETE FROM classes WHERE class_id=?");
$stmt->bind_param("i", $class_id);
$stmt->execute();

header("Location: manage_classes.php");
exit;
?>
