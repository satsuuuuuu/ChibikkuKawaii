<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $itemId = intval($_POST['item_id']);
    $quantity = intval($_POST['quantity']);

    if ($quantity < 1) {
        echo json_encode(['success' => false, 'message' => 'Invalid quantity']);
        exit();
    }

    $userId = $_SESSION['user_id'];

    // Update the cart item quantity in the database
    $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND item_id = ?");
    $stmt->bind_param('iii', $quantity, $userId, $itemId);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update cart']);
    }

    $stmt->close();
    $conn->close();
}
?>