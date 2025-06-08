<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
session_start();

// Redirect if cart is empty
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header('Location: cart.php');
    exit;
}

$cartItems = getCartItems($conn);
$cartTotal = getCartTotal($cartItems);
$shippingCost = $cartTotal >= 100 ? 0 : 10;
$orderTotal = $cartTotal + $shippingCost;

foreach ($cartItems as &$item) {
    $productId = $item['product_id'];
    $stmt = $conn->prepare("SELECT image_path FROM jersey_images WHERE jersey_id = ? ORDER BY id ASC");
    $stmt->execute([$productId]);
    $images = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $images[] = $row['image_path'];
    }
    $item['all_images'] = $images;
    if (!empty($images)) {
        $item['image_url'] = $images[0];
    }
}
unset($item);

// Handle form submission
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate inputs
    if (empty($_POST['name'])) $errors['name'] = 'Name is required';
    if (empty($_POST['email']) || !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Valid email is required';
    if (empty($_POST['phone'])) $errors['phone'] = 'Phone number is required';
    if (empty($_POST['address'])) $errors['address'] = 'Shipping address is required';

    if (empty($errors)) {
        $customerData = [
            'name' => $_POST['name'],
            'email' => $_POST['email'],
            'phone' => $_POST['phone'],
            'address' => $_POST['address']
        ];
        $_SESSION['cartItems'] = $cartItems;

        // Get Flutterwave secret key
        $query = "SELECT secret FROM admins LIMIT 1"; 
        $result = $conn->query($query);
        if ($result && $result->rowCount() > 0) {
            $row = $result->fetch(PDO::FETCH_ASSOC);
            $secretKey = $row['secret']; 
        } else {
            echo "<script>alert('Secret key not found'); window.location.href='checkout.php';</script>";
            exit();
        }

        // Generate unique transaction reference
        $txRef = 'GETJERSEYS_' . time() . '_' . mt_rand(1000, 9999);

        $email = $_POST['email'];
        $amount = $orderTotal * 100; // Amount in Kobo (Flutterwave uses base currency unit)
        $currency = 'USD';

        $encodedCustomerData = base64_encode(json_encode($customerData));
        $callback_url = "http://localhost/getjerseys/callback.php?data=" . urlencode($encodedCustomerData);

        $payload = [
            "tx_ref" => $txRef,
            "amount" => $orderTotal,
            "currency" => $currency,
            "redirect_url" => $callback_url,
            "customer" => [
                "email" => $email,
                "phonenumber" => $_POST['phone'],
                "name" => $_POST['name']
            ],
            "customizations" => [
                "title" => "GetJerseys Purchase",
                "description" => "Payment for jerseys",
                "logo" => "assets/logo.png"
            ]
        ];

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => "https://api.flutterwave.com/v3/payments",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer $secretKey",
                "Content-Type: application/json"
            ],
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            echo "<script>alert('cURL Error: $err'); window.location.href='checkout.php';</script>";
            exit();
        }

        $result = json_decode($response, true);
        if (isset($result['status']) && $result['status'] === 'success') {
            $authUrl = $result['data']['link'];
            header("Location: $authUrl");
            exit();
        } else {
            echo "<script>alert('Failed to initiate payment.'); window.location.href='checkout.php';</script>";
            exit();
        }
    } else {
        $errors['order'] = 'Failed to create order. Please try again.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - GetJerseys</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
     <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <!-- Breadcrumb -->
    <div class="container mt-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item"><a href="cart.php">Shopping Cart</a></li>
                <li class="breadcrumb-item active" aria-current="page">Checkout</li>
            </ol>
        </nav>
    </div>

    <!-- Checkout Section -->
    <section class="checkout-section py-5">
        <div class="container">
            <h1 class="mb-4">Checkout</h1>
            
            <?php if (!empty($errors['order'])): ?>
            <div class="alert alert-danger mb-4">
                <?= $errors['order'] ?>
            </div>
            <?php endif; ?>
            
            <form method="post" action="">
                <div class="row">
                    <div class="col-lg-8">
                        <div class="card mb-4">
                            <div class="card-body">
                                <h5 class="card-title mb-4">Shipping Information</h5>
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="name" class="form-label">Full Name*</label>
                                        <input type="text" class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>" id="name" name="name" value="<?= $_POST['name'] ?? '' ?>" required>
                                        <?php if (isset($errors['name'])): ?>
                                        <div class="invalid-feedback"><?= $errors['name'] ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="email" class="form-label">Email Address*</label>
                                        <input type="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" id="email" name="email" value="<?= $_POST['email'] ?? '' ?>" required>
                                        <?php if (isset($errors['email'])): ?>
                                        <div class="invalid-feedback"><?= $errors['email'] ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="phone" class="form-label">Phone Number*</label>
                                    <input type="tel" class="form-control <?= isset($errors['phone']) ? 'is-invalid' : '' ?>" id="phone" name="phone" value="<?= $_POST['phone'] ?? '' ?>" required>
                                    <?php if (isset($errors['phone'])): ?>
                                    <div class="invalid-feedback"><?= $errors['phone'] ?></div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="address" class="form-label">Shipping Address*</label>
                                    <textarea class="form-control <?= isset($errors['address']) ? 'is-invalid' : '' ?>" id="address" name="address" rows="3" required><?= $_POST['address'] ?? '' ?></textarea>
                                    <?php if (isset($errors['address'])): ?>
                                    <div class="invalid-feedback"><?= $errors['address'] ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        
                    </div>
                    
                    <div class="col-lg-4">
                        <div class="card order-summary mb-4">
                            <div class="card-body">
                                <h5 class="card-title mb-4">Order Summary</h5>
                                
                                <div class="order-items mb-4">
                                    <?php foreach ($cartItems as $item): ?>
                                    <div class="order-item d-flex mb-3">
                                        <img src="assets/images/products/<?= htmlspecialchars($item['image_url']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="order-item-image me-3">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-0"><?= htmlspecialchars($item['name']) ?></h6>
                                            <div class="text-muted small">
                                                <span>Size: <?= htmlspecialchars($item['size']) ?></span> | 
                                                <span>Qty: <?= $item['quantity'] ?></span>
                                            </div>
                                            <div class="item-price">$<?= number_format($item['subtotal'], 2) ?></div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                
                                <hr>
                                
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Subtotal</span>
                                    <span>$<?= number_format($cartTotal, 2) ?></span>
                                </div>
                                
                                <div class="d-flex justify-content-between mb-3">
                                    <span>Shipping</span>
                                    <span><?= $shippingCost > 0 ? '$' . number_format($shippingCost, 2) : 'Free' ?></span>
                                </div>
                                
                                <hr>
                                
                                <div class="d-flex justify-content-between mb-4">
                                    <strong>Total</strong>
                                    <strong>$<?= number_format($orderTotal, 2) ?></strong>
                                </div>
                                
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        Place Order
                                    </button>
                                </div>
                                
                                <div class="text-center mt-3">
                                    <small>By placing an order, you agree to our <a href="terms-of-service.php">Terms of Service</a> and <a href="privacy-policy.php">Privacy Policy</a>.</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Payment method selection
            const paymentMethods = document.querySelectorAll('input[name="payment_method"]');
            const codDetails = document.getElementById('cod-details');
            const bankTransferDetails = document.getElementById('bank-transfer-details');
            
            paymentMethods.forEach(method => {
                method.addEventListener('change', function() {
                    if (this.value === 'cod') {
                        codDetails.classList.remove('d-none');
                        bankTransferDetails.classList.add('d-none');
                    } else if (this.value === 'bank_transfer') {
                        codDetails.classList.add('d-none');
                        bankTransferDetails.classList.remove('d-none');
                    }
                });
            });
        });
    </script>
      <button id="scrollToTopBtn" onclick="scrollToTop()">↑</button>
    <a href="https://wa.me/447341157876" target="_blank" id="whatsapp-icon-container">
      <img src="https://upload.wikimedia.org/wikipedia/commons/6/6b/WhatsApp.svg" alt="WhatsApp" />
      Chat with us
    </a>
    <script src="assets/js/main.js"></script>
 <script src="script.js"></script>
</body>
</html>