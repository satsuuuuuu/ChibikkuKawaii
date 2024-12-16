<?php
// admin/dashboard.php

// Start the session and include necessary files
include 'protect.php'; // Ensure this file handles session protection
include '../db_connect.php'; // Corrected inclusion path

// Enable error reporting for debugging (disable in production)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Fetch total orders
$query = "SELECT COUNT(*) AS totalOrders FROM orders"; // Ensure 'orders' table exists
$result = mysqli_query($conn, $query);

if ($result) {
    $data = mysqli_fetch_assoc($result);
    $totalOrders = $data['totalOrders'];
} else {
    // Handle query error
    error_log("Query failed: " . mysqli_error($conn));
    $totalOrders = "N/A"; // Fallback value
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" type="image/x-icon" href="../static/assets/shop-logo.png">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap');

        html,
        body {
            font-family: 'Poppins', sans-serif;
            padding: 0;
            margin: 0;
            overflow-x: hidden;
        }
    </style>
</head>

<body class="bg-[#d1d1d1]">
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
             <!-- Logout Button -->
        <a href="logout.php"
           class="text-red-600 hover:text-red-800 px-4 py-2 rounded transition"
           onclick="return confirm('Are you sure you want to log out?');">
            Logout
        </a>
        </div>
    </header>

    <!-- Body -->
    <div class="flex flex-col md:flex-row">
        <!-- Sidebar -->
        <aside
            class="fixed top-16 left-0 w-64 h-full bg-[#F7AFAF] text-white transform -translate-x-full sm:translate-x-0 transition-transform duration-200 ease-in-out"
            id="sidebar">
            <nav class="flex flex-col mt-4">
                <button onclick="window.location.href='dashboard.php';"
                    class="py-4 px-6 hover:bg-[#F78FAF] transition">Dashboard</button>
                <button onclick="window.location.href='products.php';"
                    class="py-4 px-6 hover:bg-[#F78FAF] transition">Products</button>
                <button onclick="window.location.href='users.php';"
                    class="py-4 px-6 hover:bg-[#F78FAF] transition">Users</button>
                    <button onclick="window.location.href='orders.php';"
                    class="py-4 px-6 hover:bg-[#F78FAF] transition">orders</button>

            </nav>
        </aside>

        <!-- Overlay for mobile menu -->
        <div id="overlay" class="fixed inset-0 bg-black opacity-50 z-40 hidden sm:hidden"></div>

        <!-- Main Content -->
        <main class="flex-1 ml-0 sm:ml-64 p-4 md:p-6 transition-all duration-200">
            <div class="mb-6">
                <h1 class="text-3xl font-semibold text-gray-800">DASHBOARD</h1>
            </div>

            <!-- Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
                <div class="flex flex-col md:flex-row items-center justify-between bg-white p-6 rounded-xl shadow-md">
                    <div>
                        <p class="text-4xl font-bold text-[#F7AFAF]">
                            <?php echo $totalOrders; ?>
                        </p>
                        <p class="text-lg text-[#F7AFAF] font-semibold">Total Orders</p>
                    </div>
                    <div>
                        <svg class="w-12 h-12 text-[#F7AFAF]" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2 9m5-9v9m4-9v9m5-9l2 9">
                            </path>
                        </svg>
                    </div>
                </div>
                <!-- Add more cards as needed -->
            </div>

            <!-- Optional: Add more dashboard content here -->
        </main>
    </div>

    <!-- Scripts -->
    <script>
        const menuButton = document.getElementById('menu-button');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('overlay');

        menuButton.addEventListener('click', () => {
            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
        });

        overlay.addEventListener('click', () => {
            sidebar.classList.add('-translate-x-full');
            overlay.classList.add('hidden');
        });
    </script>
</body>

</html>