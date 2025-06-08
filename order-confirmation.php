<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'send_email.php';
session_start();

if (!isset($_GET['order_id']) || empty($_GET['order_id'])) {
    header('Location: index.php');
    exit;
}

$orderId = $_GET['order_id'];

// Fetch order details
$stmt = $conn->prepare("
    SELECT o.id, o.customer_name, o.customer_email, o.customer_phone, o.shipping_address, o.total_amount, o.created_at 
    FROM orders o 
    WHERE o.id = ?
");
$stmt->execute([$orderId]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    echo "<p>Order not found.</p>";
    exit;
}

// Fetch ordered items
$stmt = $conn->prepare("
    SELECT oi.product_id, oi.quantity, oi.price, p.name 
    FROM order_items oi
    JOIN jerseys p ON oi.product_id = p.id
    WHERE oi.order_id = ?
");
$stmt->execute([$orderId]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch first image for each item
foreach ($items as &$item) {
    $stmt = $conn->prepare("SELECT image_path FROM jersey_images WHERE jersey_id = ? ORDER BY id ASC LIMIT 1");
    $stmt->execute([$item['product_id']]);
    $image = $stmt->fetch(PDO::FETCH_ASSOC);
    $item['image'] = $image ? $image['image_path'] : 'no-image.png';
}
unset($item);

// Email recipients
$customer_email = $order['customer_email'];
$website_admin = "getjerseys2025@gmail.com";
$website_developer = "emmco96@gmail.com";

// Generate HTML list of items
function buildItemList($items) {
    $output = "<ul>";
    foreach ($items as $item) {
        $output .= "<li><strong>{$item['name']}</strong> - Quantity: {$item['quantity']} | Price: ₦" . number_format($item['price'], 2) . "</li>";
    }
    $output .= "</ul>";
    return $output;
}

$itemListHTML = buildItemList($items);

// Define custom messages
$messages = [
    $customer_email => [
        'subject' => "Your Order Confirmation - Order #{$order['id']}",
        'body' => "
            <p>Dear {$order['customer_name']},</p>
            <p>Thank you for your order! Here are the details:</p>
            <p><strong>Shipping Address:</strong> {$order['shipping_address']}</p>
            <p><strong>Order Total:</strong> ₦" . number_format($order['total_amount'], 2) . "</p>
            <p><strong>Items:</strong></p>
            $itemListHTML
            <p>We will notify you once your order ships.</p>
            <p>Thanks for choosing Get Jerseys!</p>
        "
    ],
    $website_admin => [
        'subject' => "New Customer Order - #{$order['id']}",
        'body' => "
            <p><strong>Customer Name:</strong> {$order['customer_name']}</p>
            <p><strong>Email:</strong> {$order['customer_email']}</p>
            <p><strong>Phone:</strong> {$order['customer_phone']}</p>
            <p><strong>Shipping Address:</strong> {$order['shipping_address']}</p>
            <p><strong>Total Amount:</strong> ₦" . number_format($order['total_amount'], 2) . "</p>
            <p><strong>Items Ordered:</strong></p>
            $itemListHTML
        "
    ],
    $website_developer => [
        'subject' => "Debug: Order Received - #{$order['id']}",
        'body' => "
            <p>This is a developer alert for order ID: {$order['id']}.</p>
            <p>Placed by: {$order['customer_name']} ({$order['customer_email']})</p>
            <p>Total: ₦" . number_format($order['total_amount'], 2) . "</p>
            <p>Shipping: {$order['shipping_address']}</p>
            <p>Ordered Items:</p>
            $itemListHTML
        "
    ]
];

// Send all emails
foreach ($messages as $recipient => $data) {
    $sendSuccess = sendEmail($data['subject'], $recipient, $data['body'], $data['subject']);
    if (!$sendSuccess) {
        error_log("Failed to send email to $recipient");
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Confirmation</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="style.css">

    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            padding-top: 40px;
        }

        h1 {
            color: #0d6efd;
        }

        .card {
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        .order-items table {
            margin-top: 15px;
        }

        .thank-you {
            font-size: 1.2rem;
            font-weight: 500;
            color: #28a745;
        }

        .order-summary th, .order-summary td {
            vertical-align: middle;
        }

        .order-summary th {
            background-color: #f1f1f1;
        }

        .btn-primary {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container">
        <div class="card p-4">
            <h1 class="mb-3">Order Confirmation</h1>
            <p class="thank-you">Thank you for your order! Your order ID is <strong>#<?= htmlspecialchars($order['id']) ?></strong>.</p>
            <p class="mb-4">We have received your order and will process it shortly. You will receive an email confirmation with the details.</p>

            <div class="mb-4">
                <h4>Customer Information</h4>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item"><strong>Name:</strong> <?= htmlspecialchars($order['customer_name']) ?></li>
                    <li class="list-group-item"><strong>Email:</strong> <?= htmlspecialchars($order['customer_email']) ?></li>
                    <li class="list-group-item"><strong>Phone:</strong> <?= htmlspecialchars($order['customer_phone']) ?></li>
                    <li class="list-group-item"><strong>Shipping Address:</strong><br><?= nl2br(htmlspecialchars($order['shipping_address'])) ?></li>
                    <li class="list-group-item"><strong>Total Amount:</strong> $<?= number_format($order['total_amount'], 2) ?></li>
                    <li class="list-group-item"><strong>Order Date:</strong> <?= date('F j, Y, g:i a', strtotime($order['created_at'])) ?></li>
                </ul>
            </div>

            <div>
                <h4>Ordered Items</h4>
                <div class="table-responsive">
                    <table class="table table-bordered order-summary">
                        <thead class="table-light">
                            <tr>
                                <th>Product</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                                <tr>
                                    <td><?= htmlspecialchars($item['name']) ?></td>
                                    <td>$<?= number_format($item['price'], 2) ?></td>
                                    <td><?= $item['quantity'] ?></td>
                                    <td>$<?= number_format($item['price'] * $item['quantity'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <a href="index.php" class="btn btn-primary"><i class="fas fa-arrow-left me-2"></i>Continue Shopping</a>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

