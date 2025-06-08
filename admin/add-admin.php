<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
session_start();

// Check if admin is logged in
if (!isAdminLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Handle form submission
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate inputs
    if (empty($_POST['username']) || strlen($_POST['username']) < 4) {
        $errors['username'] = 'Username must be at least 4 characters long';
    }
    
    if (empty($_POST['email']) || !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address';
    }
    
    if (empty($_POST['password']) || strlen($_POST['password']) < 6) {
        $errors['password'] = 'Password must be at least 6 characters long';
    }
    
    if ($_POST['password'] !== $_POST['confirm_password']) {
        $errors['confirm_password'] = 'Passwords do not match';
    }
    
    // If validation passes, add admin
    if (empty($errors)) {
        $username = $_POST['username'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        
        if (addAdmin($conn, $username, $email, $password)) {
            $success = true;
            // Reset form
            $_POST = [];
        } else {
            $errors['general'] = 'Failed to add admin. Username or email might already be in use.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Admin User - GetJerseys Admin</title>
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
                    <h1 class="h3">Add New Admin User</h1>
                    <a href="users.php" class="btn btn-outline-primary">
                        <i class="fas fa-arrow-left me-2"></i> Back to Users
                    </a>
                </div>
                
                <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    Admin user added successfully!
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
                                <h6 class="m-0 font-weight-bold text-primary">Admin User Information</h6>
                            </div>
                            <div class="card-body">
                                <form method="post" action="">
                                    <div class="mb-3">
                                        <label for="username" class="form-label">Username*</label>
                                        <input type="text" class="form-control <?= isset($errors['username']) ? 'is-invalid' : '' ?>" id="username" name="username" value="<?= $_POST['username'] ?? '' ?>" required>
                                        <?php if (isset($errors['username'])): ?>
                                        <div class="invalid-feedback"><?= $errors['username'] ?></div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email Address*</label>
                                        <input type="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" id="email" name="email" value="<?= $_POST['email'] ?? '' ?>" required>
                                        <?php if (isset($errors['email'])): ?>
                                        <div class="invalid-feedback"><?= $errors['email'] ?></div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="password" class="form-label">Password*</label>
                                        <input type="password" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>" id="password" name="password" required>
                                        <?php if (isset($errors['password'])): ?>
                                        <div class="invalid-feedback"><?= $errors['password'] ?></div>
                                        <?php endif; ?>
                                        <div class="form-text">Password must be at least 6 characters long.</div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="confirm_password" class="form-label">Confirm Password*</label>
                                        <input type="password" class="form-control <?= isset($errors['confirm_password']) ? 'is-invalid' : '' ?>" id="confirm_password" name="confirm_password" required>
                                        <?php if (isset($errors['confirm_password'])): ?>
                                        <div class="invalid-feedback"><?= $errors['confirm_password'] ?></div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-primary">Add Admin User</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-6">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Important Information</h6>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-info mb-4">
                                    <h5 class="alert-heading"><i class="fas fa-info-circle me-2"></i> Admin User Privileges</h5>
                                    <p>All admin users have full access to the admin dashboard and can perform the following actions:</p>
                                    <ul>
                                        <li>Manage products (add, edit, delete)</li>
                                        <li>Manage categories (add, edit, delete)</li>
                                        <li>View and update order statuses</li>
                                        <li>Add new admin users</li>
                                    </ul>
                                    <hr>
                                    <p class="mb-0">Please only create admin accounts for trusted personnel who need access to manage the website.</p>
                                </div>
                                
                                <div class="alert alert-warning">
                                    <h5 class="alert-heading"><i class="fas fa-exclamation-triangle me-2"></i> Security Notice</h5>
                                    <p>Admin users have access to sensitive customer and order information. Make sure to:</p>
                                    <ul>
                                        <li>Use strong, unique passwords</li>
                                        <li>Never share admin credentials</li>
                                        <li>Regularly update passwords</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>