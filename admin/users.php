<?php
// admin/users.php

include '../db_connect.php'; // Include your database connection

// Fetch all users
$sql = "SELECT id, username FROM users";
$result = $conn->query($sql);

// Initialize users array
$users = [];

if ($result->num_rows > 0) {
    // Output data of each row into the users array
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}

$conn->close(); // Close the database connection
// Display Delete Success Message
if (isset($_GET['delete_success']) && $_GET['delete_success'] == 1) {
    echo '<div class="mb-4 p-4 bg-green-100 text-green-700 rounded">User deleted successfully.</div>';
}

// Display Delete Error Message
if (isset($_GET['delete_error']) && $_GET['delete_error'] == 1) {
    echo '<div class="mb-4 p-4 bg-red-100 text-red-700 rounded">There was an error deleting the user. Please try again.</div>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - Users</title>
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
            background-color: #f5f5f5f5); /* Light background */
        }
    </style>
</head>
<body class="bg-gray-100">
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
                <button onclick="window.location.href='orders.php';"
                class="py-4 px-6 hover:bg-[#F78FAF] transition">orders</button>
            </nav>
        </aside>

        <!-- Overlay for mobile menu -->
        <div id="overlay" class="fixed inset-0 bg-black opacity-50 z-40 hidden sm:hidden"></div>

        <!-- Main Content -->
        <main class="flex-1 ml-0 sm:ml-64 p-4 md:p-6 transition-all duration-200">
            <div class="container mx-auto">
                <h2 class="text-3xl font-semibold text-gray-800 mb-6">Users</h2>
                
                <!-- Users Table -->
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white shadow-md rounded-lg overflow-hidden">
                        <thead class="bg-[#F7AFAF] text-white">
                            <tr>
                                <th class="py-3 px-6 text-left">ID</th>
                                <th class="py-3 px-6 text-left">Username</th>
                                <th class="py-3 px-6 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-700">
                            <?php if (!empty($users)): ?>
                                <?php foreach ($users as $user): ?>
                                    <tr class="border-b hover:bg-gray-100">
                                        <td class="py-4 px-6"><?php echo htmlspecialchars($user['id']); ?></td>
                                        <td class="py-4 px-6"><?php echo htmlspecialchars($user['username']); ?></td>
                                        <td class="py-4 px-6 text-center">
                                            <a href="#"
                                               class="text-indigo-600 hover:text-indigo-800 mr-4 edit-button"
                                               data-id="<?php echo htmlspecialchars($user['id']); ?>">
                                                Edit
                                            </a>
                                            <a href="#"
                                               class="text-red-600 hover:text-red-800 delete-button"
                                               data-id="<?php echo htmlspecialchars($user['id']); ?>">
                                                Delete
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" class="text-center py-4">No users found.</td>
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
                    const userId = this.getAttribute('data-id');
                    if (confirm('Are you sure you want to delete this user?')) {
                        fetch(`delete_user.php?id=${userId}`, { // Ensure delete_user.php is properly set up
                            method: 'GET', // Preferably change to 'POST' for security
                            headers: {
                                'Content-Type': 'application/json'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                alert('User deleted successfully.');
                                location.reload(); // Reloads the page to update the table
                            } else {
                                alert(data.message || 'Failed to delete user.');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('An error occurred while deleting the user.');
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

        // Edit User Form Toggle
        (function() {
            const editButtons = document.querySelectorAll('.edit-button');
            const usersTableSection = document.getElementById('products-table-section'); // Update ID if different
            const editUserFormSection = document.getElementById('add-product-form-section'); // Replace with actual edit form section ID
            const cancelEditButton = document.getElementById('cancel-button'); // Ensure this button exists

            editButtons.forEach(button => {
                button.addEventListener('click', (e) => {
                    e.preventDefault();
                    const userId = button.getAttribute('data-id');
                    populateEditUserForm(userId);
                    usersTableSection.classList.add('hidden');
                    editUserFormSection.classList.remove('hidden');
                });
            });

            // Cancel Edit Button Handler
            cancelEditButton.addEventListener('click', () => {
                editUserFormSection.classList.add('hidden');
                usersTableSection.classList.remove('hidden');
            });

            // Function to Fetch and Populate Edit User Form Data
            function populateEditUserForm(userId) {
                fetch(`get_user.php?id=${userId}`) // Ensure get_user.php is properly set up
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            document.getElementById('edit-user-id').value = data.user.id;
                            document.getElementById('edit-username').value = data.user.username;
                            // It's recommended **not** to expose passwords. If editing passwords, handle securely.
                        } else {
                            alert(data.message || 'Failed to fetch user details.');
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching user details:', error);
                        alert('An error occurred while fetching user details.');
                    });
            }
        })();
    </script>
</body>
</html>