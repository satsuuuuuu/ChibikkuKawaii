<?php
// fetch_featured_products.php

include 'db_connect.php'; // Ensure the path is correct

// Fetch the ID of the "Featured" category
$featured_category_query = "SELECT id FROM categories WHERE name = 'Featured' LIMIT 1";
$featured_result = $conn->query($featured_category_query);

if ($featured_result && $featured_result->num_rows > 0) {
    $featured_category = $featured_result->fetch_assoc();
    $featured_category_id = $featured_category['id'];
} else {
    die("Featured category not found. Please ensure the 'Featured' category exists.");
}

// Fetch products categorized as "Featured"
$featured_products_query = "SELECT id, name, description, original_price, discounted_price, image_path FROM products WHERE category_id = ?";
$featured_stmt = $conn->prepare($featured_products_query);

if ($featured_stmt === false) {
    die("Prepare failed: " . htmlspecialchars($conn->error));
}

$featured_stmt->bind_param("i", $featured_category_id);

if (!$featured_stmt->execute()) {
    die("Execute failed: " . htmlspecialchars($featured_stmt->error));
}

$featured_result = $featured_stmt->get_result();
$featured_products = [];

while ($row = $featured_result->fetch_assoc()) {
    $featured_products[] = $row;
}

$featured_stmt->close();
$conn->close();
?>