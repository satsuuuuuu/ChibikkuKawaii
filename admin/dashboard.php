<?php
// dashboard.php
include 'protect.php';
include '../db_connect.php';

// Enable error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Fetch all products from the database
$stmt = $conn->prepare("SELECT id, name, original_price, discounted_price, stock_status FROM products");
if ($stmt === false) {
    die("Prepare failed: " . htmlspecialchars($conn->error));
}

$stmt->execute();
$result = $stmt->get_result();

$products = [];
while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - Chibikku Kawaii</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="admin-container">
        <h1>Product Dashboard</h1>
        <a href="add_product.php" class="add-button">Add New Product</a>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Original Price</th>
                    <th>Discounted Price</th>
                    <th>Stock Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($products) > 0): ?>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($product['id']); ?></td>
                            <td><?php echo htmlspecialchars($product['name']); ?></td>
                            <td><?php echo htmlspecialchars($product['original_price']); ?></td>
                            <td><?php echo htmlspecialchars($product['discounted_price']); ?></td>
                            <td><?php echo htmlspecialchars($product['stock_status']); ?></td>
                            <td>
                                <a href="edit_product.php?id=<?php echo htmlspecialchars($product['id']); ?>" class="edit-button">Edit</a>
                                <a href="delete_product.php?id=<?php echo htmlspecialchars($product['id']); ?>" class="delete-button" onclick="return confirm('Are you sure you want to delete this product?');">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6">No products found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>