<?php
// admin/delete_user.php

header('Content-Type: application/json');

include '../db_connect.php'; // Ensure the path is correct relative to this file

$response = ['success' => false, 'message' => ''];

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $user_id = intval($_GET['id']);

    // Prevent deletion of admin accounts or yourself (optional but recommended)
    // Example: if ($user_id == 1) { /* prevent deletion */ }

    // Prepare the SQL statement
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        if ($stmt->affected_rows > 0) {
            $response['success'] = true;
            $response['message'] = 'User deleted successfully.';
        } else {
            $response['message'] = 'No user found with the provided ID.';
        }
        $stmt->close();
    } else {
        $response['message'] = 'Failed to prepare the delete statement.';
    }
} else {
    $response['message'] = 'Invalid user ID.';
}

echo json_encode($response);

$conn->close();
?>