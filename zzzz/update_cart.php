<?php
session_start();
include 'db_connect.php';

header('Content-Type: application/json');

// Get the JSON input from the AJAX request
$request = json_decode(file_get_contents('php://input'), true);

// Validate input
if (!isset($request['product_id']) || !isset($request['quantity'])) {
    echo json_encode(['success' => false, 'message' => 'Product ID and quantity are required.']);
    exit();
}

$product_id = intval($request['product_id']);
$quantity = intval($request['quantity']);

if ($quantity < 1) {
    echo json_encode(['success' => false, 'message' => 'Quantity must be at least 1.']);
    exit();
}

// Check if the user is logged in
if (!isset($_SESSION['user_id']) && !isset($_SESSION['cart_session_id'])) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in to update the cart.']);
    exit();
}

$user_id = $_SESSION['user_id'] ?? null;

// Fetch cart ID function
function getCartId($conn, $user_id) {
    if ($user_id) {
        $stmt = $conn->prepare("SELECT id FROM carts WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
    } else {
        if (!isset($_SESSION['cart_session_id'])) {
            $_SESSION['cart_session_id'] = session_id();
        }
        $session_id = $_SESSION['cart_session_id'];
        $stmt = $conn->prepare("SELECT id FROM carts WHERE session_id = ?");
        $stmt->bind_param("s", $session_id);
    }

    $stmt->execute();
    $stmt->bind_result($cart_id);
    if ($stmt->fetch()) {
        $stmt->close();
        return $cart_id;
    }
    $stmt->close();

    // No cart exists, create one
    if ($user_id) {
        $stmt = $conn->prepare("INSERT INTO carts (user_id) VALUES (?)");
        $stmt->bind_param("i", $user_id);
    } else {
        $stmt = $conn->prepare("INSERT INTO carts (session_id) VALUES (?)");
        $stmt->bind_param("s", $session_id);
    }

    if ($stmt->execute()) {
        $cart_id = $stmt->insert_id;
        $stmt->close();
        return $cart_id;
    }

    $stmt->close();
    return null;
}

// Fetch cart ID
$cart_id = getCartId($conn, $user_id);

// Handle cases where cart ID couldn't be retrieved or created
if (!$cart_id) {
    error_log("Failed to retrieve or create cart for user ID: " . ($user_id ?? 'guest session'));
    echo json_encode(['success' => false, 'message' => 'Unable to retrieve or create cart.']);
    exit();
}

// Update the quantity of the product in the cart
$stmt = $conn->prepare("UPDATE cart_items SET quantity = ? WHERE cart_id = ? AND product_id = ?");
$stmt->bind_param("iii", $quantity, $cart_id, $product_id);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Cart updated successfully.']);
    } else {
        $checkStmt = $conn->prepare("SELECT id FROM cart_items WHERE cart_id = ? AND product_id = ?");
        $checkStmt->bind_param("ii", $cart_id, $product_id);
        $checkStmt->execute();
        $checkStmt->store_result();
        if ($checkStmt->num_rows > 0) {
            echo json_encode(['success' => false, 'message' => 'Failed to update cart.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Product not found in cart.']);
        }
        $checkStmt->close();
    }
} else {
    error_log("Database error: " . $stmt->error);
    echo json_encode(['success' => false, 'message' => 'Failed to update cart.']);
}

$stmt->close();
$conn->close();
?>
