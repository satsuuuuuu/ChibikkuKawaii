<?php
// admin_store/add_admin.php

include '../db_connect.php';

// Define admin credentials
$admin_username = 'admin';
$admin_password_hashed = '$2y$10$dJo4LO0HV8naoa22SGD8AOmuwUJX4NT1sib6VhSPBrSJS2/Ziri9S'; // Replace with your hashed password

// Prepare and bind
$stmt = $conn->prepare("INSERT INTO admin_users (username, password) VALUES (?, ?)");
if ($stmt === false) {
    die("Prepare failed: " . htmlspecialchars($conn->error));
}

$stmt->bind_param("ss", $admin_username, $admin_password_hashed);

// Execute the statement
if ($stmt->execute()) {
    echo "Admin user added successfully.";
} else {
    echo "Error: " . $stmt->error;
}

// Close connections
$stmt->close();
$conn->close();
?>