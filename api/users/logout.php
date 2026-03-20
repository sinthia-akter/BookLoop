<?php
// api/users/logout.php
error_reporting(E_ALL);
ini_set('display_errors', 1);
date_default_timezone_set('UTC');
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../shared/utils.php';
require_once __DIR__ . '/../../includes/auth.php';

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(['error' => 'Method not allowed'], 405);
}

// Verify token and get user
$user = verifyToken();

// Clear the token from database
$stmt = $pdo->prepare("UPDATE users SET api_token = NULL, token_expiry = NULL WHERE user_id = ?");
$stmt->execute([$user['user_id']]);

sendResponse([
    'success' => true,
    'message' => 'Logged out successfully'
], 200);
?>