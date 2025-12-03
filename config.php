<?php
// Database configuration
$servername = "localhost";  // Usually 'localhost'
$username = "root";         // Your MySQL username
$password = "IAmTheMan!20040113!";             // Your MySQL password
$database = "schoolms_db";  // The database you created

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
// echo "Database connected successfully!";
?>