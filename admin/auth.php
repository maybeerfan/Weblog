<?php
session_start();
require_once '../connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

// Get user information
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Check if user is erfan (admin)
if ($user['username'] !== 'erfan') {
    header('Location: ../index.php');
    exit();
}

// If we reach here, user is authenticated as erfan
return true;
?>
