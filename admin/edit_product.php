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
    // Assign 'In Stock' category with id=2
    $category_id = 2; // Replace with your default category ID
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Product - Chibikku Kawaii</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="admin-container">
        <h1>Edit Product</h1>
        <form action="update_product.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($product_id); ?>">
            
            <label for="name">Product Name:</label>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" required>
            
            <label for="description">Description:</label>
            <textarea id="description" name="description" required><?php echo htmlspecialchars($description); ?></textarea>
            
            <label for="original_price">Original Price:</label>
            <input type="number" id="original_price" name="original_price" value="<?php echo htmlspecialchars($original_price); ?>" step="0.01" required>
            
            <label for="discounted_price">Discounted Price:</label>
            <input type="number" id="discounted_price" name="discounted_price" value="<?php echo htmlspecialchars($discounted_price); ?>" step="0.01" required>
            
            <label for="current_image">Current Image:</label>
            <div class="current-image">
                <img src="<?php echo htmlspecialchars($image_path ?: 'uploads/default-placeholder.png'); ?>" alt="<?php echo htmlspecialchars($name); ?>" width="200">
            </div>
            
            <label for="image">Upload New Image (Optional):</label>
            <input type="file" id="image" name="image" accept="image/*">
            
            <label for="category">Category:</label>
            <select name="category_id" id="category" required>
                <option value="">-- Select Category --</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?php echo htmlspecialchars($category['id']); ?>" <?php if ($category['id'] == $category_id) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($category['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            
            <button type="submit">Update Product</button>
        </form>
    </div>
</body>
</html>