<?php
// admin/update_product.php

include 'protect.php'; // Session protection
include '../db_connect.php'; // Database connection

// Enable error reporting for debugging (disable in production)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Check if the form is submitted via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize inputs
    $product_id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $name = isset($_POST['name']) ? htmlspecialchars(trim($_POST['name'])) : '';
    $description = isset($_POST['description']) ? htmlspecialchars(trim($_POST['description'])) : '';
    $original_price = isset($_POST['original_price']) ? floatval($_POST['original_price']) : 0;
    $discounted_price = isset($_POST['discounted_price']) ? floatval($_POST['discounted_price']) : 0;
    $category_id = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;

    // Handle image upload if a new image is provided
    $image_path = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $image_tmp_path = $_FILES['image']['tmp_name'];
        $image_name = basename($_FILES['image']['name']);
        $image_extension = strtolower(pathinfo($image_name, PATHINFO_EXTENSION));

        // Define allowed image extensions
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($image_extension, $allowed_extensions)) {
            // Define the upload directory
            $upload_dir = 'uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            // Generate a unique file name to prevent overwriting
            $new_image_name = uniqid('img_', true) . '.' . $image_extension;
            $dest_path = $upload_dir . $new_image_name;

            // Move the uploaded file to the destination directory
            if (move_uploaded_file($image_tmp_path, $dest_path)) {
                $image_path = 'uploads/' . $new_image_name;
            } else {
                // Handle upload error
                header("Location: products.php?edit_error=1");
                exit();
            }
        } else {
            // Handle invalid format
            header("Location: products.php?edit_error=1");
            exit();
        }
    }

    // Prepare the SQL statement for updating the product
    if ($image_path) {
        // Update all fields including image_path
        $stmt = $conn->prepare("UPDATE products SET name = ?, description = ?, original_price = ?, discounted_price = ?, image_path = ?, category_id = ? WHERE id = ?");
        $stmt->bind_param("ssdssii", $name, $description, $original_price, $discounted_price, $image_path, $category_id, $product_id);
    } else {
        // Update all fields except image_path
        $stmt = $conn->prepare("UPDATE products SET name = ?, description = ?, original_price = ?, discounted_price = ?, category_id = ? WHERE id = ?");
        $stmt->bind_param("ssdsii", $name, $description, $original_price, $discounted_price, $category_id, $product_id);
    }

    if ($stmt->execute()) {
        // Success: Redirect with success flag
        header("Location: products.php?edit_success=1");
    } else {
        // Failure: Redirect with error flag
        header("Location: products.php?edit_error=1");
    }

    $stmt->close();
    $conn->close();
} else {
    // Invalid request method
    header("Location: products.php");
    exit();
}
?>