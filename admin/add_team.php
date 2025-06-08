<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
session_start();

if (!isAdminLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Fetch categories for dropdown
$categories = [];
try {
    $stmt = $conn->query("SELECT id, name FROM categories");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching categories: " . $e->getMessage());
}

// Handle form submission
$successMsg = $errorMsg = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"]);
    $category_id = $_POST["category_id"];
    $category_type = $_POST["category_type"];

    if (!empty($name) && !empty($category_id) && !empty($category_type)) {
        try {
            $stmt = $conn->prepare("INSERT INTO teams (name, category_id, category_type) VALUES (:name, :category_id, :category_type)");
            $stmt->execute([
                ':name' => $name,
                ':category_id' => $category_id,
                ':category_type' => $category_type
            ]);
            $successMsg = "Team added successfully!";
        } catch (PDOException $e) {
            $errorMsg = "Error adding team: " . $e->getMessage();
        }
    } else {
        $errorMsg = "All fields are required.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Add Team - GetJerseys Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        .form-label { font-weight: 500; }
        .form-container {
            max-width: auto;
            margin: 0 auto;
            background: #fff;
            border-radius: 10px;
            padding: 32px 32px 24px 32px;
            box-shadow: 0 2px 16px rgba(0,0,0,0.08);
        }
        .admin-content h2 {
            margin-bottom: 32px;
        }
        .alert {
            margin-bottom: 24px;
        }
    </style>
</head>
<body>
<div class="admin-container">
    <?php include 'includes/sidebar.php'; ?>
    <main class="admin-content" style="padding: 40px;">
        <a href="teams.php" class="btn btn-secondary mb-3"><i class="fa fa-arrow-left"></i> Back to Teams</a>
        <h2><i class="fa fa-plus-circle"></i> Add Team</h2>

        <div class="form-container">
            <?php if ($successMsg): ?>
                <div class="alert alert-success" role="alert">
                    <?php echo htmlspecialchars($successMsg); ?>
                </div>
            <?php endif; ?>
            <?php if ($errorMsg): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo htmlspecialchars($errorMsg); ?>
                </div>
            <?php endif; ?>

            <form method="post" action="">
                <div class="mb-3">
                    <label class="form-label">Team Name</label>
                    <input type="text" name="name" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Category</label>
                    <select name="category_id" class="form-select" required>
                        <option value="">Select Category</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo htmlspecialchars($cat['id']); ?>">
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="form-label">Category Type</label>
                    <select name="category_type" class="form-select" required>
                        <option value="">Select Type</option>
                        <option value="national">National</option>
                        <option value="club">Club</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary w-100">
                    <i class="fa fa-plus"></i> Add Team
                </button>
            </form>
        </div>
    </main>
</div>
</body>
</html>
