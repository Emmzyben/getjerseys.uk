<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
session_start();

// Handle remove item action
if (isset($_GET['remove']) && isset($_SESSION['cart'])) {
    $itemKey = $_GET['remove'];
    if (isset($_SESSION['cart'][$itemKey])) {
        unset($_SESSION['cart'][$itemKey]);
    }
    header('Location: cart.php');
    exit;
}

// Handle update quantity action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_cart'])) {
    foreach ($_POST['quantity'] as $itemKey => $quantity) {
        if (isset($_SESSION['cart'][$itemKey])) {
            $_SESSION['cart'][$itemKey]['quantity'] = max(1, intval($quantity));
        }
    }
    header('Location: cart.php');
    exit;
}

// Get cart items with details
$cartItems = getCartItems($conn);
$cartTotal = getCartTotal($cartItems);
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - GetJerseys</title>
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
                <li class="breadcrumb-item active" aria-current="page">Shopping Cart</li>
            </ol>
        </nav>
    </div>

    <!-- Cart Section -->
    <section class="cart-section py-5">
        <div class="container">
            <h1 class="mb-4">Shopping Cart</h1>
            
            <?php if (empty($cartItems)): ?>
            <div class="empty-cart text-center py-5">
                <i class="fas fa-shopping-cart fa-4x mb-4 text-muted"></i>
                <h3>Your cart is empty</h3>
                <p class="mb-4">Looks like you haven't added any items to your cart yet.</p>
                <a href="index.php" class="btn btn-primary">Continue Shopping</a>
            </div>
            <?php else: ?>
            
            <form method="post" action="">
                <div class="row">
                    <div class="col-lg-8">
                        <div class="card mb-4">
                            <div class="card-body">
                                <table class="table cart-table">
                                    <thead>
                                        <tr>
                                            <th>Product</th>
                                            <th>Price</th>
                                            <th>Size</th>
                                            <th>Quantity</th>
                                            <th>Subtotal</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($cartItems as $key => $item): 
                                            $itemKey = $item['product_id'] . '-' . $item['size'];
                                        ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="assets/images/products/<?= htmlspecialchars($item['image_url']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="cart-item-image me-3">
                                                    <div>
                                                        <h6 class="mb-0"><?= htmlspecialchars($item['name']) ?></h6>
                                                        <small class="text-muted"><?= htmlspecialchars($item['team_name']) ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>$<?= number_format($item['price'], 2) ?></td>
                                            <td><?= htmlspecialchars($item['size']) ?></td>
                                            <td>
                                                <div class="quantity-selector">
                                                    <input type="number" class="form-control form-control-sm" name="quantity[<?= $itemKey ?>]" value="<?= $item['quantity'] ?>" min="1" max="10">
                                                </div>
                                            </td>
                                            <td>$<?= number_format($item['subtotal'], 2) ?></td>
                                            <td>
                                                <a href="cart.php?remove=<?= $itemKey ?>" class="text-danger remove-item">
                                                    <i class="fas fa-times"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between mb-4">
                            <a href="index.php" class="btn btn-outline-primary">
                                <i class="fas fa-arrow-left me-2"></i> Continue Shopping
                            </a>
                            <button type="submit" name="update_cart" class="btn btn-secondary">
                                <i class="fas fa-sync-alt me-2"></i> Update Cart
                            </button>
                        </div>
                    </div>
                    
                    <div class="col-lg-4">
                        <div class="card cart-summary">
                            <div class="card-body">
                                <h5 class="card-title mb-4">Order Summary</h5>
                                
                                <div class="d-flex justify-content-between mb-3">
                                    <span>Subtotal</span>
                                    <span>$<?= number_format($cartTotal, 2) ?></span>
                                </div>
                                
                                <div class="d-flex justify-content-between mb-3">
                                    <span>Shipping</span>
                                    <span><?= $cartTotal >= 100 ? 'Free' : '$10.00' ?></span>
                                </div>
                                
                                <?php if ($cartTotal < 100): ?>
                                <div class="free-shipping-message mb-3">
                                    <small>Add $<?= number_format(100 - $cartTotal, 2) ?> more to get FREE shipping!</small>
                                    <div class="progress mt-2" style="height: 5px;">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: <?= min(100, ($cartTotal / 100) * 100) ?>%"></div>
                                    </div>
                                </div>
                                <?php endif; ?>
                                
                                <hr>
                                
                                <div class="d-flex justify-content-between mb-4">
                                    <strong>Total</strong>
                                    <strong>$<?= number_format($cartTotal < 100 ? $cartTotal + 10 : $cartTotal, 2) ?></strong>
                                </div>
                                
                                <div class="d-grid">
                                    <a href="checkout.php" class="btn btn-primary btn-lg">
                                        Proceed to Checkout
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
            <?php endif; ?>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
     <script src="assets/js/main.js"></script>
      <button id="scrollToTopBtn" onclick="scrollToTop()">↑</button>
    <a href="https://wa.me/447341157876" target="_blank" id="whatsapp-icon-container">
      <img src="https://upload.wikimedia.org/wikipedia/commons/6/6b/WhatsApp.svg" alt="WhatsApp" />
      Chat with us
    </a>
     <script src="script.js"></script>
</body>
</html>