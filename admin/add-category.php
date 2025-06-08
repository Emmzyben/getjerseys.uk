<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
session_start();

// Check if admin is logged in
if (!isAdminLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Get categories
$nationalCategories = getCategoriesByType($conn, 'national');
$clubCategories = getCategoriesByType($conn, 'club');

// Handle form submission
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate inputs
    if (empty($_POST['name'])) {
        $errors['name'] = 'Category name is required';
    }
    
    if (empty($_POST['type']) || !in_array($_POST['type'], ['national', 'club'])) {
        $errors['type'] = 'Please select a valid category type';
    }
    
    // If validation passes, add category
    if (empty($errors)) {
        $name = $_POST['name'];
        $type = $_POST['type'];
        $parentId = !empty($_POST['parent_id']) ? $_POST['parent_id'] : null;
        
        if (addCategory($conn, $name, $type, $parentId)) {
            $success = true;
            // Reset form
            $_POST = [];
        } else {
            $errors['general'] = 'Failed to add category. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Category - GetJerseys Admin</title>
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
                    <h1 class="h3">Add New Category</h1>
                    <a href="categories.php" class="btn btn-outline-primary">
                        <i class="fas fa-arrow-left me-2"></i> Back to Categories
                    </a>
                </div>
                
                <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    Category added successfully!
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>
                
                <?php if (isset($errors['general'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= $errors['general'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>
                
                <div class="row">
                    <div class="col-lg-6">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Category Information</h6>
                            </div>
                            <div class="card-body">
                                <form method="post" action="">
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Category Name*</label>
                                        <input type="text" class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>" id="name" name="name" value="<?= $_POST['name'] ?? '' ?>" required>
                                        <?php if (isset($errors['name'])): ?>
                                        <div class="invalid-feedback"><?= $errors['name'] ?></div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="type" class="form-label">Category Type*</label>
                                        <select class="form-select <?= isset($errors['type']) ? 'is-invalid' : '' ?>" id="type" name="type" required>
                                            <option value="">Select Category Type</option>
                                            <option value="national" <?= isset($_POST['type']) && $_POST['type'] === 'national' ? 'selected' : '' ?>>National Teams</option>
                                            <option value="club" <?= isset($_POST['type']) && $_POST['type'] === 'club' ? 'selected' : '' ?>>Club Teams</option>
                                        </select>
                                        <?php if (isset($errors['type'])): ?>
                                        <div class="invalid-feedback"><?= $errors['type'] ?></div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="parent_id" class="form-label">Parent Category (Optional)</label>
                                        <select class="form-select" id="parent_id" name="parent_id">
                                            <option value="">None (Top Level Category)</option>
                                            <optgroup label="National Teams" id="national-parent-options">
                                                <?php foreach ($nationalCategories as $category): ?>
                                                <option value="<?= $category['id'] ?>" <?= isset($_POST['parent_id']) && $_POST['parent_id'] == $category['id'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($category['name']) ?>
                                                </option>
                                                <?php endforeach; ?>
                                            </optgroup>
                                            <optgroup label="Club Teams" id="club-parent-options">
                                                <?php foreach ($clubCategories as $category): ?>
                                                <option value="<?= $category['id'] ?>" <?= isset($_POST['parent_id']) && $_POST['parent_id'] == $category['id'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($category['name']) ?>
                                                </option>
                                                <?php endforeach; ?>
                                            </optgroup>
                                        </select>
                                        <div class="form-text">Select a parent category if this is a subcategory.</div>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-primary">Add Category</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-6">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Category Structure</h6>
                            </div>
                            <div class="card-body">
                                <ul class="nav nav-tabs" id="categoryTabs" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link active" id="national-tab" data-bs-toggle="tab" data-bs-target="#national" type="button" role="tab">National Teams</button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="club-tab" data-bs-toggle="tab" data-bs-target="#club" type="button" role="tab">Club Teams</button>
                                    </li>
                                </ul>
                                <div class="tab-content p-3 border border-top-0 rounded-bottom" id="categoryTabsContent">
                                    <div class="tab-pane fade show active" id="national" role="tabpanel">
                                        <div class="category-structure">
                                            <ul class="list-group">
                                                <li class="list-group-item active">National Teams</li>
                                                <li class="list-group-item">
                                                    <div class="d-flex align-items-center">
                                                        <i class="fas fa-globe-europe me-2"></i> Europe
                                                    </div>
                                                    <ul class="list-group mt-2">
                                                        <li class="list-group-item">
                                                            <div class="d-flex align-items-center">
                                                                <i class="fas fa-flag me-2"></i> France
                                                            </div>
                                                        </li>
                                                        <li class="list-group-item">
                                                            <div class="d-flex align-items-center">
                                                                <i class="fas fa-flag me-2"></i> Germany
                                                            </div>
                                                        </li>
                                                        <li class="list-group-item">
                                                            <div class="d-flex align-items-center">
                                                                <i class="fas fa-flag me-2"></i> England
                                                            </div>
                                                        </li>
                                                    </ul>
                                                </li>
                                                <li class="list-group-item">
                                                    <div class="d-flex align-items-center">
                                                        <i class="fas fa-globe-americas me-2"></i> South America
                                                    </div>
                                                    <ul class="list-group mt-2">
                                                        <li class="list-group-item">
                                                            <div class="d-flex align-items-center">
                                                                <i class="fas fa-flag me-2"></i> Brazil
                                                            </div>
                                                        </li>
                                                        <li class="list-group-item">
                                                            <div class="d-flex align-items-center">
                                                                <i class="fas fa-flag me-2"></i> Argentina
                                                            </div>
                                                        </li>
                                                    </ul>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="tab-pane fade" id="club" role="tabpanel">
                                        <div class="category-structure">
                                            <ul class="list-group">
                                                <li class="list-group-item active">Club Teams</li>
                                                <li class="list-group-item">
                                                    <div class="d-flex align-items-center">
                                                        <i class="fas fa-trophy me-2"></i> Premier League
                                                    </div>
                                                    <ul class="list-group mt-2">
                                                        <li class="list-group-item">
                                                            <div class="d-flex align-items-center">
                                                                <i class="fas fa-shield-alt me-2"></i> Manchester United
                                                            </div>
                                                        </li>
                                                        <li class="list-group-item">
                                                            <div class="d-flex align-items-center">
                                                                <i class="fas fa-shield-alt me-2"></i> Liverpool
                                                            </div>
                                                        </li>
                                                    </ul>
                                                </li>
                                                <li class="list-group-item">
                                                    <div class="d-flex align-items-center">
                                                        <i class="fas fa-trophy me-2"></i> La Liga
                                                    </div>
                                                    <ul class="list-group mt-2">
                                                        <li class="list-group-item">
                                                            <div class="d-flex align-items-center">
                                                                <i class="fas fa-shield-alt me-2"></i> Real Madrid
                                                            </div>
                                                        </li>
                                                        <li class="list-group-item">
                                                            <div class="d-flex align-items-center">
                                                                <i class="fas fa-shield-alt me-2"></i> Barcelona
                                                            </div>
                                                        </li>
                                                    </ul>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const typeSelect = document.getElementById('type');
            const parentSelect = document.getElementById('parent_id');
            const nationalOptions = document.getElementById('national-parent-options');
            const clubOptions = document.getElementById('club-parent-options');
            
            function updateParentOptions() {
                const type = typeSelect.value;
                
                if (type === 'national') {
                    nationalOptions.classList.remove('d-none');
                    clubOptions.classList.add('d-none');
                    
                    // Enable national options, disable club options
                    Array.from(nationalOptions.querySelectorAll('option')).forEach(option => {
                        option.disabled = false;
                    });
                    
                    Array.from(clubOptions.querySelectorAll('option')).forEach(option => {
                        option.disabled = true;
                    });
                } else if (type === 'club') {
                    nationalOptions.classList.add('d-none');
                    clubOptions.classList.remove('d-none');
                    
                    // Enable club options, disable national options
                    Array.from(nationalOptions.querySelectorAll('option')).forEach(option => {
                        option.disabled = true;
                    });
                    
                    Array.from(clubOptions.querySelectorAll('option')).forEach(option => {
                        option.disabled = false;
                    });
                } else {
                    nationalOptions.classList.add('d-none');
                    clubOptions.classList.add('d-none');
                }
            }
            
            typeSelect.addEventListener('change', updateParentOptions);
            
            // Initialize on page load
            updateParentOptions();
        });
    </script>
</body>
</html>