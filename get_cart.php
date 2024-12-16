<?php
header('Content-Type: application/json');
session_start();

try {
    require_once 'db_connect.php';

    if (!isset($_SESSION['user_id'])) {
        throw new Exception('User not logged in');
    }

    $user_id = $_SESSION['user_id'];
    
    // Updated query to match your table structure
    $query = "SELECT c.id, c.quantity, p.name, p.image_path, 
              p.original_price, p.discounted_price, p.stock_status 
              FROM cart c 
              JOIN products p ON c.product_id = p.id 
              WHERE c.user_id = ?";
              
    $stmt = $conn->prepare($query);
    
    if ($stmt === false) {
        throw new Exception('Database error: ' . $conn->error);
    }

    if (!$stmt->bind_param("i", $user_id)) {
        throw new Exception('Binding parameters failed: ' . $stmt->error);
    }

    if (!$stmt->execute()) {
        throw new Exception('Execute failed: ' . $stmt->error);
    }

    $result = $stmt->get_result();
    $cart_items = [];

    while ($row = $result->fetch_assoc()) {
        $cart_items[] = [
            'id' => $row['id'],
            'name' => $row['name'],
            'quantity' => $row['quantity'],
            'image_path' => $row['image_path'],
            'original_price' => $row['original_price'],
            'discounted_price' => $row['discounted_price'],
            'stock_status' => $row['stock_status']
        ];
    }

    $stmt->close();
    $conn->close();

    echo json_encode([
        'success' => true,
        'cart_items' => $cart_items
    ]);

} catch (Exception $e) {
    http_response_code(200);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>