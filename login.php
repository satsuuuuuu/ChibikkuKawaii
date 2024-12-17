<?php
session_start();
include 'db_connect.php'; // Include your database connection file

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get username and password from POST request
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Prepare and execute query to fetch user
    $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        // Verify password
        if (password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id']; // Store user ID in session
            $_SESSION['username'] = $user['username']; // Store username in session
            echo json_encode(["success" => true, "message" => "Login successful!"]);
        } else {
            echo json_encode(["success" => false, "message" => "Invalid password."]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "User not found."]);
    }

    $stmt->close();
    $conn->close();
}
?>