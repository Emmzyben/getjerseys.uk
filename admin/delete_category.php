<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
session_start();

if (!isAdminLoggedIn()) {
    header('Location: login.php');
    exit;
}


if (!isset($_GET['id'])) {
    die("No category ID provided.");
}

$id = $_GET['id'];
$stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
$stmt->execute([$id]);
$_SESSION['message'] = 'Category deleted successfully.';
$_SESSION['message_type'] = 'success';

header("Location: categories.php");
exit;
