<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
session_start();

$productId = isset($_GET['id']) ? intval($_GET['id']) : 0;
$jersey = getJerseyById($conn, $productId);

// Fetch jersey images from jersey_images table
$jerseyImages = [];
if ($jersey) {
    $stmt = $conn->prepare("SELECT image_path FROM jersey_images WHERE jersey_id = :jersey_id");
    $stmt->bindParam(':jersey_id', $productId, PDO::PARAM_INT);
    $stmt->execute();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $jerseyImages[] = $row['image_path'];
    }
}


// If product doesn't exist, redirect to home
if (!$jersey) {
    header('Location: index.php');
    exit;
}

// Handle add to cart action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
    $size = isset($_POST['size']) ? $_POST['size'] : '';
    
    if ($quantity > 0 && !empty($size)) {
        addToCart($productId, $quantity, $size);
        header('Location: cart.php');
        exit;
    }
}

// Get related jerseys (same team, different types)
$relatedJerseys = getJerseysByTeam($conn, $jersey['team_id']);
foreach ($relatedJerseys as &$related) {
    $stmt = $conn->prepare("SELECT image_path FROM jersey_images WHERE jersey_id = :jersey_id LIMIT 1");
    $stmt->bindParam(':jersey_id', $related['id'], PDO::PARAM_INT);
    $stmt->execute();
    $imgRow = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($imgRow && !empty($imgRow['image_path'])) {
        $related['image_url'] = 'assets/images/products/' . $imgRow['image_path'];
    } else {
        $related['image_url'] = 'assets/images/products/' . htmlspecialchars($related['image_path']);
    }
}
unset($related);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($jersey['name']) ?> - GetJerseys</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <!-- Breadcrumb -->
    <div class="container mt-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php"  style="color:red">Home</a></li>
                <li class="breadcrumb-item">
                    <a href="categories.php?type=<?= $jersey['category_type'] ?>" style="color:red">
                        <?= $jersey['category_type'] === 'national' ? 'National Teams' : 'Club Teams' ?>
                    </a>
                </li>
                <?php if (isset($jersey['category_id'], $jersey['category_name'])): ?>
                <li class="breadcrumb-item">
                    <a href="teams.php?category=<?= htmlspecialchars($jersey['category_id']) ?>">
                        <?= htmlspecialchars($jersey['category_name']) ?>
                    </a>
                </li>
                <?php endif; ?>
                <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($jersey['name']) ?></li>
            </ol>
        </nav>
    </div>

    <!-- Product Details -->
    <section class="product-details py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 mb-4">
                    <div class="product-image-container">
                       <?php if (!empty($jerseyImages)): ?>
<img src="assets/images/products/<?= htmlspecialchars($jerseyImages[0]) ?>" class="img-fluid product-image" alt="<?= htmlspecialchars($jersey['name']) ?>">
<?php else: ?>
<img src="assets/images/products/<?= htmlspecialchars($jersey['image_path']) ?>" class="img-fluid product-image" alt="<?= htmlspecialchars($jersey['name']) ?>">
<?php endif; ?>

                        <div class="jersey-type-badge"><?= htmlspecialchars(ucfirst($jersey['jersey_type'])) ?></div>
                    </div>
                    <div class="row mt-3">
                       <?php if (!empty($jerseyImages)): ?>
