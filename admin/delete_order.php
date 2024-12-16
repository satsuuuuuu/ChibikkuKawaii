<?php
// admin/delete_order.php

header('Content-Type: application/json');

include 'db_connect.php'; // Ensure the path is correct relative to this file

$response = ['success' => false, 'message' => ''];

if (isset($_GET['id']) && is_numeric($_GET['id'])) { // Preferably use POST method
    $order_id = intval($_GET['id']);

    // Optional: Prevent deletion of certain orders based on status or role
    // Example:
    // if ($order_id == 1) { // Don't delete admin order
    //     $response['message'] = 'Cannot delete this order.';
    // }

    // Prepare the SQL statement
    $stmt = $conn->prepare("DELETE FROM orders WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        if ($stmt->affected_rows > 0) {
            $response['success'] = true;
            $response['message'] = 'Order deleted successfully.';
        } else {
            $response['message'] = 'No order found with the provided ID.';
        }
        $stmt->close();
    } else {
        $response['message'] = 'Failed to prepare the delete statement.';
    }
} else {
    $response['message'] = 'Invalid order ID.';
}

echo json_encode($response);

$conn->close();
?>