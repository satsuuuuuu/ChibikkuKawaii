<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in to access the cart.']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch cart items
$query = "SELECT c.product_id, c.quantity, p.discounted_price, p.name, p.image_path, p.stock_status 
          FROM carts c 
          JOIN products p ON c.product_id = p.id 
          WHERE c.user_id = ?";
$stmt = $conn->prepare($query);

if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Error preparing query.']);
    exit;
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$cart_items = [];
while ($row = $result->fetch_assoc()) {
    // Debugging: Log the fetched data
    error_log("Fetched item: " . print_r($row, true));
    $cart_items[] = $row;
}

if (empty($cart_items)) {
    echo json_encode(['success' => false, 'message' => 'Your cart is empty.']);
} else {
    echo json_encode(['success' => true, 'cart_items' => $cart_items]);
}
?>