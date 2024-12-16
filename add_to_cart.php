<?php
header('Content-Type: application/json');
session_start();

try {
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Please log in to add items to cart');
    }

    if (!isset($_POST['product_id'])) {
        throw new Exception('Product ID is required');
    }

    require_once 'db_connect.php';

    $user_id = $_SESSION['user_id'];
    $product_id = $_POST['product_id'];
    
    // Check if product exists and is in stock
    $stmt = $conn->prepare("SELECT id, stock_status FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Product not found');
    }
    
    $product = $result->fetch_assoc();
    if ($product['stock_status'] !== 'in_stock') {
        throw new Exception('Product is out of stock');
    }

    // Check if product is already in cart
    $stmt = $conn->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt->bind_param("ii", $user_id, $product_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Update quantity if product already in cart
        $cart_item = $result->fetch_assoc();
        $new_quantity = $cart_item['quantity'] + 1;
        
        $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
        $stmt->bind_param("ii", $new_quantity, $cart_item['id']);
    } else {
        // Add new item to cart
        $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, 1)");
        $stmt->bind_param("ii", $user_id, $product_id);
    }

    if (!$stmt->execute()) {
        throw new Exception('Failed to update cart');
    }

    // Get updated cart count
    $stmt = $conn->prepare("SELECT SUM(quantity) as cart_count FROM cart WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $cart_count = $result->fetch_assoc()['cart_count'] ?? 0;

    echo json_encode([
        'success' => true,
        'message' => 'Product added to cart',
        'cart_count' => $cart_count
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>