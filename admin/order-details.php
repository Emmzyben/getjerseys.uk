<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
session_start();

if (!isAdminLoggedIn()) {
    header('Location: login.php');
    exit;
}

$orderId = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$orderId) {
    header('Location: orders.php');
    exit;
}

// Fetch order details
$orderStmt = $conn->prepare("SELECT * FROM orders WHERE id = ?");
$orderStmt->execute([$orderId]);
$order = $orderStmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    header('Location: orders.php?notfound=1');
    exit;
}

// Fetch order items with jersey details
$itemStmt = $conn->prepare("
    SELECT oi.*, j.name AS jersey_name 
    FROM order_items oi 
    JOIN jerseys j ON oi.product_id = j.id 
    WHERE oi.order_id = ?
");
$itemStmt->execute([$orderId]);
$items = $itemStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Details - GetJerseys Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>
<div class="admin-container">
    <?php include 'includes/sidebar.php'; ?>

    <main class="admin-content">
        <?php include 'includes/header.php'; ?>

        <div class="container-fluid py-4">
            <h1 class="h3 mb-4">Order #<?= $order['id'] ?> Details</h1>

            <div class="card shadow mb-4">
                <div class="card-body">
                    <h5>Customer Info</h5>
                    <p><strong>Name:</strong> <?= htmlspecialchars($order['customer_name']) ?></p>
                    <p><strong>Email:</strong> <?= htmlspecialchars($order['customer_email']) ?></p>
                    <p><strong>Phone:</strong> <?= htmlspecialchars($order['customer_phone']) ?></p>
                    <p><strong>Shipping Address:</strong> <?= nl2br(htmlspecialchars($order['shipping_address'])) ?></p>

                    <h5 class="mt-4">Order Info</h5>
                    <p><strong>Status:</strong> <?= ucfirst($order['status']) ?></p>
                    <p><strong>Order Date:</strong> <?= date('M d, Y h:i A', strtotime($order['created_at'])) ?></p>
                    <p><strong>Total Amount:</strong> $<?= number_format($order['total_amount'], 2) ?></p>

                    <h5 class="mt-4">Products</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Jersey Name</th>
                                    <th>Size</th>
                                    <th>Quantity</th>
                                    <th>Unit Price</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($items as $item): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($item['jersey_name']) ?></td>
                                        <td><?= htmlspecialchars($item['size']) ?></td>
                                        <td><?= $item['quantity'] ?></td>
                                        <td>$<?= number_format($item['price'], 2) ?></td>
                                        <td>$<?= number_format($item['quantity'] * $item['price'], 2) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <a href="orders.php" class="btn btn-secondary mt-3"><i class="fas fa-arrow-left"></i> Back to Orders</a>
                </div>
            </div>
        </div>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
