<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
session_start();

// Check if admin is logged in
if (!isAdminLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Handle deletion
if (isset($_GET['delete_id'])) {
    $deleteId = intval($_GET['delete_id']);
    $stmt = $conn->prepare("DELETE FROM jerseys WHERE id = ?");
    if ($stmt->execute([$deleteId])) {
        $_SESSION['success'] = 'Product deleted successfully.';
    } else {
        $_SESSION['error'] = 'Failed to delete product.';
    }
    header('Location: products.php');
    exit;
}

// Fetch jerseys
$stmt = $conn->query("SELECT * FROM jerseys ORDER BY created_at DESC");
$jerseys = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch all images for each jersey
foreach ($jerseys as $key => $jersey) {
    $imgStmt = $conn->prepare("SELECT image_path FROM jersey_images WHERE jersey_id = ?");
    $imgStmt->execute([$jersey['id']]);
    $jerseys[$key]['images'] = $imgStmt->fetchAll(PDO::FETCH_COLUMN);
    // For backward compatibility, set the first image as 'image_url'
    $jerseys[$key]['image_url'] = $jerseys[$key]['images'][0] ?? null;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>All Products - GetJerseys Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

</head>
<body>
    <div class="admin-container">
        <?php include 'includes/sidebar.php'; ?>

        <main class="admin-content">
            <?php include 'includes/header.php'; ?>

            <div class="container-fluid py-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h3">All Jerseys</h1>
                    <a href="add-product.php" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i> Add New Product
                    </a>
                </div>

                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success"><?= $_SESSION['success'] ?></div>
                    <?php unset($_SESSION['success']); ?>
                <?php endif; ?>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>

                <div class="card shadow">
    <div class="card-body table-responsive">
        <table class="table table-bordered align-middle table-striped">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Price ($)</th>
                    <th>Stock</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($jerseys) > 0): ?>
                    <?php foreach ($jerseys as $index => $jersey): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td>
                                <img src="../assets/images/products/<?= $jersey['image_url'] ?>" alt="jersey" style="width: 60px; height: 60px;">
                            </td>
                            <td><?= htmlspecialchars($jersey['name']) ?></td>
                            <td><?= ucfirst($jersey['jersey_type']) ?></td>
                            <td><?= number_format($jersey['price'], 2) ?></td>
                            <td><?= $jersey['stock'] ?></td>
                            <td><?= date('Y-m-d', strtotime($jersey['created_at'])) ?></td>
                            <td>
                                <a href="view_product.php?id=<?= $jersey['id'] ?>" class="btn btn-info btn-sm me-1">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="edit_product.php?id=<?= $jersey['id'] ?>" class="btn btn-warning btn-sm me-1">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="products.php?delete_id=<?= $jersey['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this product?');">
                                    <i class="fas fa-trash-alt"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="text-center">No products found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
    $(document).ready(function () {
        $('.table').DataTable({
            responsive: true,
            columnDefs: [
                { orderable: false, targets: [1, 7] } // disable sorting on Image and Actions
            ]
        });
    });
</script>

</body>
</html>
