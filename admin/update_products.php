<?php
// update_product.php
include 'protect.php';
include '../db_connect.php';

// Enable error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize inputs
    $product_id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $name = isset($_POST['name']) ? htmlspecialchars(trim($_POST['name'])) : '';
    $description = isset($_POST['description']) ? htmlspecialchars(trim($_POST['description'])) : '';
    $original_price = isset($_POST['original_price']) ? floatval($_POST['original_price']) : 0.00;
    $discounted_price = isset($_POST['discounted_price']) ? floatval($_POST['discounted_price']) : 0.00;
    $image_path = isset($_POST['image_path']) ? htmlspecialchars(trim($_POST['image_path'])) : '';
    $category_id = isset($_POST['category_id']) ? intval($_POST['category_id']) : null;

    // Basic validation
    if ($product_id <= 0 || empty($name) || empty($description) || $original_price < 0 || $discounted_price < 0 || !$category_id || empty($image_path)) {
        die("Invalid input data.");
    }

    // Prepare the SQL statement
    $stmt = $conn->prepare("UPDATE products SET name = ?, description = ?, original_price = ?, discounted_price = ?, image_path = ?, category_id = ? WHERE id = ?");
    if ($stmt === false) {
        die("Prepare failed: " . htmlspecialchars($conn->error));
    }

    // Bind parameters
    $stmt->bind_param("ssddsii", $name, $description, $original_price, $discounted_price, $image_path, $category_id, $product_id);

    // Execute the statement
    if ($stmt->execute()) {
        header("Location: dashboard.php?message=Product+updated+successfully");
        exit();
    } else {
        die("Error updating product: " . $stmt->error);
    }

    // Close connections
    $stmt->close();
    $conn->close();
} else {
    die("Invalid request method.");
}
?>