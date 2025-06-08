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

// Fetch current jersey data
$stmt = $conn->prepare("SELECT * FROM jerseys WHERE id = ?");
$stmt->execute([$productId]);
$jersey = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$jersey) {
    $_SESSION['error'] = 'Product not found.';
    header('Location: products.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $jerseyType = $_POST['jersey_type'];
    $price = floatval($_POST['price']);
    $stock = intval($_POST['stock']);
    $imageName = $jersey['image_url']; // default to existing image

   // Upload additional images if provided
if (!empty($_FILES['additional_images']['name'][0])) {
    $uploadDir = '../assets/images/products/';
    $allowed = ['jpg', 'jpeg', 'png', 'webp'];

    foreach ($_FILES['additional_images']['tmp_name'] as $key => $tmpName) {
        $originalName = basename($_FILES['additional_images']['name'][$key]);
        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        if (in_array($ext, $allowed)) {
            $newImageName = uniqid('jersey_', true) . '.' . $ext;
            if (move_uploaded_file($tmpName, $uploadDir . $newImageName)) {
                $stmt = $conn->prepare("INSERT INTO jersey_images (jersey_id, image_path) VALUES (?, ?)");
                $stmt->execute([$productId, $newImageName]);
            }
        }
    }
}

    // Update product
    $stmt = $conn->prepare("UPDATE jerseys SET name = ?, jersey_type = ?, price = ?, stock = ?, image_url = ? WHERE id = ?");
    if ($stmt->execute([$name, $jerseyType, $price, $stock, $imageName, $productId])) {
        $_SESSION['success'] = 'Product updated successfully.';
    } else {
        $_SESSION['error'] = 'Failed to update product.';
    }

    header('Location: products.php');
    exit;
}


  $imgStmt = $conn->prepare("SELECT * FROM jersey_images WHERE jersey_id = ?");
    $imgStmt->execute([$productId]);
    $additionalImages = $imgStmt->fetchAll(PDO::FETCH_ASSOC);

    // Handle delete image request
    if (isset($_GET['delete_image']) && is_numeric($_GET['delete_image'])) {
        $deleteId = intval($_GET['delete_image']);
        // Get image path
        $delStmt = $conn->prepare("SELECT image_path FROM jersey_images WHERE id = ? AND jersey_id = ?");
        $delStmt->execute([$deleteId, $productId]);
        $imgToDelete = $delStmt->fetch(PDO::FETCH_ASSOC);
        if ($imgToDelete) {
        $imgPath = '../assets/images/products/' . $imgToDelete['image_path'];
        // Delete from DB
        $conn->prepare("DELETE FROM jersey_images WHERE id = ?")->execute([$deleteId]);
        // Delete file
        if (file_exists($imgPath)) {
            unlink($imgPath);
        }
        // Redirect to avoid resubmission
        header("Location: edit_product.php?id=" . $productId);
        exit;
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Product - GetJerseys Admin</title>
   <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>
     <div class="admin-container">
    <?php include 'includes/sidebar.php'; ?>
    <main class="admin-content" style="padding: 40px;">
        <a href="products.php" class="btn btn-secondary mb-3">&larr; Back to Products</a>
        <h2>Edit Product</h2>

        <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label class="form-label">Product Name</label>
                <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($jersey['name']) ?>">
            </div>

            <div class="mb-3">
                <label class="form-label">Jersey Type</label>
                <select name="jersey_type" class="form-select" required>
                    <option value="home" <?= $jersey['jersey_type'] === 'home' ? 'selected' : '' ?>>Home</option>
                    <option value="away" <?= $jersey['jersey_type'] === 'away' ? 'selected' : '' ?>>Away</option>
                    <option value="third" <?= $jersey['jersey_type'] === 'third' ? 'selected' : '' ?>>Third</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Price ($)</label>
                <input type="number" step="0.01" name="price" class="form-control" required value="<?= htmlspecialchars($jersey['price']) ?>">
            </div>

            <div class="mb-3">
                <label class="form-label">Stock</label>
                <input type="number" name="stock" class="form-control" required value="<?= htmlspecialchars($jersey['stock']) ?>">
            </div>



<div class="mb-3">
    <label class="form-label">Upload Images</label>
    <input type="file" name="additional_images[]" class="form-control" multiple id="additionalImagesInput" accept=".jpg,.jpeg,.png,.webp">
    <small class="text-muted">You can upload multiple additional images (JPG, PNG, WEBP).</small>
    <div id="imagePreviewContainer" class="d-flex flex-wrap mt-2" style="gap: 10px;"></div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('additionalImagesInput');
    const previewContainer = document.getElementById('imagePreviewContainer');

    input.addEventListener('change', function () {
        previewContainer.innerHTML = '';
        Array.from(input.files).forEach((file, idx) => {
            if (!file.type.match('image.*')) return;
            const reader = new FileReader();
            reader.onload = function (e) {
                const wrapper = document.createElement('div');
                wrapper.style.position = 'relative';
                wrapper.style.display = 'inline-block';

                const img = document.createElement('img');
                img.src = e.target.result;
                img.width = 100;
                img.className = 'img-thumbnail';

                const removeBtn = document.createElement('span');
                removeBtn.innerHTML = '<i class="fas fa-times"></i>';
                removeBtn.style.position = 'absolute';
                removeBtn.style.top = '2px';
                removeBtn.style.right = '2px';
                removeBtn.style.color = '#dc3545';
                removeBtn.style.background = 'rgba(255,255,255,0.8)';
                removeBtn.style.borderRadius = '50%';
                removeBtn.style.padding = '2px 6px';
                removeBtn.style.cursor = 'pointer';
                removeBtn.title = 'Remove';

                removeBtn.onclick = function () {
                    // Remove the selected file from the input
                    const dt = new DataTransfer();
                    Array.from(input.files).forEach((f, i) => {
                        if (i !== idx) dt.items.add(f);
                    });
                    input.files = dt.files;
                    wrapper.remove();
                    // Re-render previews
                    input.dispatchEvent(new Event('change'));
                };

                wrapper.appendChild(img);
                wrapper.appendChild(removeBtn);
                previewContainer.appendChild(wrapper);
            };
            reader.readAsDataURL(file);
        });
    });
});
</script>

            <button type="submit" class="btn btn-primary">Update Product</button>
        </form>
   

    <?php if ($additionalImages): ?>
        <div class="mt-4">
        <h5>Images</h5>
        <div class="d-flex flex-wrap" style="gap: 10px;">
            <?php foreach ($additionalImages as $img): ?>
            <div style="position: relative; display: inline-block;">
                <img src="../assets/images/products/<?= htmlspecialchars($img['image_path']) ?>" width="100" class="img-thumbnail">
                <a href="?id=<?= $productId ?>&delete_image=<?= $img['id'] ?>"
                   onclick="return confirm('Delete this image?')"
                   style="position: absolute; top: 2px; right: 2px; color: #dc3545; background: rgba(255,255,255,0.8); border-radius: 50%; padding: 2px 6px; text-decoration: none; font-size: 16px;">
                <i class="fas fa-times"></i>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
        </div>
    <?php endif; ?>

    </main>
    </div>
</body>
</html>
