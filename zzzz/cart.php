<!-- cart.php -->
<?php
session_start();

// Check if cart is empty
if (empty($_SESSION['cart'])) {
    echo "Your cart is empty.";
} else {
    echo "Items in your cart:";
    // Display cart items here
    foreach ($_SESSION['cart'] as $product_id) {
        echo "Product ID: " . $product_id . "<br>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Head content -->
    <link rel="stylesheet" href="css/cart.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loading-overlay">
        <div class="spinner"></div>
    </div>

    <div id="cart-panel" class="cart-panel">
        <div class="cart-panel-header">
            <h3>Your Cart</h3>
            <button class="close-cart">&times;</button>
        </div>
        <div class="cart-panel-content">
            <div id="cart-items"></div>
        </div>
        <div class="cart-panel-footer">
            <p>Total: <span class="cart-total">$0.00</span></p>
            <button id="checkout-button" class="btn btn-primary">Proceed to Checkout</button>
        </div>
    </div>

    <!-- Toast Container for Notifications -->
    <div aria-live="polite" aria-atomic="true" class="position-relative">
        <div class="toast-container position-fixed top-0 end-0 p-3">
            <!-- Toasts will be injected here dynamically -->
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/cart.js"></script>
</body>
</html>