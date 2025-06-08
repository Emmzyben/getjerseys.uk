<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
session_start();

if (!isAdminLoggedIn()) {
    header('Location: login.php');
    exit;
}

if (!isset($_GET['id'])) {
    die("No team ID provided.");
}

$id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
if ($id === false) {
    die("Invalid team ID.");
}

// Fetch existing data
$stmt = $conn->prepare("SELECT * FROM teams WHERE id = ?");
$stmt->execute([$id]);
$team = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$team) {
    die("Team not found.");
}

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $category_id = filter_var($_POST['category_id'] ?? '', FILTER_VALIDATE_INT);
    $category_type = $_POST['category_type'] ?? '';

    if ($name === '' || $category_id === false || !in_array($category_type, ['national', 'club'])) {
        $_SESSION['message'] = 'Invalid input data.';
        $_SESSION['message_type'] = 'danger';
        header("Location: edit_team.php?id=$id");
        exit;
    }

    $stmt = $conn->prepare("UPDATE teams SET name = ?, category_id = ?, category_type = ? WHERE id = ?");
    $stmt->execute([$name, $category_id, $category_type, $id]);
    $_SESSION['message'] = 'Team updated successfully.';
    $_SESSION['message_type'] = 'success';


    header("Location: teams.php");
    exit;
}

$stmt = $conn->query("SELECT * FROM categories");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $message_type = $_SESSION['message_type'] ?? 'info';
    unset($_SESSION['message'], $_SESSION['message_type']);
} else {
    $message = null;
    $message_type = null;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Team - GetJerseys Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>
<div class="admin-container d-flex">
    <?php include 'includes/sidebar.php'; ?>
    <main class="admin-content flex-grow-1 p-4">
        <a href="teams.php" class="btn btn-secondary mb-3">
            <i class="fa fa-arrow-left"></i> Back to Teams
        </a>
        <?php if ($message): ?>
            <div class="alert alert-<?= htmlspecialchars($message_type) ?> alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h2 class="mb-0">Edit Team</h2>
            </div>
            <div class="card-body">
                <form method="POST" class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Team Name:</label>
                        <input type="text" name="name" value="<?= htmlspecialchars($team['name']) ?>" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Category:</label>
                        <select name="category_id" class="form-select" required>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= $team['category_id'] == $cat['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Category Type:</label>
                        <select name="category_type" class="form-select" required>
                            <option value="national" <?= $team['category_type'] == 'national' ? 'selected' : '' ?>>National</option>
                            <option value="club" <?= $team['category_type'] == 'club' ? 'selected' : '' ?>>Club</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-success">
                            <i class="fa fa-save"></i> Update
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>
</body>
</html>