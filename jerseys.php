<?php
require_once 'config/database.php';
session_start();

// Fetch all images for a jersey
function getJerseyImages(PDO $conn, $jerseyId)
{
    $stmt = $conn->prepare("SELECT image_path FROM jersey_images WHERE jersey_id = :jersey_id ORDER BY id ASC");
    $stmt->bindParam(':jersey_id', $jerseyId, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

// Jersey filter function using PDO
function getJerseysByType(PDO $conn, $type = '')
{
    if ($type !== '') {
        $stmt = $conn->prepare("SELECT * FROM jerseys WHERE jersey_type = :type");
        $stmt->bindParam(':type', $type, PDO::PARAM_STR);
    } else {
        $stmt = $conn->prepare("SELECT * FROM jerseys");
    }

    $stmt->execute();
    $jerseys = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // For each jersey, fetch images and set image_url to the first image (if any)
    foreach ($jerseys as &$jersey) {
        $images = getJerseyImages($conn, $jersey['id']);
        $jersey['images'] = $images;
        $jersey['image_url'] = !empty($images) ? $images[0] : 'default.jpg';
    }
    unset($jersey);

    return $jerseys;
}

// Get jersey type from query string
$type = $_GET['type'] ?? '';
$jerseys = getJerseysByType($conn, $type);

$typeNames = [
    'home' => 'Home',
    'away' => 'Away',
    'third' => 'Third Kit',
    'goalkeeper' => 'Goalkeeper',
    'kiddies'=> 'Kiddies',
    'training' => 'Training',
];

$title = isset($typeNames[$type]) ? $typeNames[$type] . ' Jerseys' : 'All Jerseys';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?> - GetJerseys</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
     <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include 'includes/header.php'; ?>

<!-- Category Header -->
<style>
    .category-header {
        position: relative;
        background: linear-gradient(rgba(0, 0, 0, 0.68), rgba(0, 0, 0, 0.96)), url('assets/images/icons/barca_home.jpg') center center/contain repeat;
        color: #fff;
    }
    .category-header .container {
        position: relative;
        z-index: 2;
    }
    .category-header::before {
        content: "";
        display: block;
        position: absolute;
        inset: 0;
        background: inherit;
        opacity: 1;
        z-index: 1;
    }
</style>
<div class="category-header py-5 text-white">
    <div class="container">
        <h1 class="display-5 fw-bold"><?= htmlspecialchars($title) ?></h1>
        <p class="lead">Browse our collection of <?= strtolower(htmlspecialchars($title)) ?> from teams worldwide</p>
    </div>
</div>

<!-- Products Section -->
<section class="products-section py-5">
    <div class="container">
        <div class="row">
            <!-- Sidebar Filter -->
            <div class="col-lg-3">
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Jersey Type</h5>
                        <form action="" method="get" id="typeFilterForm">
                            <select name="type" class="form-select" onchange="this.form.submit()">
                                <option value="" <?= $type === '' ? 'selected' : '' ?>>All Types</option>
                                <option value="home" <?= $type === 'home' ? 'selected' : '' ?>>Home</option>
                                <option value="away" <?= $type === 'away' ? 'selected' : '' ?>>Away</option>
                                <option value="third" <?= $type === 'third' ? 'selected' : '' ?>>Third Kit</option>
                                <option value="goalkeeper" <?= $type === 'goalkeeper' ? 'selected' : '' ?>>Goalkeeper</option>
                            </select>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Product Grid -->
            <div class="col-lg-9">
                <?php if (empty($jerseys)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-tshirt fa-4x text-muted mb-4"></i>
                        <h3>No jerseys found</h3>
                        <p class="text-muted">Try selecting a different jersey type or check back later.</p>
                    </div>
                <?php else: ?>
                    <div class="row g-4">
                        <?php foreach ($jerseys as $jersey): ?>
                            <div class="col-md-6 col-lg-4">
                                <div class="card jersey-card h-100">
                                    <div class="card-img-container">
                                        <img src="assets/images/products/<?= htmlspecialchars($jersey['image_url']) ?>" class="card-img-top" alt="<?= htmlspecialchars($jersey['name']) ?>">
                                        <div class="jersey-type"><?= ucfirst(htmlspecialchars($jersey['jersey_type'])) ?></div>
                                        <div class="card-overlay">
                                            <a href="product.php?id=<?= $jersey['id'] ?>" class="btn btn-light btn-sm">Quick View</a>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <h5 class="card-title"><?= htmlspecialchars($jersey['name']) ?></h5> 
                                        <br>               
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="price" style="font-size:medium">$<?= number_format($jersey['price'], 2) ?></span>
                                            <a href="product.php?id=<?= $jersey['id'] ?>" class="btn btn-primary btn-sm">View Details</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
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
