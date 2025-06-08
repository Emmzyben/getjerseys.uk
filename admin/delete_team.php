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

$id = $_GET['id'];
$stmt = $conn->prepare("DELETE FROM teams WHERE id = ?");
$stmt->execute([$id]);
$_SESSION['message'] = 'Team deleted successfully.';
$_SESSION['message_type'] = 'success';

header("Location: teams.php");
exit;
