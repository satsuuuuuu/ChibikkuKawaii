<?php
// edit_product.php
include 'protect.php';
include '../db_connect.php';

// Enable error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Validate and sanitize the product ID
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $product_id = intval($_GET['id']);
} else {
    die("Invalid product ID.");
}

// Fetch product details including category_id
$stmt = $conn->prepare("SELECT name, description, original_price, discounted_price, image_path, category_id FROM products WHERE id = ?");
if ($stmt === false) {
    die("Prepare failed: " . htmlspecialchars($conn->error));
}

$stmt->bind_param("i", $product_id);

if (!$stmt->execute()) {
    die("Execute failed: " . htmlspecialchars($stmt->error));
}

$stmt->bind_result($name, $description, $original_price, $discounted_price, $image_path, $category_id);
if (!$stmt->fetch()) {
    die("Product not found.");
}

$stmt->close();

// Fetch categories
$category_query = "SELECT id, name FROM categories ORDER BY name ASC";
$category_result = $conn->query($category_query);

if (!$category_result) {
    die("Error fetching categories: " . $conn->error);
}

$categories = [];
while ($row = $category_result->fetch_assoc()) {
    $categories[] = $row;
}

$conn->close();

// Assign a default category if category_id is undefined
if (is_null($category_id) || empty($category_id)) {
    $category_id = 2; // Replace with your default category ID
}

// Handle image upload securely
function handleImageUpload($file) {
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
    $upload_dir = 'uploads/';
    $default_image = 'uploads/default-placeholder.png';

    // Check if a file was uploaded
    if (!isset($file['name']) || $file['error'] !== UPLOAD_ERR_OK) {
        return $default_image; // Return default image if no file uploaded
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

    // Move the uploaded file to the upload directory
    $destination = $upload_dir . $new_file_name;
    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        die("Failed to upload the image.");
    }

    return $destination;
}

// Example usage of handleImageUpload (if needed in the update process)
// $uploaded_image_path = handleImageUpload($_FILES['image']);
?>