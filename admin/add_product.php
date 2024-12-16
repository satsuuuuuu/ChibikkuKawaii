<!-- admin/add_product.php -->
<?php
// Start the session and include necessary files
include 'protect.php'; // Ensure this file handles session protection
include '../db_connect.php'; // Database connection

// Enable error reporting for debugging (disable in production)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Initialize variables
$categories = [];

// Fetch categories from the database before rendering the form
$category_query = "SELECT id, name FROM categories ORDER BY name ASC";
$category_result = $conn->query($category_query);

if ($category_result && $category_result->num_rows > 0) {
    while ($row = $category_result->fetch_assoc()) {
        $categories[] = $row;
    }
} else {
    echo "<p>No categories found. Please add categories before adding products.</p>";
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Fetch and sanitize form data
    $name = htmlspecialchars(trim($_POST['name']));
    $description = htmlspecialchars(trim($_POST['description']));
    $original_price = floatval($_POST['original_price']);
    $discounted_price = floatval($_POST['discounted_price']);
    $category_id = intval($_POST['category_id']);

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $image_tmp_path = $_FILES['image']['tmp_name'];
        $image_name = basename($_FILES['image']['name']);
        $image_extension = strtolower(pathinfo($image_name, PATHINFO_EXTENSION));

        // Define allowed image extensions
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($image_extension, $allowed_extensions)) {
            // Define the upload directory
            $upload_dir = '../uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            // Generate a unique file name to prevent overwriting
            $new_image_name = uniqid('img_', true) . '.' . $image_extension;
            $dest_path = $upload_dir . $new_image_name;

            // Move the uploaded file to the destination directory
            if (move_uploaded_file($image_tmp_path, $dest_path)) {
                $image_path = 'uploads/' . $new_image_name; // Relative path for storage
            } else {
                echo "<p>Error uploading the image.</p>";
                $image_path = '';
            }
        } else {
            echo "<p>Invalid image format. Allowed formats: jpg, jpeg, png, gif.</p>";
            $image_path = '';
        }
    } else {
        echo "<p>Error with the image upload.</p>";
        $image_path = '';
    }

    // Proceed only if all required fields are filled and image is uploaded successfully
    if (!empty($name) && !empty($description) && $original_price > 0 && $discounted_price >= 0 && $category_id > 0 && !empty($image_path)) {
        // Prepare the SQL statement
        $stmt = $conn->prepare("INSERT INTO products (name, description, original_price, discounted_price, image_path, category_id) VALUES (?, ?, ?, ?, ?, ?)");
        if ($stmt === false) {
            die("Prepare failed: " . htmlspecialchars($conn->error));
        }

        // Bind parameters
        $stmt->bind_param("ssdssi", $name, $description, $original_price, $discounted_price, $image_path, $category_id);

        // Execute the statement
        if ($stmt->execute()) {
            echo "<p>Product added successfully.</p>";
            // Optionally, redirect to the dashboard or clear the form
            // header("Location: dashboard.php?message=Product+added+successfully");
            // exit();
        } else {
            echo "<p>Error adding product: " . htmlspecialchars($stmt->error) . "</p>";
        }

        // Close the statement
        $stmt->close();
    } else {
        echo "<p>Please ensure all fields are filled out correctly.</p>";
    }

    // Close the database connection
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add New Product - Admin Dashboard</title>
    <link rel="stylesheet" href="css/style.css"> <!-- Link to your admin CSS -->
</head>
<body>
    <div class="container">
        <h2>Add New Product</h2>
        <form action="add_product.php" method="POST" enctype="multipart/form-data">
            <label for="name">Product Name:</label>
            <input type="text" id="name" name="name" required>

            <label for="description">Description:</label>
            <textarea id="description" name="description" required></textarea>

            <label for="original_price">Original Price ($):</label>
            <input type="number" step="0.01" id="original_price" name="original_price" required>

            <label for="discounted_price">Discounted Price ($):</label>
            <input type="number" step="0.01" id="discounted_price" name="discounted_price" required>

            <label for="image">Product Image:</label>
            <input type="file" id="image" name="image" accept="image/*" required>

            <label for="category">Category:</label>
            <select name="category_id" id="category" required>
                <option value="">-- Select Category --</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?php echo htmlspecialchars($category['id']); ?>">
                        <?php echo htmlspecialchars($category['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="submit">Add Product</button>
        </form>
    </div>
</body>
</html>