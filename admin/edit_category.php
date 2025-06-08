<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
session_start();

if (!isAdminLoggedIn()) {
    header('Location: login.php');
    exit;
}

if (!isset($_GET['id'])) {
    die("No category ID provided.");
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    die("Invalid category ID.");
}

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $stmt = $conn->prepare("UPDATE categories SET name = ? WHERE id = ?");
    $stmt->execute([$name, $id]);
    $_SESSION['message'] = 'Category updated successfully.';
    $_SESSION['message_type'] = 'success';


    header("Location:categories.php");
    exit;
}

// Fetch existing
$stmt = $conn->prepare("SELECT * FROM categories WHERE id = ?");
$stmt->execute([$id]);
$category = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Category - GetJerseys Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>
<div class="admin-container d-flex">
    <?php include 'includes/sidebar.php'; ?>
    <main class="admin-content flex-grow-1 p-4">
        <a href="categories.php" class="btn btn-secondary mb-3">
            <i class="fa fa-arrow-left"></i> Back to categories
        </a>
        <div class="card shadow-sm" >
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fa fa-edit"></i> Edit Category</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label for="categoryName" class="form-label">Category Name:</label>
                        <input type="text" id="categoryName" name="name" class="form-control" value="<?= htmlspecialchars($category['name']) ?>" required>
                    </div>
                    <button type="submit" class="btn btn-success">
                        <i class="fa fa-save"></i> Update
                    </button>
                </form>
            </div>
        </div>
    </main>
</div>
</body>
</html>