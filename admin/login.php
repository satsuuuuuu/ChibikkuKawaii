<?php
// admin/login.php
session_start();
include '../db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = htmlspecialchars(trim($_POST['username']));
    $password = htmlspecialchars(trim($_POST['password']));

    // Prepare the SQL statement
    $stmt = $conn->prepare("SELECT id, password FROM admin_users WHERE username = ?");
    
    if ($stmt === false) {
        die("Prepare failed: " . htmlspecialchars($conn->error));
    }

    // Bind parameters
    $stmt->bind_param("s", $username);
    
    // Execute the statement
    $stmt->execute();
    
    // Bind result variables
    $stmt->bind_result($id, $hashed_password);
    
    if ($stmt->fetch()) {
        if (password_verify($password, $hashed_password)) {
            // Password is correct, start a new session
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $id;
            $_SESSION['admin_username'] = $username;
            header("Location: dashboard.php");
            exit();
        } else {
            echo "Invalid username or password.";
        }
    } else {
        echo "Invalid username or password.";
    }

    // Close statement and connection
    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login - Chibikku Kawaii</title>
    <link rel="stylesheet" href="css/style.css"> <!-- Link to your admin CSS -->
</head>
<body>
    <div class="container">
        <h2>Admin Login</h2>
        <form action="login.php" method="POST">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>
    
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
    
            <button type="submit">Login</button>
        </form>
        <?php
        if (!empty($error)) {
            echo "<p class='error'>{$error}</p>";
        }
        ?>
    </div>
</body>
</html>