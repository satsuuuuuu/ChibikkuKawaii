<?php
// admin/products.php

// Start the session and include necessary files
include 'protect.php'; // Ensure this file handles session protection
include '../db_connect.php'; // Securely include your database connection

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Fetch and sanitize form data
    $name = htmlspecialchars(trim($_POST['name']));
    $description = htmlspecialchars(trim($_POST['description']));
    $original_price = floatval($_POST['original_price']);
    $discounted_price = floatval($_POST['discounted_price']);
    $category_id = intval($_POST['category_id']);

    // Handle image upload
    $image_path = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $image_tmp_path = $_FILES['image']['tmp_name'];
        $image_name = basename($_FILES['image']['name']);
        $image_extension = strtolower(pathinfo($image_name, PATHINFO_EXTENSION));

        // Define allowed image extensions
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($image_extension, $allowed_extensions)) {
            // Define the upload directory
            $upload_dir = 'uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            // Generate a unique file name to prevent overwriting
            $new_image_name = uniqid('img_', true) . '.' . $image_extension;
            $dest_path = $upload_dir . $new_image_name;

            // Move the uploaded file to the destination directory
            if (move_uploaded_file($image_tmp_path, $dest_path)) {
                $image_path = 'uploads/' . $new_image_name; // Relative path for storage
            } else {
                $error_message = "Error uploading the image.";
            }
        } else {
            $error_message = "Invalid image format. Allowed formats: jpg, jpeg, png, gif.";
        }
    } else {
        $error_message = "Error with the image upload.";
    }

    // Proceed only if all required fields are filled and image is uploaded successfully
    if (!empty($name) && !empty($description) && $original_price > 0 && $discounted_price >= 0 && $category_id > 0 && !empty($image_path)) {
        // Prepare the SQL statement
        $stmt = $conn->prepare("INSERT INTO products (name, description, original_price, discounted_price, image_path, category_id) VALUES (?, ?, ?, ?, ?, ?)");
        if ($stmt === false) {
            $error_message = "Prepare failed: " . htmlspecialchars($conn->error);
        } else {
            // Bind parameters
            $stmt->bind_param("ssdssi", $name, $description, $original_price, $discounted_price, $image_path, $category_id);

            // Execute the statement
            if ($stmt->execute()) {
                $success_message = "Product added successfully.";
                // Clear form data
                $_POST = [];
            } else {
                $error_message = "Error adding product: " . htmlspecialchars($stmt->error);
            }

            // Close the statement
            $stmt->close();
        }
    } else {
        $error_message = isset($error_message) ? $error_message : "Please ensure all fields are filled out correctly.";
    }
}

// Fetch products
$query = "SELECT * FROM products";
$result = mysqli_query($conn, $query);

if ($result) {
    $products = mysqli_fetch_all($result, MYSQLI_ASSOC);
} else {
    // Handle query error
    error_log("Query failed: " . mysqli_error($conn));
    $products = [];
}

// Fetch categories for the Add Product form
$category_query = "SELECT id, name FROM categories ORDER BY name ASC";
$category_result = $conn->query($category_query);

$categories = [];
if ($category_result && $category_result->num_rows > 0) {
    while ($row = $category_result->fetch_assoc()) {
        $categories[] = $row;
    }
} else {
    $categories = [];
}
if (isset($_GET['delete_success']) && $_GET['delete_success'] == 1) {
    echo '<div class="mb-4 p-4 bg-green-100 text-green-700 rounded">Product deleted successfully.</div>';
}

