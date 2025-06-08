<?php
require_once 'config/database.php';

$categories = [
  'national' => [],
  'club' => []
];

$query = "
SELECT c.id AS category_id, c.name AS category_name, c.type, t.id AS team_id, t.name AS team_name
FROM categories c
LEFT JOIN teams t ON c.id = t.category_id
WHERE c.type IN ('national', 'club')
ORDER BY c.type, c.name, t.name";


$result = $conn->query($query);

while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
    $type = $row['type'];
    $categoryName = $row['category_name'];
    $teamName = $row['team_name'];
    

    if (!isset($categories[$type][$categoryName])) {
        $categories[$type][$categoryName] = [];
    }

   if ($teamName) {
    $categories[$type][$categoryName][] = [
        'id' => $row['team_id'],
        'name' => $teamName
    ];
}

}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Get Jerseys - Premium Sports Jerseys</title>
<link rel="shortcut icon" href="assets/logo.png">
  <meta name="description" content="Get the latest premium quality football jerseys from top national teams and clubs. Shop now for authentic jerseys at unbeatable prices.">
  <meta name="keywords" content="football jerseys, premium jerseys, national teams, club teams, sports apparel">
  <meta name="author" content="Get Jerseys Team">
  <meta property="og:title" content="Get Jerseys - Premium Sports Jerseys">
  <meta property="og:description" content="Get the latest premium quality football jerseys from top national teams and clubs. Shop now for authentic jerseys at unbeatable prices.">
  <meta property="og:image" content="assets/logo.png">
  <meta property="og:url" content="https://getjerseys.uk">
  <meta property="og:type" content="website">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  <style>
    .navbar-brand img {
      max-height: 60px;
      transform: scale(2.5) !important;
      transform-origin: left center;
      transition: .5s;
    }
  </style>
</head>
<body>

<header class="main-header">
<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom">
  <div class="container">
    <!-- Logo -->
    <a class="navbar-brand d-flex align-items-center" href="index.php">
      <img src="assets/logo.png" alt="Get Jerseys Logo" class="me-2" style="height: 100px;">
    </a>

    <!-- Toggle for mobile -->
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <!-- Menu -->
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto me-3">
        <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>

  <!-- National Teams Grid Dropdown -->
<?php if (!empty($categories['national'])): ?>
  <li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">National Teams</a>
    <ul class="dropdown-menu p-3" style="min-width: 500px;">
      <div class="row">
        <?php foreach ($categories['national'] as $category => $teams): ?>
          <?php if (!empty($teams)): ?>
            <div class="col-md-6 mb-2">
              <strong><?= htmlspecialchars($category) ?></strong>
              <ul class="list-unstyled">
               <?php foreach ($teams as $team): ?>
  <li><a class="dropdown-item" href="teams.php?team_id=<?= $team['id'] ?>"><?= htmlspecialchars($team['name']) ?></a></li>
<?php endforeach; ?>

              </ul>
            </div>
          <?php endif; ?>
        <?php endforeach; ?>
      </div>
    </ul>
  </li>
<?php endif; ?>

<!-- Club Teams Grid Dropdown -->
<?php if (!empty($categories['club'])): ?>
  <li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Club Teams</a>
    <ul class="dropdown-menu p-3" style="min-width: 500px;">
      <div class="row">
        <?php foreach ($categories['club'] as $category => $teams): ?>
          <?php if (!empty($teams)): ?>
            <div class="col-md-6 mb-2">
              <strong><?= htmlspecialchars($category) ?></strong>
              <ul class="list-unstyled">
              <?php foreach ($teams as $team): ?>
  <li><a class="dropdown-item" href="teams.php?team_id=<?= $team['id'] ?>"><?= htmlspecialchars($team['name']) ?></a></li>
<?php endforeach; ?>

              </ul>
            </div>
          <?php endif; ?>
        <?php endforeach; ?>
      </div>
    </ul>
  </li>
<?php endif; ?>


        <!-- Jersey Types Dropdown -->
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Jersey Types</a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="jerseys.php?type=home">Home</a></li>
            <li><a class="dropdown-item" href="jerseys.php?type=away">Away</a></li>
            <li><a class="dropdown-item" href="jerseys.php?type=third">Third</a></li>
            <li><a class="dropdown-item" href="jerseys.php?type=goalkeeper">Goalkeeper</a></li>
          </ul>
        </li>

        <li class="nav-item"><a class="nav-link" href="all-jerseys.php">All Jerseys</a></li>
         <li class="nav-item"><a class="nav-link" href="jerseys.php?type=kiddies">Kiddies</a></li>
           <li class="nav-item"><a class="nav-link" href="jerseys.php?type=training">Training Kits</a></li>
      </ul>

      <!-- Cart Icon -->
      <div class="d-flex">
        <a href="cart.php" class="btn btn-outline-dark position-relative">
          <i class="fas fa-shopping-cart"></i>
          <?php if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?>
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
              <?= count($_SESSION['cart']) ?>
            </span>
          <?php endif; ?>
        </a>
      </div>
    </div>
  </div>
</nav>
</header>

</body>
</html>
