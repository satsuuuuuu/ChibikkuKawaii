<?php
// Start the session at the very top
session_start();

// Include the database connection
include 'db_connect.php'; // Ensure the path is correct

// Enable error reporting for debugging (disable in production)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Check if the user is logged in
$showLogin = isset($_SESSION['showLogin']) ? $_SESSION['showLogin'] : false;
unset($_SESSION['showLogin']); // Clear the session variable

// Fetch the ID of the "Featured" category
$featured_category_query = "SELECT id FROM categories WHERE name = 'Featured' LIMIT 1";
$featured_result = $conn->query($featured_category_query);

if ($featured_result && $featured_result->num_rows > 0) {
    $featured_category = $featured_result->fetch_assoc();
    $featured_category_id = $featured_category['id'];
} else {
    die("Featured category not found. Please ensure the 'Featured' category exists.");
}

// Fetch products categorized as "Featured"
$featured_products_query = "SELECT id, name, description, original_price, discounted_price, image_path FROM products WHERE category_id = ?";
$featured_stmt = $conn->prepare($featured_products_query);

if ($featured_stmt === false) {
    die("Prepare failed: " . htmlspecialchars($conn->error));
}

$featured_stmt->bind_param("i", $featured_category_id);

if (!$featured_stmt->execute()) {
    die("Execute failed: " . htmlspecialchars($featured_stmt->error));
}

$featured_result = $featured_stmt->get_result();
$featured_products = [];

while ($row = $featured_result->fetch_assoc()) {
    $featured_products[] = $row;
}

$featured_stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Chibikku Kawaii - Home</title>
    
    
    <script>
    let isLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;
</script>
<script src="https://cdn.jsdelivr.net/npm/@splidejs/splide@4.1.4/dist/js/splide.min.js" defer></script>
    <script src="js/splide.js" defer></script>
    <script src="js/login.js" defer></script>
    <link rel="stylesheet" href="css/header.css">
    <link rel="stylesheet" href="css/cart.css">
    <!-- CSS Files -->
    <link rel="stylesheet" href="css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@splidejs/splide@4.1.4/dist/css/splide.min.css">

    <link rel="stylesheet" href="css/index.css">
    <link rel="stylesheet" href="css/featured.css">
    <link rel="stylesheet" href="css/footer.css">
    <link rel="stylesheet" href="css/cart-sidebar.css">
    
    

    <!-- Include cart.js once after defining isLoggedIn -->
    <script src="js/cart.js" defer></script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" defer></script>
</head>

<body>

    <!-- Include Header -->
    <?php include 'includes/header.php'; ?>
    <?php include 'includes/cart-sidebar.php'; ?>
    <!-- Main Content Wrapper -->
    <div class="wrapper">

        <!-- Hero Carousel Section -->
        <section class="hero-carousel">
            <div id="splide-hero" class="splide">
                <div class="splide__track">
                    <ul class="splide__list">
                        <li class="splide__slide">
                            <img src="images/carousel1.jpg" alt="Cute Kawaii Product 1" class="product-image">
                            <div class="overlay-text">
                                <h1>Adorable Plush Toys</h1>
                                <p>Soft, cuddly, and perfect for any kawaii enthusiast!</p>
                                <a href="shop.php" class="cta-button">Shop Now</a>
                            </div>
                        </li>
                        <li class="splide__slide">
                            <img src="images/carousel2.jpg" alt="Cute Kawaii Product 2" class="product-image">
                            <div class="overlay-text">
                                <h1>Adorable Plush Toys</h1>
                                <p>Soft, cuddly, and perfect for any kawaii enthusiast!</p>
                                <a href="shop.php" class="cta-button">Shop Now</a>
                            </div>
                        </li>
                        <li class="splide__slide">
                            <img src="images/carousel3.jpg" alt="Cute Kawaii Product 3" class="product-image">
                            <div class="overlay-text">
                                <h1>Adorable Plush Toys</h1>
                                <p>Soft, cuddly, and perfect for any kawaii enthusiast!</p>
                                <a href="shop.php" class="cta-button">Shop Now</a>
                            </div>
                        </li>
                        <li class="splide__slide">
                            <img src="images/carousel4.jpg" alt="Cute Kawaii Product 4" class="product-image">
                            <div class="overlay-text">
                                <h1>Adorable Plush Toys</h1>
                                <p>Soft, cuddly, and perfect for any kawaii enthusiast!</p>
                                <a href="shop.php" class="cta-button">Shop Now</a>
                            </div>
                        </li>
                        <!-- Add more slides as needed -->
                    </ul>
                </div>
            </div>
        </section>
        
        <!-- Featured Anime Merchandise Carousel Section -->
<!-- Featured Merchandise Section -->
 
<section class="featured-merchandise">
    <h2 class="section-title">Featured Anime Merchandise</h2>
    <?php if (!empty($featured_products)): ?>
        <div id="splide-featured" class="splide">
            <div class="splide__track">
                <ul class="splide__list">
                    <?php foreach($featured_products as $product): ?>
                        <li class="splide__slide">
                            <div class="product-card">
                                <div class="product-image-container">
                                    <img src="<?php echo htmlspecialchars($product['image_path'] ?: 'images/default-placeholder.png'); ?>" 
                                         alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                         class="product-image" 
                                         loading="lazy">
                                </div>
                                <h3 class="product-name"><?php echo htmlspecialchars($product['name']); ?></h3>
                                <p class="description"><?php echo htmlspecialchars($product['description']); ?></p>
                                <p class="original-price">$<?php echo number_format($product['original_price'], 2); ?></p>
                                <p class="discounted-price">$<?php echo number_format($product['discounted_price'], 2); ?></p>
                                <button class="add-to-cart" data-product-id="<?php echo htmlspecialchars($product['id']); ?>">Add to Cart</button>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    <?php else: ?>
        <p>No featured anime available at the moment.</p>
    <?php endif; ?>
</section>

<!-- Login Modal -->
<div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="loginModalLabel">User Login</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="loginForm">
                    <div class="mb-3 text-start">
                        <label for="username" class="form-label">Username:</label>
                        <input type="text" class="form-control" id="username" name="username" placeholder="Enter your username" required>
                    </div>
                    <div class="mb-3 text-start">
                        <label for="password" class="form-label">Password:</label>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
                    </div>
                    <div id="loginError" class="alert alert-danger d-none" role="alert">
                        <!-- Error messages will be injected here -->
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Login</button>
                </form>
                <div class="mt-3 text-center">
                    <a href="signup.php">Don't have an account? Sign up here</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
        // Check if the URL contains the query parameter "showLogin"
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('showLogin')) {
            const loginModal = new bootstrap.Modal(document.getElementById('loginModal'));
            loginModal.show(); // Show the login modal
        }
    </script>


    <!-- Toast Container for Notifications -->
    <div aria-live="polite" aria-atomic="true" class="position-relative">
        <div class="toast-container position-fixed top-0 end-0 p-3">
            <!-- Toasts will be injected here dynamically -->
        </div>
    </div>
</div>
        
    <?php include 'includes/footer.php'; ?>
</body>
</html>