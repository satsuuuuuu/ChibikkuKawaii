<?php
session_start();
include 'db_connect.php'; // Include your database connection file

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if all required fields are set
    if (isset($_POST['username'], $_POST['password'], $_POST['csrf_token'])) {
        // Validate CSRF token
        if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            $error = "Invalid CSRF token.";
        } else {
            // Sanitize and validate input
            $username = htmlspecialchars(trim($_POST['username']));
            $password = htmlspecialchars(trim($_POST['password']));

            // Check if the username already exists
            $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $error = "Username already exists.";
            } else {
                // Hash the password for security
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // Insert the new user into the database
                $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
                if ($stmt) {
                    $stmt->bind_param("ss", $username, $hashed_password);
                    if ($stmt->execute()) {
                        // Registration successful
                        header("Location: index.php?showLogin=true"); // Redirect to index with a query parameter
                        exit();
                    } else {
                        $error = "Error: " . $stmt->error;
                    }
                } else {
                    $error = "Error preparing statement: " . $conn->error;
                }
            }
            $stmt->close();
        }
    } else {
        $error = "All fields are required.";
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Sign-Up</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .signup-container {
            width: 100%;
            max-width: 400px;
            background: #ffffff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .signup-container h2 {
            margin-bottom: 20px;
            color: #333;
        }

        .signup-container label {
            display: block;
            text-align: left;
            margin-bottom: 5px;
            color: #555;
            font-size: 14px;
        }

        .signup-container input[type="text"],
        .signup-container input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
            box-sizing: border-box;
        }

        .signup-container button {
            width: 100%;
            padding: 10px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .signup-container button:hover {
            background-color: #0056b3;
        }

        .signup-container .error {
            color: #ff4d4d;
            font-size: 14px;
            margin-top: 10px;
        }

        .signup-container a {
            display: block;
            margin-top: 15px;
            color: #007bff;
            text-decoration: none;
            font-size: 14px;
        }

        .signup-container a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="signup-container">
        <h2>User Sign-Up</h2>
        <form action="signup.php" method="POST">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" placeholder="Enter your username" required>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" placeholder="Enter your password" required>

            <!-- CSRF Token -->
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); ?>">

            <button type="submit">Sign Up</button>
        </form>
        <?php
        // Display error message if sign-up fails
        if (!empty($error)) {
            echo "<p class='error'>{$error}</p>";
        }
        ?>
        <a href="login.php">Already have an account? Log in here</a>
    </div>
</body>
</html>