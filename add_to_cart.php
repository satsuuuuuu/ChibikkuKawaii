<?php
session_start();
include 'db_connect.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in to add items to the cart.']);
    exit;
}

$user_id = $_SESSION['user_id'];
$product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
$quantity = 1; // Default quantity

if ($product_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid product ID.']);
    exit;
}

// Fetch the product's discounted price
$query = "SELECT discounted_price FROM products WHERE id = ?";
$stmt = $conn->prepare($query);
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Error preparing query.']);
    exit;
}
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Product not found.']);
    exit;
}

$product = $result->fetch_assoc();
$discounted_price = $product['discounted_price'];

// Check if the product is already in the cart
$query = "SELECT quantity FROM carts WHERE user_id = ? AND product_id = ?";
$stmt = $conn->prepare($query);
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Error preparing query.']);
    exit;
}
$stmt->bind_param("ii", $user_id, $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Update the quantity if the product is already in the cart
    $query = "UPDATE carts SET quantity = quantity + 1 WHERE user_id = ? AND product_id = ?";
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Error preparing query.']);
        exit;
    }
    $stmt->bind_param("ii", $user_id, $product_id);
} else {
    // Insert a new row if the product is not in the cart
    $query = "INSERT INTO carts (user_id, product_id, quantity, discounted_price) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Error preparing query.']);
        exit;
    }
    $stmt->bind_param("iiid", $user_id, $product_id, $quantity, $discounted_price);
}

// Execute the query
if ($stmt->execute()) {
    // Fetch the updated cart count
    $query = "SELECT COUNT(*) AS cart_count FROM carts WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Error preparing query.']);
        exit;
    }
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $cart_count = $result->fetch_assoc()['cart_count'];

    echo json_encode(['success' => true, 'message' => 'Item added to cart.', 'cart_count' => $cart_count]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error adding item to cart.']);
}
?>