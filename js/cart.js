document.addEventListener('DOMContentLoaded', function () {
    const cartIcon = document.getElementById('cart-icon');
    const cartPanel = document.getElementById('cart-panel');
    const closeCartButton = document.querySelector('.close-cart');

    // Check if the cart icon is disabled
    if (cartIcon.classList.contains('disabled')) {
        cartIcon.addEventListener('click', function (e) {
            e.preventDefault();
            alert('Please log in to access the cart.');
        });
        return; // Stop further execution if the cart is disabled
    }

    // Open cart panel
    cartIcon.addEventListener('click', function (e) {
        e.preventDefault();
        cartPanel.classList.add('open'); // Add the 'open' class to slide in the cart panel
    });

    // Close cart panel
    closeCartButton.addEventListener('click', function () {
        cartPanel.classList.remove('open'); // Remove the 'open' class to slide out the cart panel
    });
});