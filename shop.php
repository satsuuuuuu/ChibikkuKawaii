<?php
session_start();
include 'db_connect.php';

// Get category filter if set
$category_id = isset($_GET['category']) ? (int)$_GET['category'] : null;

// Prepare the query based on category filter
if ($category_id) {
    $products_query = "SELECT id, name, description, original_price, discounted_price, image_path 
                      FROM products 
                      WHERE category_id = ?";
    $stmt = $conn->prepare($products_query);
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $products_result = $stmt->get_result();
} else {
    $products_query = "SELECT id, name, description, original_price, discounted_price, image_path 
                      FROM products";
    $products_result = $conn->query($products_query);
}

if (!$products_result) {
    die("Error fetching products: " . $conn->error);
}

$products = [];
while ($row = $products_result->fetch_assoc()) {
    $products[] = $row;
}

// Don't close the connection here as header.php needs it
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Shop - Anime Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/shop.css">
    <link rel="stylesheet" href="css/header.css">
    <link rel="stylesheet" href="css/footer.css">
    <link rel="stylesheet" href="css/cart-sidebar.css">
    <link rel="stylesheet" href="css/cart.css">
</head>
<body>
    <?php 
    $isLoggedIn = isset($_SESSION['user_id']); // Add this line for header.php
    include 'includes/header.php'; 
    ?>
    
    <div class="wrapper">
        <div class="container mt-5">
            <h2 class="mb-4">Our Products</h2>
            
            <!-- Add category filter buttons -->
            <div class="category-filters mb-4">
                <a href="shop.php" class="btn <?php echo !$category_id ? 'btn-primary' : 'btn-outline-primary'; ?>">
                    All Products
                </a>
                <?php
                $cat_query = "SELECT id, name FROM categories ORDER BY name";
                $cat_result = $conn->query($cat_query);
                while ($category = $cat_result->fetch_assoc()) {
                    $isActive = $category_id == $category['id'] ? 'btn-primary' : 'btn-outline-primary';
                    echo '<a href="shop.php?category=' . $category['id'] . '" class="btn ' . $isActive . ' ms-2">'
                         . htmlspecialchars($category['name']) . '</a>';
                }
                ?>
            </div>

            <div class="row">
                <?php foreach ($products as $product): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100">
                            <img src="<?php echo htmlspecialchars($product['image_path']); ?>" 
                                 class="card-img-top" 
                                 alt="<?php echo htmlspecialchars($product['name']); ?>">
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                                <p class="card-text"><?php echo htmlspecialchars($product['description']); ?></p>
                                <div class="mt-auto">
                                    <?php if ($product['discounted_price'] < $product['original_price']): ?>
                                        <p class="card-text mb-1">
                                            <span class="text-muted text-decoration-line-through">
                                                $<?php echo number_format($product['original_price'], 2); ?>
                                            </span>
                                            <span class="text-danger fw-bold">
                                                $<?php echo number_format($product['discounted_price'], 2); ?>
                                            </span>
                                        </p>
                                    <?php else: ?>
                                        <p class="card-text fw-bold">
                                            $<?php echo number_format($product['original_price'], 2); ?>
                                        </p>
                                    <?php endif; ?>
                                    <button class="add-to-cart" 
                                            data-product-id="<?php echo $product['id']; ?>">
                                        Add to Cart
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div aria-live="polite" aria-atomic="true" class="position-relative">
            <div class="toast-container position-fixed top-0 end-0 p-3">
            </div>
        </div>

        <div class="loading-overlay" id="loading-overlay">
            <div class="spinner"></div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/cart.js"></script>
    
    <?php
    // Close the connection at the end of the file
    $conn->close();
    ?>
</body>
</html>