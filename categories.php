<?php
require_once 'config/database.php';
session_start();

function getJerseysByCategory($conn, $type, $categoryName = null) {
    $sql = "SELECT jerseys.*, teams.name AS team_name, categories.name AS category_name 
            FROM jerseys 
            JOIN teams ON jerseys.team_id = teams.id 
            JOIN categories ON teams.category_id = categories.id 
            WHERE categories.type = :type";

    $params = ['type' => $type];

    if ($categoryName !== null) {
        $sql .= " AND categories.name = :catName";
        $params['catName'] = $categoryName;
    }

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function filterJerseys($jerseys, $sortBy, $priceRange, $jerseyType) {
    if ($jerseyType !== 'all') {
        $jerseys = array_filter($jerseys, fn($j) => $j['jersey_type'] === $jerseyType);
    }

    $jerseys = array_filter($jerseys, function ($j) use ($priceRange) {
        return match ($priceRange) {
            '0-5000' => $j['price'] >= 0 && $j['price'] <= 5000,
            '5000-25000' => $j['price'] > 5000 && $j['price'] <= 25000,
            '25000-200000' => $j['price'] > 25000 && $j['price'] <= 200000,
            'all' => true,
            default => true,
        };
    });

    usort($jerseys, function ($a, $b) use ($sortBy) {
        return match ($sortBy) {
            'price_low' => $a['price'] <=> $b['price'],
            'price_high' => $b['price'] <=> $a['price'],
            'name_asc' => strcmp($a['name'], $b['name']),
            default => strtotime($b['created_at']) <=> strtotime($a['created_at']),
        };
    });

    return $jerseys;
}

// --- Input Handling ---
$type = $_GET['type'] ?? '';
$name = $_GET['name'] ?? '';
$sortBy = $_GET['sort'] ?? 'newest';
$priceRange = $_GET['price'] ?? 'all';
$jerseyType = $_GET['jersey_type'] ?? 'all';

$jerseys = [];
$title = '';
$subtitle = '';

if ($type === 'national') {
    if ($name) {
        $title = htmlspecialchars("$name National Team Jerseys");
        $subtitle = "Explore $name's national football jerseys";
        $jerseys = getJerseysByCategory($conn, 'national', $name);
    } else {
        $title = "National Team Jerseys";
        $subtitle = "Explore jerseys from national teams worldwide";
        $jerseys = getJerseysByCategory($conn, 'national');
    }
} elseif ($type === 'club') {
    if ($name) {
        $title = htmlspecialchars("$name Club Jerseys");
        $subtitle = "Explore jerseys from $name club teams";
        $jerseys = getJerseysByCategory($conn, 'club', $name);
    } else {
        $title = "Club Team Jerseys";
        $subtitle = "Explore jerseys from clubs around the world";
        $jerseys = getJerseysByCategory($conn, 'club');
    }
}


// Fetch images for each jersey and set the first image as jersey_url
foreach ($jerseys as &$jersey) {
    $stmt = $conn->prepare("SELECT image_path FROM jersey_images WHERE jersey_id = :jersey_id ORDER BY id ASC");
    $stmt->execute(['jersey_id' => $jersey['id']]);
    $images = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $jersey['jersey_url'] = $images[0] ?? $jersey['image_url'] ?? 'default.jpg';
}
unset($jersey);

$jerseys = filterJerseys($jerseys, $sortBy, $priceRange, $jerseyType);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?> - GetJerseys</title>
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
            <h1 class="display-5 fw-bold"><?= $title ?></h1>
            <p class="lead"><?= $subtitle ?></p>
        </div>
    </div>

    <!-- Products Section -->
    <section class="products-section py-5">
        <div class="container">
            <div class="row">
                <!-- Filters Sidebar -->
                <div class="col-lg-3">
                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title mb-4">Filters</h5>
                            <form action="" method="get" id="filterForm">
                              <input type="hidden" name="type" value="<?= htmlspecialchars($type) ?>">
<?php if ($name): ?>
<input type="hidden" name="name" value="<?= htmlspecialchars($name) ?>">
<?php endif; ?>


                                <div class="mb-4">
                                    <label class="form-label">Sort By</label>
                                    <select name="sort" class="form-select" onchange="this.form.submit()">
                                        <option value="newest" <?= $sortBy === 'newest' ? 'selected' : '' ?>>Newest First</option>
                                        <option value="price_low" <?= $sortBy === 'price_low' ? 'selected' : '' ?>>Price: Low to High</option>
                                        <option value="price_high" <?= $sortBy === 'price_high' ? 'selected' : '' ?>>Price: High to Low</option>
                                        <option value="name_asc" <?= $sortBy === 'name_asc' ? 'selected' : '' ?>>Name: A to Z</option>
                                    </select>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label">Price Range</label>
                                    <select name="price" class="form-select" onchange="this.form.submit()">
                                             <option value="all" <?= $priceRange === 'all' ? 'selected' : '' ?>>All</option>
                                <option value="0-5000" <?= $priceRange === '0-5000' ? 'selected' : '' ?>>$0 - $5000</option>
                                <option value="5000-25000" <?= $priceRange === '5000-25000' ? 'selected' : '' ?>>$5000 - $25000</option>
                                <option value="25000-200000" <?= $priceRange === '25000-200000' ? 'selected' : '' ?>>$25000 - $200000</option>
                           
                                    </select>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label">Jersey Type</label>
                                    <select name="jersey_type" class="form-select" onchange="this.form.submit()">
                                        <option value="all" <?= $jerseyType === 'all' ? 'selected' : '' ?>>All Types</option>
                                        <option value="home" <?= $jerseyType === 'home' ? 'selected' : '' ?>>Home</option>
                                        <option value="away" <?= $jerseyType === 'away' ? 'selected' : '' ?>>Away</option>
                                        <option value="third" <?= $jerseyType === 'third' ? 'selected' : '' ?>>Third</option>
                                    </select>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Products Grid -->
                <div class="col-lg-9">
                    <?php if (empty($jerseys)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-tshirt fa-4x text-muted mb-4"></i>
                        <h3>No jerseys found</h3>
                        <p class="text-muted">Try adjusting your filters or check back later for new arrivals.</p>
                    </div>
                    <?php else: ?>
                    <div class="row g-4">
                        <?php foreach ($jerseys as $jersey): ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="card jersey-card h-100">
                                <div class="card-img-container">
                                  <img src="assets/images/products/<?= htmlspecialchars($jersey['jersey_url']) ?>" class="card-img-top" alt="<?= htmlspecialchars($jersey['name']) ?>">

                                    <div class="jersey-type"><?= htmlspecialchars(ucfirst($jersey['jersey_type'])) ?></div>
                                    <div class="card-overlay">
                                        <a href="product.php?id=<?= $jersey['id'] ?>" class="btn btn-light btn-sm">Quick View</a>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <h5 class="card-title"><?= htmlspecialchars($jersey['name']) ?></h5><br>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="price" style="padding:10px;font-size:small">$<?= number_format($jersey['price'], 2) ?></span>
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