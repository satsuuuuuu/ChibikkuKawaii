<?php
// Start the session at the very top
session_start();

// Include the database connection
require_once 'db_connect.php'; // Ensures the path is correct

// Enable error reporting for debugging (disable in production)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Determine if the user is logged in
$isLoggedIn = isset($_SESSION['user_id']);

// Clear the 'showLogin' session variable if set
$showLogin = $_SESSION['showLogin'] ?? false;
unset($_SESSION['showLogin']);

// Function to fetch the Featured category ID
function getFeaturedCategoryId($conn)
{
    $query = "SELECT id FROM categories WHERE name = 'Featured' LIMIT 1";
    if ($result = $conn->query($query)) {
        if ($result->num_rows > 0) {
            return $result->fetch_assoc()['id'];
        }
    }
    die("Featured category not found. Please ensure the 'Featured' category exists.");
}

// Function to fetch featured products
function getFeaturedProducts($conn, $categoryId)
{
    $query = "SELECT id, name, description, original_price, discounted_price, image_path 
              FROM products 
              WHERE category_id = ?";
    $stmt = $conn->prepare($query);

    if (!$stmt) {
        die("Prepare failed: " . htmlspecialchars($conn->error));
    }

    $stmt->bind_param("i", $categoryId);

    if (!$stmt->execute()) {
        die("Execute failed: " . htmlspecialchars($stmt->error));
    }

    $result = $stmt->get_result();
    $products = [];

    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }

    $stmt->close();
    return $products;
}

// Fetch the Featured category ID
$featuredCategoryId = getFeaturedCategoryId($conn);

// Fetch featured products
$featuredProducts = getFeaturedProducts($conn, $featuredCategoryId);

// Close the database connection
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Chibikku Kawaii - Home</title>
    
    <!-- Stylesheets -->

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@splidejs/splide@4.1.4/dist/css/splide.min.css">
    <link rel="stylesheet" href="css/all.min.css">
    <link rel="stylesheet" href="css/featured.css">
    <link rel="stylesheet" href="css/index.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/shop.css">
    <link rel="stylesheet" href="css/header.css">
    <link rel="stylesheet" href="css/footer.css">
    <link rel="stylesheet" href="css/cart-sidebar.css">
    <link rel="stylesheet" href="css/cart.css">
    <!-- Scripts -->
    <script>
        const isLoggedIn = <?php echo json_encode($isLoggedIn); ?>;
    </script>
    <script src="https://cdn.jsdelivr.net/npm/@splidejs/splide@4.1.4/dist/js/splide.min.js" defer></script>
    <script src="js/splide.js" defer></script>
    <script src="js/login.js" defer></script>
    <script src="js/cart.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" defer></script>
</head>
<body>

<?php 
    $isLoggedIn = isset($_SESSION['user_id']); // Add this line for header.php
    include 'includes/header.php'; 
    ?>
    <?php include 'includes/cart-sidebar.php'; ?>

    <!-- Main Content Wrapper -->
    <div class="wrapper">

        <!-- Hero Carousel Section -->
        <section class="hero-carousel">
            <div id="splide-hero" class="splide">
                <div class="splide__track">
                    <ul class="splide__list">
                        <?php for ($i = 1; $i <= 4; $i++): ?>
                            <li class="splide__slide">
                                <img src="images/carousel<?php echo $i; ?>.jpg" alt="Cute Kawaii Product <?php echo $i; ?>" class="product-image">
                                <div class="overlay-text">
                                    <h1>Adorable Plush Toys</h1>
                                    <p>Soft, cuddly, and perfect for any kawaii enthusiast!</p>
                                    <a href="shop.php" class="cta-button">Shop Now</a>
                                </div>
                            </li>
                        <?php endfor; ?>
                        <!-- Add more slides as needed -->
                    </ul>
                </div>
            </div>
        </section>
        
        <!-- Featured Merchandise Section -->
        <section class="featured-merchandise">
            <h2 class="section-title">Featured Anime Merchandise</h2>
            <?php if (!empty($featuredProducts)): ?>
                <div id="splide-featured" class="splide">
                    <div class="splide__track">
                        <ul class="splide__list">
                            <?php foreach ($featuredProducts as $product): ?>
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
        
        <!-- Script to Show Login Modal Based on URL Parameter -->
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const urlParams = new URLSearchParams(window.location.search);
                if (urlParams.has('showLogin')) {
                    const loginModal = new bootstrap.Modal(document.getElementById('loginModal'));
                    loginModal.show();
                }
            });
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