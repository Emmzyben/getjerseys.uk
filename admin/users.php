<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
session_start();

if (!isset($conn) || !$conn instanceof PDO) {
    die('Database connection not established.');
}

if (!isAdminLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Delete functionality
if (isset($_GET['delete'])) {
    $deleteId = (int) $_GET['delete'];
    
    try {
        $stmt = $conn->prepare("DELETE FROM admins WHERE id = ?");
        $stmt->execute([$deleteId]);
        $_SESSION['success'] = "User deleted successfully.";
        header("Location: users.php");
        exit;
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error deleting user: " . $e->getMessage();
    }
}

// Fetch admins
$query = "SELECT id, username, email, created_at FROM admins ORDER BY id DESC";
$stmt = $conn->prepare($query);
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>All Admin Users</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

    <!-- FontAwesome (optional) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <!-- Custom Admin CSS -->
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
<div class="admin-container">
    <?php include 'includes/sidebar.php'; ?>

    <main class="admin-content" style="padding: 40px;">
          <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">All Admin Users</h2>
                <a href="add-admin.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add New
                </a>
            </div>

        <?php if ($users): ?>
            <table id="usersTable" class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= htmlspecialchars($user['id']) ?></td>
                            <td><?= htmlspecialchars($user['username']) ?></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td><?= htmlspecialchars($user['created_at']) ?></td>
                            <td>
                                <a href="edit-user.php?id=<?= $user['id'] ?>" class="btn btn-sm btn-primary me-2">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                             <a href="users.php?delete=<?= $user['id'] ?>" class="btn btn-sm btn-danger"
   onclick="return confirm('Are you sure you want to delete this user?');">
   <i class="fas fa-trash-alt"></i> Delete
</a>

                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="alert alert-info">No admin users found.</div>
        <?php endif; ?>
    </main>
</div>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<!-- DataTables Init -->
<script>
    $(document).ready(function () {
        $('#usersTable').DataTable({
            pageLength: 10,
            lengthMenu: [5, 10, 25, 50],
            order: [[0, 'desc']],
            language: {
                searchPlaceholder: "Search users...",
                search: ""
            }
        });
    });
</script>
</body>
</html>
