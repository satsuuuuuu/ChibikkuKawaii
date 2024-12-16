<?php
// remove_from_cart.php
session_start();
include 'db_connect.php'; // Ensure correct path

header('Content-Type: application/json');

// Get the JSON input from the AJAX request
$request = json_decode(file_get_contents('php://input'), true);

if (!isset($request['product_id'])) {
    echo json_encode(['success' => false, 'message' => 'Product ID is required.']);
    exit();
}

$product_id = intval($request['product_id']);

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // User is not logged in, prompt to log in
    echo json_encode(['success' => false, 'message' => 'You must be logged in to remove items from the cart.']);
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch cart ID
$stmt = $conn->prepare("SELECT id FROM carts WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($cart_id);
if (!$stmt->fetch()) {
    echo json_encode(['success' => false, 'message' => 'Cart not found.']);
    $stmt->close();
    $conn->close();
    exit();
}
$stmt->close();

// Remove the product from the cart
$stmt = $conn->prepare("DELETE FROM cart_items WHERE cart_id = ? AND product_id = ?");
$stmt->bind_param("ii", $cart_id, $product_id);
if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Product removed from cart.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to remove product from cart.']);
}
$stmt->close();
$conn->close();
?>