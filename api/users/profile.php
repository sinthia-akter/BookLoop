<?php
// api/users/profile.php
require_once '../../config/database.php';
require_once '../../shared/utils.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    sendResponse(['error' => 'Please login first'], 401);
}

// Get user profile
$stmt = $pdo->prepare("SELECT id, name, email, role, created_at FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user) {
    sendResponse(['error' => 'User not found'], 404);
}

sendResponse(['user' => $user]);
?>