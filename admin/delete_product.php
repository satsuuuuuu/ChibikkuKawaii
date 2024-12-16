<?php
// admin/delete_product.php

header('Content-Type: application/json');

include '../db_connect.php'; // Ensure the path is correct relative to this file

$response = ['success' => false, 'message' => ''];

// Check if the request method is GET
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['id']) && is_numeric($_GET['id'])) {
        $product_id = intval($_GET['id']);

        // Prepare the SQL statement
        $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $product_id);
            $stmt->execute();
            if ($stmt->affected_rows > 0) {
                $response['success'] = true;
                $response['message'] = 'Product deleted successfully.';
            } else {
                $response['message'] = 'No product found with the provided ID.';
            }
            $stmt->close();
        } else {
            $response['message'] = 'Failed to prepare the delete statement.';
        }
    } else {
        $response['message'] = 'Invalid product ID.';
    }
} else {
    $response['message'] = 'Invalid request method.';
}

echo json_encode($response);

$conn->close();
?>