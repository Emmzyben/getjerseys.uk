<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

// Check if category_id is provided
if (!isset($_GET['category_id']) || !is_numeric($_GET['category_id'])) {
    echo json_encode(['error' => 'Missing or invalid category_id']);
    exit;
}

$categoryId = (int) $_GET['category_id'];

// Fetch teams
$teams = getTeamsByCategoryId($conn, $categoryId);

echo json_encode($teams);
?>
