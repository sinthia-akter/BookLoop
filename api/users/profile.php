<?php
// api/users/profile.php
error_reporting(E_ALL);
ini_set('display_errors', 1);
date_default_timezone_set('UTC');
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../shared/utils.php';
require_once __DIR__ . '/../../includes/auth.php';

// Only allow GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendResponse(['error' => 'Method not allowed'], 405);
}

// Verify token and get user
$user = verifyToken();

try {
    $stmt = $pdo->prepare("SELECT user_id, full_name, email, phone, address, role, created_at FROM users WHERE user_id = ?");
    $stmt->execute([$user['user_id']]);
    $userData = $stmt->fetch();

    if (!$userData) {
        sendResponse(['error' => 'User not found'], 404);
    }

    sendResponse([
        'success' => true,
        'message' => 'Profile retrieved successfully',
        'data' => [
            'user' => [
                'id' => $userData['user_id'],
                'name' => $userData['full_name'],
                'email' => $userData['email'],
                'phone' => $userData['phone'],
                'address' => $userData['address'],
                'role' => $userData['role'],
                'member_since' => $userData['created_at']
            ]
        ]
    ], 200);
    
} catch (PDOException $e) {
    sendResponse(['error' => 'Database error: ' . $e->getMessage()], 500);
}
?>