<?php
session_start();
include 'db_connect.php';

// Retrieve and sanitize input
$username = htmlspecialchars(trim($_POST['username']));
$password = htmlspecialchars(trim($_POST['password']));

// Check if the user is an admin
$stmt = $conn->prepare("SELECT id, password FROM admin_users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->bind_result($admin_id, $hashed_password);
$stmt->fetch();

if ($admin_id && password_verify($password, $hashed_password)) {
    // Admin login successful
    $_SESSION['admin_logged_in'] = true;
    $_SESSION['admin_id'] = $admin_id;
    $_SESSION['admin_username'] = $username;
    echo json_encode(['success' => true, 'isAdmin' => true]);
    exit();
}

// If not admin, check for regular user
$stmt->close();
$stmt = $conn->prepare("SELECT id, password FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->bind_result($user_id, $hashed_password);
$stmt->fetch();

if ($user_id && password_verify($password, $hashed_password)) {
    // User login successful
    $_SESSION['user_logged_in'] = true;
    $_SESSION['user_id'] = $user_id;
    $_SESSION['username'] = $username;
    echo json_encode(['success' => true, 'isAdmin' => false]);
    exit();
}

// If login fails
echo json_encode(['success' => false, 'message' => 'Invalid username or password.']);
$stmt->close();
$conn->close();
?>