<div class="row mt-3">
    <?php foreach ($jerseyImages as $imagePath): ?>
    <div class="col-4">
        <img src="assets/images/products/<?= htmlspecialchars($imagePath) ?>" class="img-fluid thumbnail" alt="Jersey Image">
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

                    </div>
                </div>
                <div class="col-lg-6">
                    <h1 class="product-title"><?= htmlspecialchars($jersey['name']) ?></h1>
                    <p class="team-name"><?= htmlspecialchars($jersey['team_name']) ?></p>
                    <div class="price-container my-3">
                        <span class="price">₦<?= number_format($jersey['price'], 2) ?></span>
                    </div>
                 
                    <div class="description mb-4">
                        <p><?= nl2br(htmlspecialchars($jersey['description'] ?? 'Official ' . $jersey['team_name'] . ' ' . $jersey['jersey_type'] . ' jersey for the current season. Made with high-quality materials for comfort and durability.')) ?></p>
                    </div>
                    <form method="post" action="">
                        <div class="mb-3">
                            <label class="form-label">Size</label>
                            <div class="size-selector">
                                <?php
                                $sizes = explode(',', $jersey['sizes'] ?? 'S,M,L,XL,XXL');
                                foreach ($sizes as $size):
                                ?>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="size" id="size-<?= $size ?>" value="<?= $size ?>" required>
                                    <label class="form-check-label" for="size-<?= $size ?>"><?= $size ?></label>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <label for="quantity" class="form-label">Quantity</label>
                                <div class="input-group">
                                    <button type="button" class="btn btn-outline-secondary" id="decrease-qty">-</button>
                                    <input type="number" class="form-control text-center" id="quantity" name="quantity" value="1" min="1" max="10">
                                    <button type="button" class="btn btn-outline-secondary" id="increase-qty">+</button>
                                </div>
                            </div>
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" name="add_to_cart" class="btn btn-primary btn-lg">
                                <i class="fas fa-shopping-cart me-2"></i> Add to Cart
                            </button>
                          
                        </div>
                    </form>
                    <div class="shipping-info mt-4">
                     
                        <div class="d-flex align-items-center">
                            <i class="fas fa-shield-alt me-2"></i>
                            <span>Secure checkout</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Product Details Tabs -->
    <section class="product-tabs py-5 bg-light">
        <div class="container">
            <ul class="nav nav-tabs" id="productTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="description-tab" data-bs-toggle="tab" data-bs-target="#description" type="button" role="tab">Description</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="details-tab" data-bs-toggle="tab" data-bs-target="#details" type="button" role="tab">Product Details</button>
                </li>
              
            </ul>
            <div class="tab-content p-4 bg-white shadow-sm" id="productTabsContent">
                <div class="tab-pane fade show active" id="description" role="tabpanel">
                    <p><?= nl2br(htmlspecialchars($jersey['description'] ?? 'Official ' . $jersey['team_name'] . ' ' . $jersey['jersey_type'] . ' jersey for the current season. Made with high-quality materials for comfort and durability. Features the team\'s iconic colors and design, with breathable fabric that keeps you cool and comfortable. The jersey includes the team crest and sponsor logos, all accurately represented as in the professional version worn by the players.')) ?></p>
                </div>
                <div class="tab-pane fade" id="details" role="tabpanel">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Team:</span>
                            <span class="text-muted"><?= htmlspecialchars($jersey['team_name']) ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Jersey Type:</span>
                            <span class="text-muted"><?= htmlspecialchars(ucfirst($jersey['jersey_type'])) ?></span>
                        </li>
                       
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Material:</span>
                            <span class="text-muted">100% Polyester</span>
                        </li>
                       
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Available Sizes:</span>
                            <span class="text-muted"><?= htmlspecialchars($jersey['sizes'] ?? 'S, M, L, XL, XXL') ?></span>
                        </li>
                    </ul>
                </div>
             
            </div>
        </div>
    </section>

    <!-- Related Products -->
    <section class="related-products py-5">
        <div class="container">
            <h2 class="section-title mb-4">Related Products</h2>
            <div class="row">
                <?php 
                $count = 0;
                foreach ($relatedJerseys as $related):
                    if ($related['id'] != $jersey['id'] && $count < 4):
                        $count++;
                ?>
                <div class="col-md-6 col-lg-3 mb-4">
                    <div class="card jersey-card h-100">
                        <div class="card-img-container">
                            <img src="<?= htmlspecialchars($related['image_url']) ?>" class="card-img-top" alt="<?= htmlspecialchars($related['name']) ?>">
                            <div class="jersey-type"><?= htmlspecialchars(ucfirst($related['jersey_type'])) ?></div>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($related['name']) ?></h5>
                            <p class="team-name"><?= htmlspecialchars($jersey['team_name']) ?></p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="price" style="font-size: medium;">₦<?= number_format($related['price'], 2) ?></span>
                                <a href="product.php?id=<?= $related['id'] ?>" class="btn btn-outline-primary btn-sm" >View Details</a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php 
                    endif;
                endforeach; 
                ?>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Quantity buttons
            const quantityInput = document.getElementById('quantity');
            const decreaseBtn = document.getElementById('decrease-qty');
            const increaseBtn = document.getElementById('increase-qty');
            
            decreaseBtn.addEventListener('click', function() {
                const currentValue = parseInt(quantityInput.value);
                if (currentValue > 1) {
                    quantityInput.value = currentValue - 1;
                }
            });
            
            increaseBtn.addEventListener('click', function() {
                const currentValue = parseInt(quantityInput.value);
                if (currentValue < 10) {
                    quantityInput.value = currentValue + 1;
                }
            });
            
            // Image thumbnails
            const thumbnails = document.querySelectorAll('.thumbnail');
            const mainImage = document.querySelector('.product-image');
            
            thumbnails.forEach(thumb => {
                thumb.addEventListener('click', function() {
                    mainImage.src = this.src;
                });
            });
        });
    </script>
  <button id="scrollToTopBtn" onclick="scrollToTop()">↑</button>
    <a href="https://wa.me/447341157876" target="_blank" id="whatsapp-icon-container">
      <img src="https://upload.wikimedia.org/wikipedia/commons/6/6b/WhatsApp.svg" alt="WhatsApp" />
      Chat with us
    </a>
     <script src="script.js"></script>
</body>
</html>