<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
session_start();

// Check if admin is logged in
if (!isAdminLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Get all categories
$nationalCategories = getCategoriesByType($conn, 'national');
$clubCategories = getCategoriesByType($conn, 'club');

// Handle form submission
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate inputs
    if (empty($_POST['name'])) {
        $errors['name'] = 'Product name is required';
    }

    if (empty($_POST['team_id']) || !is_numeric($_POST['team_id'])) {
        $errors['team_id'] = 'Please select a team';
    }

    if (empty($_POST['jersey_type'])) {
        $errors['jersey_type'] = 'Jersey type is required';
    }

    if (empty($_POST['price']) || !is_numeric($_POST['price']) || $_POST['price'] <= 0) {
        $errors['price'] = 'Please enter a valid price';
    }

    if (empty($_POST['stock']) || !is_numeric($_POST['stock']) || $_POST['stock'] < 0) {
        $errors['stock'] = 'Please enter a valid stock quantity';
    }

    $uploadOk = true;
    $imagePaths = [];

    if (isset($_FILES['images']) && count($_FILES['images']['name']) > 0) {
        $targetDir = "../assets/images/products/";

        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        for ($i = 0; $i < count($_FILES['images']['name']); $i++) {
            $tmpName = $_FILES['images']['tmp_name'][$i];
            $name = $_FILES['images']['name'][$i];
            $error = $_FILES['images']['error'][$i];
            $size = $_FILES['images']['size'][$i];
            $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));

            if ($error === 0) {
                if ($size > 5000000) {
                    $errors['images'] = 'One of the images is too large. Max size is 5MB.';
                    $uploadOk = false;
                    break;
                }

                if (!in_array($ext, ['jpg', 'jpeg', 'png'])) {
                    $errors['images'] = 'Only JPG, JPEG, and PNG files are allowed.';
                    $uploadOk = false;
                    break;
                }

                $uniqueName = uniqid() . '.' . $ext;
                $destination = $targetDir . $uniqueName;

                if (move_uploaded_file($tmpName, $destination)) {
                    $imagePaths[] = $uniqueName;
                } else {
                    $errors['images'] = 'Failed to upload one of the images.';
                    $uploadOk = false;
                    break;
                }
            } else {
                $errors['images'] = 'There was an error with one of the images.';
                $uploadOk = false;
                break;
            }
        }
    } else {
        $uploadOk = false;
        $errors['images'] = 'Please select at least one image.';
    }

    // If validation passes, add product
    if (empty($errors) && $uploadOk) {
        $productData = [
            'name' => $_POST['name'],
            'team_id' => $_POST['team_id'],
            'jersey_type' => $_POST['jersey_type'],
            'description' => $_POST['description'],
            'price' => $_POST['price'],
            'sizes' => implode(',', $_POST['sizes'] ?? []),
            'stock' => $_POST['stock']
        ];

        $jerseyId = addJersey($conn, $productData);

        if ($jerseyId) {
            // Insert images into jersey_images table
            $stmt = $conn->prepare("INSERT INTO jersey_images (jersey_id, image_path) VALUES (?, ?)");
            foreach ($imagePaths as $imgPath) {
                $stmt->execute([$jerseyId, $imgPath]);
            }

            $success = true;
            $_POST = []; // Clear form
        } else {
            $errors['general'] = 'Failed to add product. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product - GetJerseys Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>
    <div class="admin-container">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="admin-content">
            <?php include 'includes/header.php'; ?>
            
            <div class="container-fluid py-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h3">Add New Product</h1>
                    <a href="products.php" class="btn btn-outline-primary">
                        <i class="fas fa-arrow-left me-2"></i> Back to Products
                    </a>
                </div>
                
                <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    Product added successfully!
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>
                
                <?php if (isset($errors['general'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= $errors['general'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>
                
                <div class="card shadow">
                    <div class="card-body">
                        <form method="post" action="" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Product Name*</label>
                                        <input type="text" class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>" id="name" name="name" value="<?= $_POST['name'] ?? '' ?>" required>
                                        <?php if (isset($errors['name'])): ?>
                                        <div class="invalid-feedback"><?= $errors['name'] ?></div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="category_type" class="form-label">Category Type*</label>
                                                <select class="form-select" id="category_type" name="category_type" required>
                                                    <option value="">Select Category Type</option>
                                                    <option value="national" <?= isset($_POST['category_type']) && $_POST['category_type'] === 'national' ? 'selected' : '' ?>>National Teams</option>
                                                    <option value="club" <?= isset($_POST['category_type']) && $_POST['category_type'] === 'club' ? 'selected' : '' ?>>Club Teams</option>
                                                </select>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="category_id" class="form-label">Category*</label>
                                                <select class="form-select" id="category_id" name="category_id">
                                                    <option value="">Select Category</option>
                                                    <!-- Categories will be loaded dynamically -->
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="team_id" class="form-label">Team*</label>
                                        <select class="form-select <?= isset($errors['team_id']) ? 'is-invalid' : '' ?>" id="team_id" name="team_id">
                                            <option value="">Select Team</option>
                                            <!-- Teams will be loaded dynamically -->
                                        </select>
                                        <?php if (isset($errors['team_id'])): ?>
                                        <div class="invalid-feedback"><?= $errors['team_id'] ?></div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="jersey_type" class="form-label">Jersey Type*</label>
                                        <select class="form-select <?= isset($errors['jersey_type']) ? 'is-invalid' : '' ?>" id="jersey_type" name="jersey_type" required>
                                            <option value="">Select Jersey Type</option>
                                            <option value="home" <?= isset($_POST['jersey_type']) && $_POST['jersey_type'] === 'home' ? 'selected' : '' ?>>Home</option>
                                            <option value="away" <?= isset($_POST['jersey_type']) && $_POST['jersey_type'] === 'away' ? 'selected' : '' ?>>Away</option>
                                            <option value="third" <?= isset($_POST['jersey_type']) && $_POST['jersey_type'] === 'third' ? 'selected' : '' ?>>Third</option>
                                            <option value="goalkeeper" <?= isset($_POST['jersey_type']) && $_POST['jersey_type'] === 'goalkeeper' ? 'selected' : '' ?>>Goalkeeper</option>
                                        </select>
                                        <?php if (isset($errors['jersey_type'])): ?>
                                        <div class="invalid-feedback"><?= $errors['jersey_type'] ?></div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="description" class="form-label">Description</label>
                                        <textarea class="form-control" id="description" name="description" rows="5"><?= $_POST['description'] ?? '' ?></textarea>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="images" class="form-label">Product Images*</label>
                                        <input type="file" class="form-control <?= isset($errors['images']) ? 'is-invalid' : '' ?>" id="images" name="images[]" accept="image/*" multiple required>
                                        <?php if (isset($errors['images'])): ?>
                                            <div class="invalid-feedback"><?= $errors['images'] ?></div>
                                        <?php endif; ?>
                                        <div class="form-text">Upload images of the jersey. Max size: 5MB each. Formats: JPG, JPEG, PNG.</div>
                                        <div id="image-preview" class="mt-2 d-flex flex-wrap gap-2"></div>
                                    </div>
                                    <script>
                                    document.addEventListener("DOMContentLoaded", function () {
                                        const imagesInput = document.getElementById('images');
                                        const previewContainer = document.getElementById('image-preview');

                                        // Store selected files in a DataTransfer object
                                        let dt = new DataTransfer();

                                        function updateInputFiles() {
                                            imagesInput.files = dt.files;
                                        }

                                        imagesInput.addEventListener('change', function () {
                                            dt = new DataTransfer();
                                            previewContainer.innerHTML = '';
                                            const files = Array.from(this.files);

                                            files.forEach((file, idx) => {
                                                if (!file.type.startsWith('image/')) return;

                                                dt.items.add(file);

                                                const reader = new FileReader();
                                                reader.onload = function (e) {
                                                    const wrapper = document.createElement('div');
                                                    wrapper.className = 'position-relative d-inline-block';

                                                    const img = document.createElement('img');
                                                    img.src = e.target.result;
                                                    img.className = 'img-thumbnail';
                                                    img.style.maxWidth = '100px';
                                                    img.style.maxHeight = '100px';
                                                    img.style.objectFit = 'cover';

                                                    // Remove icon
                                                    const removeBtn = document.createElement('span');
                                                    removeBtn.innerHTML = '<i class="fas fa-times-circle text-danger"></i>';
                                                    removeBtn.style.position = 'absolute';
                                                    removeBtn.style.top = '2px';
                                                    removeBtn.style.right = '2px';
                                                    removeBtn.style.cursor = 'pointer';
                                                    removeBtn.title = 'Remove';

                                                    removeBtn.addEventListener('click', function () {
                                                        // Remove file from DataTransfer
                                                        const newDt = new DataTransfer();
                                                        Array.from(dt.files).forEach((f, i) => {
                                                            if (i !== idx) newDt.items.add(f);
                                                        });
                                                        dt = newDt;
                                                        updateInputFiles();

                                                        // Remove preview
                                                        wrapper.remove();

                                                        // Re-render previews to update remove indices
                                                        previewContainer.innerHTML = '';
                                                        Array.from(dt.files).forEach((f, i) => {
                                                            const reader2 = new FileReader();
                                                            reader2.onload = function (ev) {
                                                                const wrap = document.createElement('div');
                                                                wrap.className = 'position-relative d-inline-block';

                                                                const img2 = document.createElement('img');
                                                                img2.src = ev.target.result;
                                                                img2.className = 'img-thumbnail';
                                                                img2.style.maxWidth = '100px';
                                                                img2.style.maxHeight = '100px';
                                                                img2.style.objectFit = 'cover';

                                                                const removeBtn2 = document.createElement('span');
                                                                removeBtn2.innerHTML = '<i class="fas fa-times-circle text-danger"></i>';
                                                                removeBtn2.style.position = 'absolute';
                                                                removeBtn2.style.top = '2px';
                                                                removeBtn2.style.right = '2px';
                                                                removeBtn2.style.cursor = 'pointer';
                                                                removeBtn2.title = 'Remove';

                                                                removeBtn2.addEventListener('click', function () {
                                                                    // Remove file from DataTransfer
                                                                    const newDt2 = new DataTransfer();
                                                                    Array.from(dt.files).forEach((ff, ii) => {
                                                                        if (ii !== i) newDt2.items.add(ff);
                                                                    });
                                                                    dt = newDt2;
                                                                    updateInputFiles();
                                                                    wrap.remove();
                                                                    // Recursively re-render
                                                                    previewContainer.innerHTML = '';
                                                                    Array.from(dt.files).forEach((fff, iii) => {
                                                                        const reader3 = new FileReader();
                                                                        reader3.onload = function (evv) {
                                                                            // ...repeat as above (or factor out)
                                                                        };
                                                                        reader3.readAsDataURL(fff);
                                                                    });
                                                                });

                                                                wrap.appendChild(img2);
                                                                wrap.appendChild(removeBtn2);
                                                                previewContainer.appendChild(wrap);
                                                            };
                                                            reader2.readAsDataURL(f);
                                                        });
                                                    });

                                                    wrapper.appendChild(img);
                                                    wrapper.appendChild(removeBtn);
                                                    previewContainer.appendChild(wrapper);
                                                };
                                                reader.readAsDataURL(file);
                                            });

                                            updateInputFiles();
                                        });
                                    });
                                    </script>
                                    
                                    <div class="mb-3">
                                        <label for="price" class="form-label">Price ($)*</label>
                                        <input type="number" class="form-control <?= isset($errors['price']) ? 'is-invalid' : '' ?>" id="price" name="price" min="0.01" step="0.01" value="<?= $_POST['price'] ?? '' ?>" required>
                                        <?php if (isset($errors['price'])): ?>
                                        <div class="invalid-feedback"><?= $errors['price'] ?></div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Available Sizes*</label>
                                        <div class="size-checkboxes">
                                            <?php
                                            $sizes = ['S', 'M', 'L', 'XL', 'XXL'];
                                            $selectedSizes = $_POST['sizes'] ?? [];
                                            
                                            foreach ($sizes as $size):
                                            ?>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="sizes[]" id="size-<?= $size ?>" value="<?= $size ?>" <?= in_array($size, $selectedSizes) ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="size-<?= $size ?>"><?= $size ?></label>
                                            </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="stock" class="form-label">Stock*</label>
                                        <input type="number" class="form-control <?= isset($errors['stock']) ? 'is-invalid' : '' ?>" id="stock" name="stock" min="0" value="<?= $_POST['stock'] ?? '50' ?>" required>
                                        <?php if (isset($errors['stock'])): ?>
                                        <div class="invalid-feedback"><?= $errors['stock'] ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <hr class="my-4">
                            
                            <div class="d-flex justify-content-end">
                                <button type="reset" class="btn btn-outline-secondary me-2">Reset</button>
                                <button type="submit" class="btn btn-primary">Add Product</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
document.addEventListener("DOMContentLoaded", function () {
    const categoryTypeSelect = document.getElementById("category_type");
    const categorySelect = document.getElementById("category_id");
    const teamSelect = document.getElementById("team_id");

    // Load categories when category type changes
    categoryTypeSelect.addEventListener("change", function () {
        const type = this.value;
        categorySelect.innerHTML = '<option value="">Loading...</option>';
        fetch(`get_categories.php?category_type=${type}`)
            .then(res => res.json())
            .then(data => {
                categorySelect.innerHTML = '<option value="">Select Category</option>';
                data.forEach(category => {
                    const option = document.createElement("option");
                    option.value = category.id;
                    option.textContent = category.name;
                    categorySelect.appendChild(option);
                });
                teamSelect.innerHTML = '<option value="">Select Team</option>';
            });
    });

    // Load teams when category changes
    categorySelect.addEventListener("change", function () {
        const categoryId = this.value;
        teamSelect.innerHTML = '<option value="">Loading...</option>';
        fetch(`get_teams.php?category_id=${categoryId}`)
            .then(res => res.json())
            .then(data => {
                teamSelect.innerHTML = '<option value="">Select Team</option>';
                data.forEach(team => {
                    const option = document.createElement("option");
                    option.value = team.id;
                    option.textContent = team.name;
                    teamSelect.appendChild(option);
                });
            });
    });
});
</script>

</body>
</html>