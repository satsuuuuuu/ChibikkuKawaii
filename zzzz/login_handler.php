<?php
// login_handler.php
session_start();
include 'db_connect.php'; // Ensure correct path

// Set header to JSON
header('Content-Type: application/json');

// Retrieve POST data
$username = $_POST['username'];
$password = $_POST['password'];
$csrf_token = $_POST['csrf_token'];

// Validate CSRF token
if (!hash_equals($_SESSION['csrf_token'], $csrf_token)) {
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token.']);
    exit();
}

// Authenticate user (use prepared statements and password_hash)
$stmt = $conn->prepare("SELECT id, password FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows !== 1) {
    echo json_encode(['success' => false, 'message' => 'Invalid credentials.']);
    exit();
}
$stmt->bind_result($user_id, $hashed_password);
$stmt->fetch();
if (!password_verify($password, $hashed_password)) {
    echo json_encode(['success' => false, 'message' => 'Invalid credentials.']);
    exit();
}
$stmt->close();

// Successful login
$_SESSION['user_id'] = $user_id;
$_SESSION['username'] = $username;

// Regenerate session ID for security
session_regenerate_id(true);

// Merge session cart with user cart
function mergeCarts($conn, $user_id, $session_id) {
    // Fetch user's cart ID
    $stmt = $conn->prepare("SELECT id FROM cart WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($user_cart_id);
    if (!$stmt->fetch()) {
        // No cart exists, create one
        $stmt->close();
        $stmt = $conn->prepare("INSERT INTO cart (user_id) VALUES (?)");
        $stmt->bind_param("i", $user_id);
        if ($stmt->execute()) {
            $user_cart_id = $stmt->insert_id;
        } else {
            $stmt->close();
            return;
        }
    }
    $stmt->close();

    // Fetch session cart ID
    $stmt = $conn->prepare("SELECT id FROM cart WHERE session_id = ?");
    $stmt->bind_param("s", $session_id);
    $stmt->execute();
    $stmt->bind_result($session_cart_id);
    if ($stmt->fetch()) {
        $stmt->close();

        // Transfer cart items from session cart to user cart
        $stmt = $conn->prepare("INSERT INTO cart_items (cart_id, product_id, quantity) SELECT ?, product_id, quantity FROM cart_items WHERE cart_id = ?");
        $stmt->bind_param("ii", $user_cart_id, $session_cart_id);
        if ($stmt->execute()) {
            // Successfully merged
        }
        $stmt->close();

        // Delete the session cart
        $stmt = $conn->prepare("DELETE FROM cart WHERE id = ?");
        $stmt->bind_param("i", $session_cart_id);
        $stmt->execute();
        $stmt->close();
    } else {
        $stmt->close();
    }
}

// If there was a session-based cart, merge it with the user cart
if (isset($_SESSION['cart_session_id'])) {
    mergeCarts($conn, $user_id, $_SESSION['cart_session_id']);
    unset($_SESSION['cart_session_id']); // Clear session cart ID after merging
}

echo json_encode(['success' => true, 'message' => 'Login successful.']);
?>