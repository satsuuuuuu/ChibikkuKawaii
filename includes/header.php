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
    fetch('get_cart.php')
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.text().then(text => {
                try {
                    return JSON.parse(text);
                } catch (e) {
                    console.error('Error parsing JSON:', text);
                    throw new Error('Invalid JSON response');
                }
            });
        })
        .then(data => {
            console.log('Cart Data:', data); // Debug output
            if (data.success) {
                renderCartItems(data.cart_items);
            } else {
                throw new Error(data.message || 'Failed to load cart items');
            }
        })
        .catch(error => {
            console.error('Error fetching cart items:', error);
            const cartItemsContainer = document.getElementById('cart-items-container');
            if (cartItemsContainer) {
                cartItemsContainer.innerHTML = `
                    <div class="cart-error">
                        <p>Error loading cart items</p>
                        <p class="error-details">${error.message}</p>
                    </div>
                `;
            }
        });
}

function renderCartItems(cartItems) {
    const cartItemsContainer = document.getElementById('cart-items-container');
    
    if (!cartItemsContainer) {
        console.error('Cart container not found');
        return;
    }

    if (!Array.isArray(cartItems) || cartItems.length === 0) {
        cartItemsContainer.innerHTML = '<p class="empty-cart-message">Your cart is empty</p>';
        return;
    }

    let cartHTML = '<div class="cart-items">';
    cartItems.forEach(item => {
        const hasDiscount = parseFloat(item.discounted_price) < parseFloat(item.original_price);
        
        cartHTML += `
            <div class="cart-item">
                <div class="cart-item-image">
                    <img src="${item.image_path || 'images/default-placeholder.png'}" alt="${item.name}">
                </div>
                <div class="cart-item-details">
                    <h3>${item.name}</h3>
                    <div class="price-container">
                        ${hasDiscount ? 
                            `<p class="original-price">$${parseFloat(item.original_price).toFixed(2)}</p>
                             <p class="discounted-price">$${parseFloat(item.discounted_price).toFixed(2)}</p>` :
                            `<p class="price">$${parseFloat(item.original_price).toFixed(2)}</p>`
                        }
                    </div>
                    <p class="quantity">Quantity: ${item.quantity}</p>
                    <p class="stock-status ${item.stock_status}">${item.stock_status.replace('_', ' ')}</p>
                </div>
            </div>
        `;
    });
    cartHTML += '</div>';
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
            const originalText = this.textContent;
            
            // Add loading state while maintaining your style
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
                    // Update cart count if available
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
 </script>
</head>
<body>
<header class="header">
    <div class="container">
        <div class="header-content">
            <div class="logo">
                <img src="images/logo.png" alt="Logo Icon">
                <span>Chibikku Kawaii</span>
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

    <div class="search-container">
        <input type="text" class="search-bar" placeholder="Search...">
        <button class="search-button" id="search-button">
            <i class="fas fa-search"></i>
        </button>
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
    </div>
</div>

</body>
</html>
