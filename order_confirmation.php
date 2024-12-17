<?php
session_start();
include 'db_connect.php';

if (!isset($_GET['order_id'])) {
    echo "Invalid order.";
    exit;
}

$order_id = intval($_GET['order_id']);

// Fetch order details and items
$query = "SELECT o.id, o.total_amount, o.order_date, o.status, 
                 oi.product_id, oi.quantity, oi.price, p.name 
          FROM orders o
          JOIN order_items oi ON o.id = oi.order_id
          JOIN products p ON oi.product_id = p.id
          WHERE o.id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Order not found.";
    exit;
}

$order = [];
$order_items = [];

// Fetch order and order items
while ($row = $result->fetch_assoc()) {
    if (empty($order)) {
        $order = [
            'id' => $row['id'],
            'total_amount' => $row['total_amount'],
            'order_date' => $row['order_date'],
            'status' => $row['status'],
        ];
    }
    $order_items[] = [
        'product_id' => $row['product_id'],
        'name' => $row['name'],
        'quantity' => $row['quantity'],
        'price' => $row['price'],
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="order-confirmation">
        <h1>Order Confirmation</h1>
        <p>Thank you for your purchase! Here are the details of your order:</p>

        <h2>Order Details</h2>
        <p><strong>Order ID:</strong> <?php echo htmlspecialchars($order['id']); ?></p>
        <p><strong>Total Amount:</strong> $<?php echo number_format($order['total_amount'], 2); ?></p>
        <p><strong>Order Date:</strong> <?php echo htmlspecialchars($order['order_date']); ?></p>
        <p><strong>Status:</strong> <?php echo htmlspecialchars($order['status']); ?></p>

        <h2>Order Items</h2>
        <table border="1" cellpadding="10" cellspacing="0">
            <thead>
                <tr>
                    <th>Product Name</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($order_items as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['name']); ?></td>
                        <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                        <td>$<?php echo number_format($item['price'], 2); ?></td>
                        <td>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <a href="index.php" class="btn">Continue Shopping</a>
    </div>
</body>
</html>