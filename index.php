<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
session_start();

// Get latest jerseys for homepage
$latestJerseys = getLatestJerseys($conn, 8);

// Fetch the first image for each jersey from jersey_images table
foreach ($latestJerseys as &$jersey) {
    $jerseyId = $jersey['id'];
    $stmt = $conn->prepare("SELECT image_path FROM jersey_images WHERE jersey_id = ? ORDER BY id ASC LIMIT 1");
    $stmt->execute([$jerseyId]);
    $image = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($image && isset($image['image_path'])) {
        $jersey['image_path'] = $image['image_path'];
    } else {
        // Fallback if no image found
        $jersey['image_path'] = 'default.jpg';
    }
}
unset($jersey);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GetJerseys - Premium Football Jerseys</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
     <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <!-- Hero Section -->
    <div class="hero-section position-relative overflow-hidden">
        <div class="hero-slider">
            <?php foreach ($latestJerseys as $index => $jersey): ?>
                <div class="hero-slide<?= $index === 0 ? ' active' : '' ?>" style="background-image: url('./assets/images/products/<?= htmlspecialchars($jersey['image_path']) ?>');"></div>
            <?php endforeach; ?>
        </div>
        <div class="container position-relative" style="z-index: 2;">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1 class="display-4 text-white fw-bold">Premium Quality Football Jerseys</h1>
                    <p class="lead text-white">Get authentic jerseys from your favorite national teams and clubs worldwide.</p>
                    <div class="d-flex gap-3 mt-4">
                        <a href="categories.php?type=national" class="btn btn-primary btn-lg">National Teams</a>
                        <a href="categories.php?type=club" class="btn btn-outline-light btn-lg">Club Teams</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="hero-overlay"></div>
    </div>

    <!-- Latest Jerseys Section -->
    <section class="latest-jerseys py-5">
        <div class="container">
            <h2 class="section-title mb-4">Latest Arrivals</h2>
            <div class="row">
                <?php foreach ($latestJerseys as $jersey): ?>
                <div class="col-md-6 col-lg-3 mb-4">
                    <div class="card jersey-card h-100">
                        <div class="card-img-container">
                            <img src="./assets/images/products/<?= htmlspecialchars($jersey['image_path']) ?>" class="card-img-top" alt="<?= htmlspecialchars($jersey['name']) ?>">
                            <div class="jersey-type"><?= htmlspecialchars(ucfirst($jersey['jersey_type'])) ?></div>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($jersey['name']) ?></h5>
                            <p class="team-name"><?= htmlspecialchars($jersey['team_name']) ?></p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="price" style="padding:10px;font-size:small">$<?= number_format($jersey['price'], 2) ?></span>
                                <a href="product.php?id=<?= $jersey['id'] ?>" class="btn btn-outline-primary" style="padding:10px;font-size:small">View Details</a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="text-center mt-4">
                <a href="all-jerseys.php" class="btn btn-primary">View All Jerseys</a>
            </div>
        </div>
    </section>

    <!-- Categories Section -->
    <section class="categories-section py-5 bg-light">
        <div class="container">
            <h2 class="section-title mb-4">Browse by Category</h2>
            <div class="row row-cols-1 row-cols-md-2 g-4">
                <div class="col">
                    <a href="categories.php?type=national" class="category-card modern-card text-decoration-none d-flex flex-column align-items-center justify-content-center p-4">
                        <img src="assets/images/icons/germany.jpg" alt="National Teams" class="mb-3 category-icon" />
                        <h3>National Teams</h3>
                        <p class="text-center">Jerseys from national teams across all continents</p>
                    </a>
                </div>
                <div class="col">
                    <a href="categories.php?type=club" class="category-card modern-card text-decoration-none d-flex flex-column align-items-center justify-content-center p-4">
                        <img src="assets/images/icons/chelsea.webp" alt="Club Teams" class="mb-3 category-icon" />
                        <h3>Club Teams</h3>
                        <p class="text-center">Jerseys from top clubs in the best leagues worldwide</p>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Jersey Types Section -->
    <section class="jersey-types py-5">
        <div class="container">
            <h2 class="section-title mb-4">Jersey Types</h2>
            <div class="row row-cols-1 row-cols-md-3 g-4">
                <div class="col">
                    <a href="jerseys.php?type=home" class="type-card modern-card home text-decoration-none d-flex flex-column align-items-center justify-content-center p-4">
                        <img src="assets/images/icons/barca_home.jpg" alt="Home Jerseys" class="mb-3 type-icon" />
                        <h3>Home Jerseys</h3>
                    </a>
                </div>
                <div class="col">
                    <a href="jerseys.php?type=away" class="type-card modern-card away text-decoration-none d-flex flex-column align-items-center justify-content-center p-4">
                        <img src="assets/images/icons/barca_away.jpg" alt="Away Jerseys" class="mb-3 type-icon" />
                        <h3>Away Jerseys</h3>
                    </a>
                </div>
                <div class="col">
                    <a href="jerseys.php?type=third" class="type-card modern-card third text-decoration-none d-flex flex-column align-items-center justify-content-center p-4">
                        <img src="assets/images/icons/barca_third.jpg" alt="Third Kits" class="mb-3 type-icon" />
                        <h3>Third Kits</h3>
                    </a>
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