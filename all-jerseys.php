<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
session_start();

// Get all jerseys with join (to include team and category type)
function getAllJerseysWithTeams($conn) {
    $sql = "
        SELECT j.*, t.name AS team_name, t.category_type, t.category_id
        FROM jerseys j
        JOIN teams t ON j.team_id = t.id
    ";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Filter jerseys function
function filterJerseys(
    $jerseys,
    $sortBy = null,
    $priceRange = null,
    $jerseyType = null,
    $teamId = null,
    $categoryType = null
) {
    // Filter by price range
    if ($priceRange && is_array($priceRange) && count($priceRange) === 2) {
        [$min, $max] = $priceRange;
        $jerseys = array_filter($jerseys, fn($j) => $j['price'] >= $min && $j['price'] <= $max);
    }

    // Filter by jersey type
    if ($jerseyType && $jerseyType !== 'all') {
        $jerseys = array_filter($jerseys, fn($j) => strtolower($j['jersey_type']) === strtolower($jerseyType));
    }

    // Filter by team
    if ($teamId && $teamId !== 'all') {
        $jerseys = array_filter($jerseys, fn($j) => $j['team_id'] == $teamId);
    }

    // Filter by category type (club or national)
    if ($categoryType && $categoryType !== 'all') {
        $jerseys = array_filter($jerseys, fn($j) => strtolower($j['category_type']) === strtolower($categoryType));
    }

    // Sorting
    if ($sortBy) {
        usort($jerseys, function($a, $b) use ($sortBy) {
            switch ($sortBy) {
                case 'price_asc': return $a['price'] <=> $b['price'];
                case 'price_desc': return $b['price'] <=> $a['price'];
                case 'newest': return strtotime($b['created_at']) <=> strtotime($a['created_at']);
                case 'oldest': return strtotime($a['created_at']) <=> strtotime($b['created_at']);
                default: return 0;
            }
        });
    }

    return array_values($jerseys);
}

// Fetch all data
$jerseys = getAllJerseysWithTeams($conn);
$teams = $conn->query("SELECT id, name FROM teams ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

// Get filter values
$sortBy = $_GET['sort'] ?? 'newest';
$priceRaw = $_GET['price'] ?? 'all';
$jerseyType = $_GET['jersey_type'] ?? 'all';
$categoryType = $_GET['category'] ?? 'all';
$teamId = $_GET['team'] ?? 'all';

// Parse price range
$priceRange = null;
if ($priceRaw !== 'all' && preg_match('/^(\d+)-(\d+)$/', $priceRaw, $matches)) {
    $priceRange = [(float)$matches[1], (float)$matches[2]];
}

// Apply filters
$filteredJerseys = filterJerseys($jerseys, $sortBy, $priceRange, $jerseyType, $teamId, $categoryType);
foreach ($filteredJerseys as &$jersey) {
    $stmt = $conn->prepare("SELECT image_path FROM jersey_images WHERE jersey_id = ? ORDER BY id ASC");
    $stmt->execute([$jersey['id']]);
    $images = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $jersey['image_url'] = $images[0] ?? 'default.jpg';
}
unset($jersey);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>All Jerseys - GetJerseys</title>
      <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
     <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include 'includes/header.php'; ?>

<div class="container mt-4">
    <div class="row">
        <!-- Filters Sidebar -->
        <div class="col-lg-3">
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title mb-4">Filters</h5>
                    <form method="get" id="filterForm">
                        <!-- Category Type -->
                        <div class="mb-3">
                            <label class="form-label">Category</label>
                            <select name="category" class="form-select" onchange="this.form.submit()">
                                <option value="all" <?= $categoryType === 'all' ? 'selected' : '' ?>>All</option>
                                <option value="club" <?= $categoryType === 'club' ? 'selected' : '' ?>>Club</option>
                                <option value="national" <?= $categoryType === 'national' ? 'selected' : '' ?>>National</option>
                            </select>
                        </div>

                        <!-- Team -->
                        <div class="mb-3">
                            <label class="form-label">Team</label>
                            <select name="team" class="form-select" onchange="this.form.submit()">
                                <option value="all">All</option>
                                <?php foreach ($teams as $team): ?>
                                    <option value="<?= $team['id'] ?>" <?= $teamId == $team['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($team['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Jersey Type -->
                        <div class="mb-3">
                            <label class="form-label">Jersey Type</label>
                            <select name="jersey_type" class="form-select" onchange="this.form.submit()">
                                <option value="all" <?= $jerseyType === 'all' ? 'selected' : '' ?>>All</option>
                                <option value="home" <?= $jerseyType === 'home' ? 'selected' : '' ?>>Home</option>
                                <option value="away" <?= $jerseyType === 'away' ? 'selected' : '' ?>>Away</option>
                                <option value="third" <?= $jerseyType === 'third' ? 'selected' : '' ?>>Third</option>
                                <option value="goalkeeper" <?= $jerseyType === 'goalkeeper' ? 'selected' : '' ?>>Goalkeeper</option>
                            </select>
                        </div>

                        <!-- Price Range -->
                        <div class="mb-3">
                            <label class="form-label">Price Range</label>
                            <select name="price" class="form-select" onchange="this.form.submit()">
                                <option value="all" <?= $priceRaw === 'all' ? 'selected' : '' ?>>All</option>
                                <option value="0-5000" <?= $priceRaw === '0-5000' ? 'selected' : '' ?>>$0 - $5000</option>
                                <option value="5000-25000" <?= $priceRaw === '5000-25000' ? 'selected' : '' ?>>$5000 - $25000</option>
                                <option value="25000-200000" <?= $priceRaw === '25000-200000' ? 'selected' : '' ?>>$25000 - $200000</option>
                            </select>
                        </div>

                        <!-- Sort -->
                        <div class="mb-3">
                            <label class="form-label">Sort By</label>
                            <select name="sort" class="form-select" onchange="this.form.submit()">
                                <option value="newest" <?= $sortBy === 'newest' ? 'selected' : '' ?>>Newest</option>
                                <option value="oldest" <?= $sortBy === 'oldest' ? 'selected' : '' ?>>Oldest</option>
                                <option value="price_asc" <?= $sortBy === 'price_asc' ? 'selected' : '' ?>>Price: Low to High</option>
                                <option value="price_desc" <?= $sortBy === 'price_desc' ? 'selected' : '' ?>>Price: High to Low</option>
                            </select>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Jerseys Display -->
        <div class="col-lg-9">
            <div class="row">
                <?php if (empty($filteredJerseys)): ?>
                    <p>No jerseys found for selected filters.</p>
                <?php else: ?>
                    <?php foreach ($filteredJerseys as $jersey): ?>
                        <div class="col-md-4 mb-4">
                            <div class="card h-100">
                                <img src="assets/images/products/<?= htmlspecialchars($jersey['image_url']) ?>" class="card-img-top" alt="<?= htmlspecialchars($jersey['name']) ?>">
                                <div class="card-body">
                                    <h5 class="card-title"><?= htmlspecialchars($jersey['name']) ?></h5>
                                    <p class="card-text">$<?= number_format($jersey['price'], 2) ?></p>
                                    <p class="card-text"><small>Team: <?= htmlspecialchars($jersey['team_name']) ?></small></p>
                                    <a href="product.php?id=<?= $jersey['id'] ?>" class="btn btn-primary">View Details</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

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
