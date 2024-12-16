// js/header.js

document.addEventListener('DOMContentLoaded', function() {
    const searchButton = document.getElementById('search-button');
    const searchContainer = document.querySelector('.search-container');

    searchButton.addEventListener('click', function() {
        searchContainer.classList.toggle('active');
    });
});
// Open the Cart Sidebar
document.getElementById('cart-icon').addEventListener('click', function() {
    document.getElementById('cart-panel').classList.add('open');
});

// Close the Cart Sidebar
document.querySelector('.close-cart').addEventListener('click', function() {
    document.getElementById('cart-panel').classList.remove('open');
});

// Example to populate cart items dynamically (you need to fetch this from session or database)
function updateCart() {
    let cartItems = [
        { name: 'Item 1', quantity: 2, price: 10.00 },
        { name: 'Item 2', quantity: 1, price: 15.00 }
    ]; // Replace with actual data

    let cartItemsContainer = document.getElementById('cart-items');
    let total = 0;
    cartItemsContainer.innerHTML = ''; // Clear previous items

    cartItems.forEach(function(item) {
        let cartItem = document.createElement('div');
        cartItem.classList.add('cart-item');
        cartItem.innerHTML = `
            <div class="item-name">${item.name}</div>
            <div class="item-quantity">x${item.quantity}</div>
            <div class="item-price">$${(item.price * item.quantity).toFixed(2)}</div>
        `;
        cartItemsContainer.appendChild(cartItem);
        total += item.price * item.quantity;
    });

    document.querySelector('.cart-total').textContent = '$' + total.toFixed(2);
}
