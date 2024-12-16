<?php
// admin/orders.php

include '../db_connect.php'; // Include your database connection

// Fetch all orders with related user and product information
$sql = "SELECT orders.id, users.username, products.name AS product_name, orders.quantity, orders.total_price, orders.order_date, orders.status
        FROM orders
        JOIN users ON orders.user_id = users.id
        JOIN products ON orders.product_id = products.id
        ORDER BY orders.order_date DESC";
$result = $conn->query($sql);

// Initialize orders array
$orders = [];

if ($result->num_rows > 0) {
    // Fetch each order into the orders array
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
}

$conn->close(); // Close the database connection
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - Orders</title>
    <script src="https://cdn.tailwindcss.com"></script> <!-- Tailwind CSS -->
    <link rel="icon" type="image/x-icon" href="../static/assets/shop-logo.png">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap');

        html,
        body {
            font-family: 'Poppins', sans-serif;
            padding: 0;
            margin: 0;
            overflow-x: hidden;
            background-color: #f7fafc; /* Light background */
        }
    </style>
</head>
<body class="bg-[#f5f5f5]">
    <!-- Header -->
    <header class="fixed top-0 left-0 w-full h-16 bg-[#F7AFAF] flex justify-between items-center px-4 md:px-24">
        <div class="flex items-center gap-x-4 font-semibold text-white text-xl">
            <img class="w-8 hidden sm:block" src="../images/logo.png" alt="Shop Logo">
            <p class="cursor-pointer">CHIBIKKUKAWAII.SHOP</p>
        </div>
        <div class="flex items-center gap-x-5 font-semibold text-white text-xl">
            <p>ADMIN</p>
            <!-- Mobile menu button -->
            <button class="sm:hidden focus:outline-none" id="menu-button">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                    xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
            </button>
        </div>
    </header>

    <!-- Body -->
    <div class="flex flex-col md:flex-row pt-16">
        <!-- Sidebar -->
        <aside class="fixed top-16 left-0 w-64 h-full bg-[#F7AFAF] text-white transform -translate-x-full sm:translate-x-0 transition-transform duration-200 ease-in-out" id="sidebar">
            <nav class="flex flex-col mt-4">
                <button onclick="window.location.href='dashboard.php';" class="py-4 px-6 hover:bg-[#F78FAF] transition">Dashboard</button>
                <button onclick="window.location.href='products.php';" class="py-4 px-6 hover:bg-[#F78FAF] transition">Products</button>
                <button onclick="window.location.href='users.php';" class="py-4 px-6 hover:bg-[#F78FAF] transition">Users</button>
                <button onclick="window.location.href='orders.php';" class="py-4 px-6 bg-[#D1D1D1] text-[#F7AFAF] hover:bg-[#C0C0C0] transition">Orders</button>
            </nav>
        </aside>

        <!-- Overlay for mobile menu -->
        <div id="overlay" class="fixed inset-0 bg-black opacity-50 z-40 hidden sm:hidden"></div>

        <!-- Main Content -->
        <main class="flex-1 ml-0 sm:ml-64 p-4 md:p-6 transition-all duration-200">
            <div class="container mx-auto">
                <h2 class="text-3xl font-semibold text-gray-800 mb-6">Orders</h2>

                <!-- Display Feedback Messages -->
                <?php
                // Display Delete Success Message
                if (isset($_GET['delete_success']) && $_GET['delete_success'] == 1) {
                    echo '<div class="mb-4 p-4 bg-green-100 text-green-700 rounded">Order deleted successfully.</div>';
                }

                // Display Delete Error Message
                if (isset($_GET['delete_error']) && $_GET['delete_error'] == 1) {
                    echo '<div class="mb-4 p-4 bg-red-100 text-red-700 rounded">There was an error deleting the order. Please try again.</div>';
                }
                ?>

                <!-- Orders Table -->
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white shadow-md rounded-lg overflow-hidden">
                        <thead class="bg-[#F7AFAF] text-white">
                            <tr>
                                <th class="py-3 px-6 text-left">Order ID</th>
                                <th class="py-3 px-6 text-left">Username</th>
                                <th class="py-3 px-6 text-left">Product</th>
                                <th class="py-3 px-6 text-left">Quantity</th>
                                <th class="py-3 px-6 text-left">Total Price ($)</th>
                                <th class="py-3 px-6 text-left">Order Date</th>
                                <th class="py-3 px-6 text-left">Status</th>
                                <th class="py-3 px-6 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-700">
                            <?php if (!empty($orders)): ?>
                                <?php foreach ($orders as $order): ?>
                                    <tr class="border-b hover:bg-gray-100">
                                        <td class="py-4 px-6"><?php echo htmlspecialchars($order['id']); ?></td>
                                        <td class="py-4 px-6"><?php echo htmlspecialchars($order['username']); ?></td>
                                        <td class="py-4 px-6"><?php echo htmlspecialchars($order['product_name']); ?></td>
                                        <td class="py-4 px-6"><?php echo htmlspecialchars($order['quantity']); ?></td>
                                        <td class="py-4 px-6"><?php echo htmlspecialchars($order['total_price']); ?></td>
                                        <td class="py-4 px-6"><?php echo htmlspecialchars($order['order_date']); ?></td>
                                        <td class="py-4 px-6"><?php echo htmlspecialchars($order['status']); ?></td>
                                        <td class="py-4 px-6 text-center">
                                            <a href="#"
                                               class="text-indigo-600 hover:text-indigo-800 mr-4 edit-button"
                                               data-id="<?php echo htmlspecialchars($order['id']); ?>">
                                                Edit
                                            </a>
                                            <a href="#"
                                               class="text-red-600 hover:text-red-800 delete-button"
                                               data-id="<?php echo htmlspecialchars($order['id']); ?>">
                                                Delete
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center py-4">No orders found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- Scripts -->
    <script>
        // Delete Functionality
        (function() {
            const deleteButtons = document.querySelectorAll('.delete-button');

            deleteButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const orderId = this.getAttribute('data-id');
                    const tableRow = this.closest('tr'); // Get the closest table row

                    if (confirm('Are you sure you want to delete this order?')) {
                        fetch(`delete_order.php?id=${orderId}`, { // Ensure delete_order.php is properly set up
                            method: 'GET', // Change to 'POST' if implementing POST method
                            headers: {
                                'Content-Type': 'application/json'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                alert('Order deleted successfully.');
                                tableRow.remove(); // Remove the row from the table
                            } else {
                                alert(data.message || 'Failed to delete order.');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('An error occurred while deleting the order.');
                        });
                    }
                });
            });
        })();

        // Mobile Sidebar Toggle
        (function() {
            const menuButton = document.getElementById('menu-button');
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('overlay');

            if (menuButton && sidebar && overlay) {
                menuButton.addEventListener('click', () => {
                    sidebar.classList.toggle('-translate-x-full');
                    overlay.classList.toggle('hidden');
                });

                overlay.addEventListener('click', () => {
                    sidebar.classList.add('-translate-x-full');
                    overlay.classList.add('hidden');
                });
            }
        })();

        // Edit Order Functionality
        (function() {
            const editButtons = document.querySelectorAll('.edit-button');
            const editFormSection = document.getElementById('edit-order-form-section'); // Ensure this ID exists
            const ordersTableSection = document.getElementById('products-table-section'); // Update ID if different
            const cancelEditButton = document.getElementById('cancel-edit-button'); // Ensure this button exists

            editButtons.forEach(button => {
                button.addEventListener('click', (e) => {
                    e.preventDefault();
                    const orderId = button.getAttribute('data-id');
                    populateEditOrderForm(orderId);
                    ordersTableSection.classList.add('hidden');
                    editFormSection.classList.remove('hidden');
                });
            });

            // Cancel Edit Button Handler
            cancelEditButton.addEventListener('click', () => {
                editFormSection.classList.add('hidden');
                ordersTableSection.classList.remove('hidden');
            });

            // Function to Fetch and Populate Edit Order Form Data
            function populateEditOrderForm(orderId) {
                fetch(`get_order.php?id=${orderId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            document.getElementById('edit-order-id').value = data.order.id;
                            document.getElementById('edit-status').value = data.order.status;
                            // Add other fields as necessary
                        } else {
                            alert(data.message || 'Failed to fetch order details.');
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching order details:', error);
                        alert('An error occurred while fetching order details.');
                    });
            }
        })();
    </script>

    <!-- Edit Order Form Section (Hidden by Default) -->
    <div id="edit-order-form-section" class="hidden fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center">
        <div class="bg-white p-6 rounded-lg shadow-lg w-1/3">
            <h2 class="text-2xl font-semibold mb-4">Edit Order</h2>
            <form action="update_order.php" method="POST">
                <input type="hidden" name="id" id="edit-order-id">

                <div class="mb-4">
                    <label for="edit-status" class="block text-gray-700">Status:</label>
                    <select name="status" id="edit-status"
                            class="w-full mt-1 p-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-[#F7AFAF]" required>
                        <option value="">-- Select Status --</option>
                        <option value="Pending">Pending</option>
                        <option value="Processing">Processing</option>
                        <option value="Completed">Completed</option>
                        <option value="Cancelled">Cancelled</option>
                    </select>
                </div>

                <!-- Add more fields as necessary -->

                <div class="flex justify-end">
                    <button type="button" id="cancel-edit-button"
                            class="mr-4 bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 transition">Cancel</button>
                    <button type="submit"
                            class="bg-[#F7AFAF] text-white px-4 py-2 rounded hover:bg-[#F0A0A0] transition">Update Order</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
