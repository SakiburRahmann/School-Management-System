<?php
session_start();
include('config.php');

// If logged in, block signup (optional)
if (isset($_SESSION['username'])) {
    header("Location: dashboard.php");
    exit;
}

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $role = $_POST['role'];

    // Validate password length
    if (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } else {

        // Check existing user
        $check = $conn->prepare("SELECT * FROM users WHERE username = ?");
        $check->bind_param("s", $username);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows > 0) {
            $error = "Username already taken!";
        } else {
            // Hash password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Insert user
            $stmt = $conn->prepare(
                "INSERT INTO users (username, password, role) VALUES (?, ?, ?)"
            );
            $stmt->bind_param("sss", $username, $hashedPassword, $role);

            if ($stmt->execute()) {
                $success = "Account created successfully! You can login now.";
            } else {
                $error = "Failed to register user.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Signup - SchoolMS</title>
    <link rel="stylesheet" href="style/style.css">
</head>
<body>

<h2 style="text-align:center;">Create Admin / Teacher / Student</h2>

<div style="width:300px; margin:auto;">

    <?php 
        if ($error != "") echo "<p style='color:red;'>$error</p>";
        if ($success != "") echo "<p style='color:green;'>$success</p>";
    ?>

    <form method="POST">

        <label>Username:</label><br>
        <input type="text" name="username" required><br><br>

        <label>Password:</label><br>
        <input type="password" name="password" required><br><br>

        <label>Role:</label><br>
        <select name="role" required>
            <option value="Admin">Admin</option>
            <option value="Teacher">Teacher</option>
            <option value="Student">Student</option>
        </select>
        <br><br>

        <button type="submit">Create Account</button>

    </form>
</div>

</body>
</html>
