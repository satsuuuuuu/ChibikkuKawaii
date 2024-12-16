<?php
// admin_store/add_default_categories.php

include '../db_connect.php';

// Define default categories
$default_categories = [
    ['name' => 'Featured', 'description' => 'Featured products'],
    ['name' => 'In Stock', 'description' => 'Products currently in stock'],
    ['name' => 'Pre Order', 'description' => 'Products available for pre-order']
];

// Prepare the SQL statement
$stmt = $conn->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");

foreach ($default_categories as $category) {
    $stmt->bind_param("ss", $category['name'], $category['description']);
    $stmt->execute();
}

echo "Default categories added successfully.";

// Close connections
$stmt->close();
$conn->close();
?>