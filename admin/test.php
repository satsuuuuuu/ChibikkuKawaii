<?php
include '../db_connect.php';

$product_id = 1; // Replace with a valid product ID

$stmt = $conn->prepare("SELECT name, description, price, category_id, stock_status FROM products WHERE id = ?");
if ($stmt === false) {
    die("Prepare failed: " . htmlspecialchars($conn->error));
}

$stmt->bind_param("i", $product_id);
$stmt->execute();
$stmt->bind_result($name, $description, $original_price,$discounted_price,$category_id, $stock_status);

if ($stmt->fetch()) {
    echo "Product Name: " . htmlspecialchars($name) . "<br>";
    echo "description: " . htmlspecialchars($description) . "<br>";
    echo "old Price: " . htmlspecialchars($original_price) . "<br>";
    echo "Category ID: " . htmlspecialchars($category_id) . "<br>";
    echo "discounted Price: " . htmlspecialchars($discounted_price) . "<br>";
} else {
    echo "No product found with ID: " . htmlspecialchars($product_id);
}

$stmt->close();
$conn->close();
?>