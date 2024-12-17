<?php
// admin/update_product.php

// Include necessary files with corrected paths
include '../protect.php'; // Adjusted path for session protection
include '../db_connect.php'; // Adjusted path for database connection

// Enable error reporting for debugging (disable in production)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Function to handle secure image upload
function handleImageUpload($file) {
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
    $upload_dir = '../uploads/'; // Adjusted path to point to the root-level uploads directory
    $default_image = '../uploads/default-placeholder.png'; // Adjusted path for default image

    // Check if a file was uploaded
    if (!isset($file['name']) || $file['error'] !== UPLOAD_ERR_OK) {
        return ''; // Return empty string if no file uploaded
    }

    // Validate file type
    $file_info = pathinfo($file['name']);
    $file_extension = strtolower($file_info['extension']);
    if (!in_array($file_extension, $allowed_extensions)) {
        die("Invalid file type. Only JPG, JPEG, PNG, and GIF are allowed.");
    }

    // Validate MIME type
    $mime_type = mime_content_type($file['tmp_name']);
    if (!in_array($mime_type, ['image/jpeg', 'image/png', 'image/gif'])) {
        die("Invalid file type. Only JPG, JPEG, PNG, and GIF are allowed.");
    }

    // Generate a unique file name
    $new_file_name = uniqid('img_', true) . '.' . $file_extension;

    // Ensure the upload directory exists
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    // Move the uploaded file to the upload directory
    $destination = $upload_dir . $new_file_name;
    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        die("Failed to upload the image.");
    }

    return 'uploads/' . $new_file_name; // Return relative path for database storage
}

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
    $image_path = handleImageUpload($_FILES['image']);

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
        exit();
    } else {
        // Failure: Redirect with error flag
        header("Location: products.php?edit_error=1");
        exit();
    }

    $stmt->close();
    $conn->close();
} else {
    // Invalid request method
    header("Location: products.php");
    exit();
}
?>