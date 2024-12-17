<?php include 'db_connect.php'; 


$isLoggedIn = isset($_SESSION['username']) && !empty($_SESSION['username']);

function getCategories($conn) {
    if ($conn && !$conn->connect_error) {
        try {
            $stmt = $conn->prepare("SELECT id, name FROM categories ORDER BY name");
            if ($stmt) {
                $stmt->execute();
                return $stmt->get_result();
            }
        } catch (Exception $e) {
            error_log("Error fetching categories: " . $e->getMessage());
        }
    }
    return false;
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chibikku Kawaii</title>
    <link rel="stylesheet" href="header.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script>
        
document.addEventListener('DOMContentLoaded', function () {
    const cartIcon = document.getElementById('cart-icon');
    const cartPanel = document.getElementById('cart-panel');
    const closeCartButton = document.querySelector('.close-cart');  
    const cartItemsList = document.getElementById('cart-items-list');

    // Check if the cart icon is disabled (user not logged in)
    if (cartIcon.classList.contains('disabled')) {
        cartIcon.addEventListener('click', function (e) {
            e.preventDefault();
            alert('Please log in to access the cart.');
        });
        return; // Stop further execution if the cart is disabled
    }

    // Open cart panel and fetch cart items
    cartIcon.addEventListener('click', function (e) {
        e.preventDefault();
        cartPanel.classList.add('open'); // Add the 'open' class to slide in the cart panel
        fetchCartItems(); // Fetch and render cart items
    });

    // Close cart panel
    closeCartButton.addEventListener('click', function () {
        cartPanel.classList.remove('open'); // Remove the 'open' class to slide out the cart panel
    });

    // Fetch cart items from the server
   function fetchCartItems() {
    fetch('get_cart.php', {
        method: 'GET', // Use GET since we're fetching data, not sending it
        headers: {
            'Content-Type': 'application/json',
        },
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            renderCartItems(data.cart_items); // Render the cart items
        } else {
            console.error('Failed to fetch cart items:', data.message);
        }
    })
    .catch(error => {
        console.error('Error fetching cart items:', error);
    });
}

function renderCartItems(cartItems) {
    const cartItemsContainer = document.getElementById('cart-items-container');
    
    if (!cartItemsContainer) {
        console.error('Cart container not found');
        return;
    }

    // Check if the cart is empty
    if (!Array.isArray(cartItems) || cartItems.length === 0) {
        cartItemsContainer.innerHTML = `
            <p class="empty-cart-message">Your cart is empty</p>
        `;
        return;
    }

    let cartHTML = '<div class="cart-items">';
    let grandTotal = 0; // Initialize grand total

    // Loop through cart items and generate HTML
    cartItems.forEach(item => {
        const hasDiscount = parseFloat(item.discounted_price) < parseFloat(item.original_price || item.discounted_price);
        const price = hasDiscount ? parseFloat(item.discounted_price) : parseFloat(item.original_price || item.discounted_price);
        const total = price * item.quantity; // Calculate total for each product
        grandTotal += total; // Add to grand total

        cartHTML += `
            <div class="cart-item">
                <div class="cart-item-image">
                    <img src="${item.image_path || 'images/default-placeholder.png'}" alt="${item.name}">
                </div>
                <div class="cart-item-details">
                    <h3>${item.name}</h3>
                    <div class="price-container">
                        <p class="price">Price: $${price.toFixed(2)}</p>
                        <p class="quantity">Quantity: ${item.quantity}</p>
                        <p class="total">Total: $${total.toFixed(2)}</p>
                    </div>
                </div>
            </div>
        `;
    });

    cartHTML += '</div>'; // Close cart items container

    // Add the footer with the grand total and checkout button
    cartHTML += `
        <div class="cart-footer">
            <h3>Grand Total: $${grandTotal.toFixed(2)}</h3>
            <button class="checkout-button" onclick="proceedToCheckout()">Proceed to Checkout</button>
        </div>
    `;

    // Render the cart HTML
    cartItemsContainer.innerHTML = cartHTML;
}



    function updateCart() {
    fetch('get_cart.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderCartItems(data.cart_items);
            }
        })
        .catch(error => console.error('Error updating cart:', error));
}
});
    </script>   
    <script>
   document.addEventListener('DOMContentLoaded', function() {
    const addToCartButtons = document.querySelectorAll('.add-to-cart');
    
    addToCartButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const productId = this.getAttribute('data-product-id');
            console.log('Product ID:', productId); // Debugging line
            
            if (!productId) {
                alert('Product ID is missing. Please try again.');
                return;
            }
            
            const originalText = this.textContent;
            
            // Add loading state
            this.style.opacity = '0.7';
            this.textContent = 'Adding...';
            this.disabled = true;
            
            fetch('add_to_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'product_id=' + encodeURIComponent(productId)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update cart count
                    const cartCount = document.getElementById('cart-count');
                    if (cartCount) {
                        cartCount.textContent = data.cart_count;
                    }
                    
                    // Show success state
                    this.textContent = '✓ Added';
                    setTimeout(() => {
                        this.textContent = originalText;
                    }, 1500);
                    
                    // Refresh cart if open
                    if (typeof fetchCartItems === 'function') {
                        fetchCartItems();
                    }
                } else {
                    throw new Error(data.message || 'Failed to add item to cart');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert(error.message);
            })
            .finally(() => {
                // Restore button state
                this.style.opacity = '1';
                this.disabled = false;
                if (this.textContent === 'Adding...') {
                    this.textContent = originalText;
                }
            });
        });
    });
});
document.getElementById('checkout-button').addEventListener('click', function () {
    fetch('checkout.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            window.location.href = `order_confirmation.php?order_id=${data.order_id}`;
        } else {
            alert(data.message);
        }
    })
    .catch(error => console.error('Error during checkout:', error));
});
 </script>
