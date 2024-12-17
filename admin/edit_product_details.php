<!-- admin/edit_product_details.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Product Details - Admin Dashboard</title>
    <link rel="stylesheet" href="css/style.css"> <!-- Link to your admin CSS -->
</head>
<body>
    <div class="container">
        <h2>Edit Product Details</h2>
        <?php
        // admin/edit_product_details.php
        
        include '../db_connect.php';

        if (isset($_GET['id'])) {
            $id = intval($_GET['id']);

            // Prepare statement to fetch product details
            $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $product = $result->fetch_assoc();
                ?>
                <form action="update_product.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="id" value="<?php echo $product['id']; ?>">

                    <label for="name">Product Name:</label>
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>

                    <label for="description">Description:</label>
                    <textarea id="description" name="description" required><?php echo htmlspecialchars($product['description']); ?></textarea>

                    <label for="original_price">Original Price ($):</label>
                    <input type="number" step="0.01" id="original_price" name="original_price" value="<?php echo $product['original_price']; ?>" required>

                    <label for="discounted_price">Discounted Price ($):</label>
                    <input type="number" step="0.01" id="discounted_price" name="discounted_price" value="<?php echo $product['discounted_price']; ?>" required>

                    <label for="current_image">Current Product Image:</label>
                    <div class="current-image">
                        <img src="<?php echo htmlspecialchars($product['image_path'] ?: 'uploads/default-placeholder.png'); ?>" alt="Product Image" width="200">
                    </div>

                    <label for="image">Change Product Image:</label>
                    <input type="file" id="image" name="image" accept="image/*">

                    <label for="category">Category:</label>
                    <select name="category_id" id="category" required>
                        <?php
                        // Fetch categories
                        $category_query = "SELECT id, name FROM categories ORDER BY name ASC";
                        $category_result = $conn->query($category_query);

                        if ($category_result->num_rows > 0) {
                            while ($category = $category_result->fetch_assoc()) {
                                $selected = ($category['id'] == $product['category_id']) ? 'selected' : '';
                                echo "<option value='" . htmlspecialchars($category['id']) . "' $selected>" . htmlspecialchars($category['name']) . "</option>";
                            }
                        } else {
                            echo "<option value=''>No categories available</option>";
                        }
                        ?>
                    </select>

                    <button type="submit">Update Product</button>
                </form>
                <?php
            } else {
                echo "Product not found.";
            }

            $stmt->close();
        } else {
            echo "Invalid product ID.";
        }

        $conn->close();
        ?>
    </div>
</body>
</html>