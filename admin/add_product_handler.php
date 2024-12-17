<?php
// admin/add_product_handler.php

include '../db_connect.php';

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve and sanitize form inputs
    $name = htmlspecialchars(trim($_POST['name']));
    $description = htmlspecialchars(trim($_POST['description']));
    $original_price = floatval($_POST['original_price']);
    $discounted_price = floatval($_POST['discounted_price']);

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $image_tmp_path = $_FILES['image']['tmp_name'];
        $image_name = uniqid() . '_' . basename($_FILES['image']['name']); // Unique file name
        $image_upload_path = '../uploads/' . $image_name; // Updated to 'uploads/'

        // Move the uploaded file to the uploads directory
        if (move_uploaded_file($image_tmp_path, $image_upload_path)) {
            // Prepare and bind
            $stmt = $conn->prepare("INSERT INTO products (name, description, original_price, discounted_price, image_path) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("ssdds", $name, $description, $original_price, $discounted_price, $image_upload_path);

            // Execute the statement
            if ($stmt->execute()) {
                echo "New product added successfully.";
                // Redirect to dashboard or add_product page
                header("Location: dashboard.php?status=success");
                exit();
            } else {
                echo "Error: " . $stmt->error;
            }

            // Close the statement
            $stmt->close();
        } else {
            echo "Failed to move the uploaded image.";
        }
    } else {
        echo "Image upload error. Error Code: " . $_FILES['image']['error'];
    }
}

// Close the connection
$conn->close();
?>