</head>
<body>
<header class="header">
    <div class="container">
        <div class="header-content">
            <div class="logo">
                <img src="images/logo.png" alt="Logo Icon">
                <span class="logo-text">Chibikku Kawaii</span>
            </div>

            <nav class="nav">
    <div class="nav-links">
        <a href="index.php" class="nav-link">Home</a>
        <div class="dropdown">
            <a href="shop.php" class="nav-link">Shop</a>
            <div class="dropdown-content">
                <a href="shop.php">All Products</a>
                <?php
                // Fetch categories and display them in the dropdown
                $categories = getCategories($conn);
                if ($categories && $categories->num_rows > 0):
                    while ($category = $categories->fetch_assoc()):
                ?>
                    <a href="shop.php?category=<?php echo urlencode($category['id']); ?>">
                        <?php echo htmlspecialchars($category['name']); ?>
                    </a>
                <?php
                    endwhile;
                else:
                ?>
                    <a href="#">No Categories Available</a>
                <?php endif; ?>
            </div>
        </div>
    </div>



    <div class="icons">
        <?php if ($isLoggedIn): ?>
            <span class="username-display me-3">Hello, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
            <a href="logout.php" title="Logout" class="logout-button"><i class="fas fa-sign-out-alt"></i></a>
            <a href="#" id="cart-icon" class="cart-icon" title="Cart">
                <i class="fas fa-shopping-cart"></i>
            </a>
            <span id="cart-count" class="cart-count">0</span>
        <?php else: ?>
            <a href="#" title="Login" class="login-button" data-bs-toggle="modal" data-bs-target="#loginModal">
                <i class="fas fa-user"></i>
            </a>
            <a href="#" id="cart-icon" class="cart-icon disabled" title="Cart (Login Required)">
                <i class="fas fa-shopping-cart"></i>
            </a>
            <span id="cart-count" class="cart-count">0</span>
        <?php endif; ?>
    </div>
</nav>
        </div>
    </div>
</header>

<!-- Cart Panel -->
 <style>
 </style>

<!-- Cart Panel -->
<div id="cart-panel" class="cart-panel">
    <div class="cart-header">
        <h2>Your Cart</h2>
        <button class="close-cart">&times;</button>
    </div>
    <div class="cart-content">
        <div id="cart-items-container">
            <!-- Cart items will be displayed here -->
        </div>
        <script>
            function proceedToCheckout() {
    window.location.href = 'checkout.php'; // Redirect to the checkout page
}
        </script>
    </div>
</div>
       


</body>
</html>
