<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

// Check if category_type is provided
if (!isset($_GET['category_type'])) {
    echo json_encode(['error' => 'Missing category_type']);
    exit;
}

$categoryType = $_GET['category_type'];

// Fetch categories
$categories = getCategoriesByType($conn, $categoryType);

echo json_encode($categories);
?>
