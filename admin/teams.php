<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
session_start();

if (!isAdminLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Fetch all teams with category name
try {
    $stmt = $conn->query("
        SELECT teams.id, teams.name AS team_name, categories.name AS category_name, teams.category_type, teams.created_at
        FROM teams
        INNER JOIN categories ON teams.category_id = categories.id
        ORDER BY teams.created_at DESC
    ");
    $teams = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching teams: " . $e->getMessage());
}
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
    <title>All Teams</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <!-- Custom Admin CSS -->
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>

<body>
    <div class="admin-container d-flex">
        <?php include 'includes/sidebar.php'; ?>

        <main class="admin-content p-5 w-100">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">All Teams</h2>
                <a href="add_team.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add New
                </a>
            </div>
 <?php if ($message): ?>
            <div class="alert alert-<?= htmlspecialchars($message_type) ?> alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
            <?php if (count($teams) > 0): ?>
                <div class="table-responsive">
                    <table id="teamsTable" class="table table-bordered table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Team Name</th>
                                <th>Category</th>
                                <th>Type</th>
                                <th>Created At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($teams as $team): ?>
                                <tr>
                                    <td><?= htmlspecialchars($team['id']) ?></td>
                                    <td><?= htmlspecialchars($team['team_name']) ?></td>
                                    <td><?= htmlspecialchars($team['category_name']) ?></td>
                                    <td><?= htmlspecialchars($team['category_type']) ?></td>
                                    <td><?= htmlspecialchars($team['created_at']) ?></td>
                                    <td>
                                        <a href="edit_team.php?id=<?= $team['id'] ?>" class="btn btn-sm btn-warning me-1">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <a href="delete_team.php?id=<?= $team['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this team?');">
                                            <i class="fas fa-trash-alt"></i> Delete
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info">No teams found.</div>
            <?php endif; ?>
        </main>
    </div>

       <!-- jQuery (Required first) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- Bootstrap Bundle (Popper included) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <!-- Initialize DataTable -->
    <script>
        $(document).ready(function () {
            $('#teamsTable').DataTable({
                pageLength: 10,
                lengthMenu: [5, 10, 25, 50],
                order: [[0, 'desc']],
                language: {
                    searchPlaceholder: "Search teams...",
                    search: ""
                }
            });
        });
    </script>

</body>
</html>
