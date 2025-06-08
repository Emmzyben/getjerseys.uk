<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
session_start();

if (!isAdminLoggedIn()) {
    header('Location: login.php');
    exit;
}

if (!isset($_GET['id'])) {
    $_SESSION['error'] = 'No product selected.';
    header('Location: products.php');
    exit;
}

$productId = intval($_GET['id']);
// Fetch main jersey details
$stmt = $conn->prepare("SELECT * FROM jerseys WHERE id = ?");
$stmt->execute([$productId]);
$jersey = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch all images for this jersey
$imageStmt = $conn->prepare("SELECT image_path FROM jersey_images WHERE jersey_id = ?");
$imageStmt->execute([$productId]);
$jerseyImages = $imageStmt->fetchAll(PDO::FETCH_COLUMN);

if (!$jersey) {
    $_SESSION['error'] = 'Product not found.';
    header('Location: products.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Product - GetJerseys Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>
    <div class="admin-container">
        <?php include 'includes/sidebar.php'; ?>
      <main class="admin-content" style="padding: 40px;">
    <a href="products.php" class="btn btn-secondary mb-3">&larr; Back to Products</a>

    <div class="card p-4 shadow-sm">
        <h2 class="mb-4"><?= htmlspecialchars($jersey['name']) ?></h2>

        <div class="row">
            <div class="col-md-6">
    <?php if (!empty($jerseyImages)): ?>
        <div class="d-flex flex-row overflow-auto mb-3" style="gap: 10px;">
            <?php foreach ($jerseyImages as $index => $img): ?>
                <img src="../assets/images/products/<?= htmlspecialchars($img) ?>"
                     class="img-thumbnail"
                     style="max-height: 200px; cursor: pointer;"
                     alt="<?= htmlspecialchars($jersey['name']) ?>"
                     data-bs-toggle="modal"
                     data-bs-target="#imageModal"
                     data-img="../assets/images/products/<?= htmlspecialchars($img) ?>">
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <img src="../assets/images/products/<?= htmlspecialchars($jersey['image_path']) ?>"
             class="img-fluid img-thumbnail mb-3"
             style="max-height: 300px; cursor: pointer;"
             alt="<?= htmlspecialchars($jersey['name']) ?>"
             data-bs-toggle="modal"
             data-bs-target="#imageModal"
             data-img="../assets/images/products/<?= htmlspecialchars($jersey['image_path']) ?>">
    <?php endif; ?>
</div>


            <div class="col-md-6">
                <table class="table table-bordered">
                    <tr>
                        <th>Type</th>
                        <td><?= ucfirst(htmlspecialchars($jersey['jersey_type'])) ?></td>
                    </tr>
                    <tr>
                        <th>Price</th>
                        <td>$<?= number_format($jersey['price'], 2) ?></td>
                    </tr>
                    <tr>
                        <th>Stock</th>
                        <td><?= $jersey['stock'] ?></td>
                    </tr>
                    <tr>
                        <th>Created At</th>
                        <td><?= $jersey['created_at'] ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</main>
<!-- Image Modal -->
<div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-body text-center">
        <img id="modalImage" src="" class="img-fluid" alt="Full-size image">
      </div>
    </div>
  </div>
</div>
<script>
    const imageModal = document.getElementById('imageModal');
    imageModal.addEventListener('show.bs.modal', function (event) {
        const triggerImage = event.relatedTarget;
        const imageUrl = triggerImage.getAttribute('data-img');
        const modalImage = document.getElementById('modalImage');
        modalImage.src = imageUrl;
    });
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    </div>
</body>
</html>
