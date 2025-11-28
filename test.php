<?php
include('config.php');  // connect to database

$sql = "SELECT * FROM students";
$result = $conn->query($sql);

if ($result) {
    echo "Database connection working! Number of students: " . $result->num_rows;
} else {
    echo "Error: " . $conn->error;
}
?>
