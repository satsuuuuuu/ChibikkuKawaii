<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in to checkout.']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Step 1: Retrieve cart items
$query = "SELECT c.product_id, c.quantity, p.discounted_price AS price 
          FROM carts c 
          JOIN products p ON c.product_id = p.id 
          WHERE c.user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Your cart is empty.']);
    exit;
}

$cart_items = [];
$total_amount = 0;

while ($row = $result->fetch_assoc()) {
    $cart_items[] = $row;
    $total_amount += $row['price'] * $row['quantity'];
}

// Step 2: Create a new order
$query = "INSERT INTO orders (user_id, total_amount) VALUES (?, ?)";
$stmt = $conn->prepare($query);
$stmt->bind_param("id", $user_id, $total_amount);
$stmt->execute();
$order_id = $stmt->insert_id;

// Step 3: Add items to the order_items table
$query = "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($query);

foreach ($cart_items as $item) {
    $stmt->bind_param("iiid", $order_id, $item['product_id'], $item['quantity'], $item['price']);
    $stmt->execute();
}

// Step 4: Clear the cart
$query = "DELETE FROM carts WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();

// Redirect to order confirmation page
header("Location: order_confirmation.php?order_id=" . $order_id);
exit;
?>