// Display Delete Error Message
if (isset($_GET['delete_error']) && $_GET['delete_error'] == 1) {
    echo '<div class="mb-4 p-4 bg-red-100 text-red-700 rounded">There was an error deleting the product. Please try again.</div>';
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - Products</title>
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
        <aside
            class="fixed top-16 left-0 w-64 h-full bg-[#F7AFAF] text-white transform -translate-x-full sm:translate-x-0 transition-transform duration-200 ease-in-out"
            id="sidebar">
            <nav class="flex flex-col mt-4">
                <button onclick="window.location.href='dashboard.php';"
                    class="py-4 px-6 hover:bg-[#F78FAF] transition">Dashboard</button>
                <button onclick="showProductsTable();"
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
            <div class="mb-6 flex justify-between items-center">
                <h1 class="text-3xl font-semibold text-gray-800">Products</h1>
                <button id="add-product-button"
                    class="bg-white text-[#F7AFAF] px-4 py-2 rounded hover:bg-gray-200 transition">Add Product</button>
            </div>

            <!-- Products Table Section -->
            <div id="products-table-section">
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white shadow-md rounded-lg overflow-hidden">
                        <thead class="bg-[#F7AFAF] text-white">
                            <tr>
                                <th class="py-3 px-6 text-left">ID</th>
                                <th class="py-3 px-6 text-left">Product Name</th>
                                <th class="py-3 px-6 text-left">Original Price ($)</th>
                                <th class="py-3 px-6 text-left">Discounted Price ($)</th>
                                <th class="py-3 px-6 text-left">Stock Status</th>
                                <th class="py-3 px-6 text-left">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-700">
                            <?php if (!empty($products)): ?>
                                <?php foreach ($products as $product): ?>
                                    <tr class="border-b hover:bg-gray-100">
                                        <td class="py-4 px-6"><?php echo htmlspecialchars($product['id']); ?></td>
                                        <td class="py-4 px-6"><?php echo htmlspecialchars($product['name']); ?></td>
                                        <td class="py-4 px-6"><?php echo number_format(htmlspecialchars($product['original_price']), 2); ?></td>
                                        <td class="py-4 px-6"><?php echo number_format(htmlspecialchars($product['discounted_price']), 2); ?></td>
                                        <td class="py-4 px-6"><?php echo htmlspecialchars($product['stock_status']); ?></td>
                                        <td class="py-4 px-6">

<a href="#"
   class="text-indigo-600 hover:text-indigo-800 mr-4 edit-button"
   data-id="<?php echo htmlspecialchars($product['id']); ?>">
    Edit
</a>
                                            <!-- Delete Button in Products Table -->
                                            <a href="#"
                           class="text-red-600 hover:text-red-800 delete-button"
                           data-id="<?php echo htmlspecialchars($product['id']); ?>">
                            Delete
                        </a>
<!-- Products Table Row -->
<tr class="border-b hover:bg-gray-100">

                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4">No products found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
<!-- edit product form -->
<div id="edit-product-form-section" class="hidden bg-white p-6 rounded-lg shadow-md">
    <h2 class="text-2xl font-semibold mb-4">Edit Product</h2>

    <!-- Display Success and Error Messages -->
    <?php if (isset($_GET['edit_success']) && $_GET['edit_success'] == 1): ?>
        <div class="mb-4 p-4 bg-green-100 text-green-700 rounded">
            Product updated successfully.
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['edit_error']) && $_GET['edit_error'] == 1): ?>
        <div class="mb-4 p-4 bg-red-100 text-red-700 rounded">
            There was an error updating the product. Please try again.
        </div>
    <?php endif; ?>

    <form id="edit-product-form" action="update_product.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id" id="edit-product-id">

        <div class="mb-4">
            <label for="edit-name" class="block text-gray-700">Product Name:</label>
            <input type="text" id="edit-name" name="name"
                   class="w-full mt-1 p-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-[#F7AFAF]" required>
        </div>

        <div class="mb-4">
            <label for="edit-description" class="block text-gray-700">Description:</label>
            <textarea id="edit-description" name="description"
                      class="w-full mt-1 p-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-[#F7AFAF]"
                      rows="4" required></textarea>
        </div>

        <div class="mb-4">
            <label for="edit-original_price" class="block text-gray-700">Original Price ($):</label>
            <input type="number" step="0.01" id="edit-original_price" name="original_price"
                   class="w-full mt-1 p-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-[#F7AFAF]" required>
        </div>

        <div class="mb-4">
            <label for="edit-discounted_price" class="block text-gray-700">Discounted Price ($):</label>
            <input type="number" step="0.01" id="edit-discounted_price" name="discounted_price"
                   class="w-full mt-1 p-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-[#F7AFAF]" required>
        </div>

        <div class="mb-4">
            <label for="edit-image" class="block text-gray-700">Product Image:</label>
            <input type="file" id="edit-image" name="image" accept="image/*"
                   class="w-full mt-1 p-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-[#F7AFAF]">
            <p class="mt-2 text-sm text-gray-600" id="current-image-text"></p>
            <img id="current-image" src="" alt="Current Product Image" class="w-20 h-20 object-cover mt-2">
        </div>

        <div class="mb-4">
            <label for="edit-category" class="block text-gray-700">Category:</label>
            <select name="category_id" id="edit-category"
                    class="w-full mt-1 p-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-[#F7AFAF]" required>
                <option value="">-- Select Category --</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?php echo htmlspecialchars($category['id']); ?>">
                        <?php echo htmlspecialchars($category['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="flex justify-end">
            <button type="button" id="cancel-edit-button"
                    class="mr-4 bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 transition">Cancel</button>
            <button type="submit"
                    class="bg-[#F7AFAF] text-white px-4 py-2 rounded hover:bg-[#F0A0A0] transition">Update Product</button>
        </div>
    </form>
    
</div>
            <!-- Add Product Form Section (Hidden by Default) -->
            <div id="add-product-form-section" class="hidden">
                <div class="bg-white p-6 rounded-lg shadow-md">
                   

                    <!-- Display Success Message -->
                    <?php if (isset($success_message)): ?>
                        <div class="mb-4 p-4 bg-green-100 text-green-700 rounded">
                            <?php echo htmlspecialchars($success_message); ?>
                        </div>
                    <?php endif; ?>

                    <!-- Display Error Message -->
                    <?php if (isset($error_message)): ?>
                        <div class="mb-4 p-4 bg-red-100 text-red-700 rounded">
                            <?php echo htmlspecialchars($error_message); ?>
                        </div>
                    <?php endif; ?>

                    <form action="products.php" method="POST" enctype="multipart/form-data">
                        <div class="mb-4">
                            <label for="name" class="block text-gray-700">Product Name:</label>
                            <input type="text" id="name" name="name"
                                class="w-full mt-1 p-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-[#F7AFAF]"
                                required>
                        </div>

                        <div class="mb-4">
                            <label for="description" class="block text-gray-700">Description:</label>
                            <textarea id="description" name="description"
                                class="w-full mt-1 p-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-[#F7AFAF]"
                                rows="4" required></textarea>
                        </div>

                        <div class="mb-4">
                            <label for="original_price" class="block text-gray-700">Original Price ($):</label>
                            <input type="number" step="0.01" id="original_price" name="original_price"
                                class="w-full mt-1 p-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-[#F7AFAF]"
                                required>
                        </div>

                        <div class="mb-4">
                            <label for="discounted_price" class="block text-gray-700">Discounted Price ($):</label>
                            <input type="number" step="0.01" id="discounted_price" name="discounted_price"
                                class="w-full mt-1 p-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-[#F7AFAF]"
                                required>
                        </div>

                        <div class="mb-4">
                            <label for="image" class="block text-gray-700">Product Image:</label>
                            <input type="file" id="image" name="image" accept="image/*"
                                class="w-full mt-1 p-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-[#F7AFAF]"
                                required>
                        </div>

                        <div class="mb-4">
                            <label for="category" class="block text-gray-700">Category:</label>
                            <select name="category_id" id="category"
                                class="w-full mt-1 p-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-[#F7AFAF]"
                                required>
                                <option value="">-- Select Category --</option>
                                <?php if (!empty($categories)): ?>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo htmlspecialchars($category['id']); ?>">
                                            <?php echo htmlspecialchars($category['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <option value="">No Categories Available</option>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="flex justify-end">
                            <button type="button" id="cancel-button"
                                class="mr-4 bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 transition">Cancel</button>
                            <button type="submit"
                                class="bg-[#F7AFAF] text-white px-4 py-2 rounded hover:bg-[#F0A0A0] transition">Add Product</button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

   <!-- Scripts -->
<!-- Delete Functionality -->
<script>
    (function() {
        const deleteButtons = document.querySelectorAll('.delete-button');
        const productsTableSection = document.getElementById('products-table-section');

        deleteButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const productId = this.getAttribute('data-id');
                if (confirm('Are you sure you want to delete this product?')) {
                    fetch(`delete_product.php?id=${productId}`, {
                        method: 'GET', // Change to 'POST' if implementing POST method
                        headers: {
                            'Content-Type': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Product deleted successfully.');
                            location.reload();
                        } else {
                            alert(data.message || 'Failed to delete product.');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while deleting the product.');
                    });
                }
            });
        });
    })();
</script>

<!-- Mobile Sidebar Toggle -->
<script>
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
</script>

<!-- Add Product Form Toggle -->
<script>
    (function() {
        const addProductButton = document.getElementById('add-product-button');
        const productsTableSection = document.getElementById('products-table-section');
        const addProductFormSection = document.getElementById('add-product-form-section');
        const cancelButton = document.getElementById('cancel-button');

        if (addProductButton && productsTableSection && addProductFormSection && cancelButton) {
            addProductButton.addEventListener('click', () => {
                productsTableSection.classList.add('hidden');
                addProductFormSection.classList.remove('hidden');
            });

            cancelButton.addEventListener('click', () => {
                addProductFormSection.classList.add('hidden');
                productsTableSection.classList.remove('hidden');
            });
        }
    })();
</script>

<!-- Edit Product Button Click Handler -->
<script>
    (function() {
        const editButtons = document.querySelectorAll('.edit-button');
        const editFormSection = document.getElementById('edit-product-form-section');
        const productsTableSection = document.getElementById('products-table-section');
        const cancelEditButton = document.getElementById('cancel-edit-button');

        if (editButtons.length && editFormSection && productsTableSection && cancelEditButton) {
            editButtons.forEach(button => {
                button.addEventListener('click', (e) => {
                    e.preventDefault();
                    const productId = button.getAttribute('data-id');
                    populateEditForm(productId);
                    productsTableSection.classList.add('hidden');
                    editFormSection.classList.remove('hidden');
                });
            });

            // Cancel Edit Button Handler
            cancelEditButton.addEventListener('click', () => {
                editFormSection.classList.add('hidden');
                productsTableSection.classList.remove('hidden');
            });
        }

        // Function to Fetch and Populate Edit Form Data
        function populateEditForm(productId) {
            fetch(`get_product.php?id=${productId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('edit-product-id').value = data.product.id;
                        document.getElementById('edit-name').value = data.product.name;
                        document.getElementById('edit-description').value = data.product.description;
                        document.getElementById('edit-original_price').value = data.product.original_price;
                        document.getElementById('edit-discounted_price').value = data.product.discounted_price;
                        document.getElementById('edit-category').value = data.product.category_id;
                        document.getElementById('current-image').src = `../${data.product.image_path}`;
                        document.getElementById('current-image-text').textContent = "Current Image:";
                    } else {
                        alert(data.message || 'Failed to fetch product details.');
                    }
                })
                .catch(error => {
                    console.error('Error fetching product details:', error);
                    alert('An error occurred while fetching product details.');
                });
        }
    })();
</script>

</body>

</html>