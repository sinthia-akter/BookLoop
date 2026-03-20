<?php
// api/users/login.php
error_reporting(E_ALL);
ini_set('display_errors', 1);
date_default_timezone_set('UTC');
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../shared/utils.php';
require_once __DIR__ . '/../../includes/auth.php';

// Get JSON input
$input = getJsonInput();

if (!$input) {
    sendResponse(['error' => 'Invalid JSON input'], 400);
}

if (empty($input['email']) || empty($input['password'])) {
    sendResponse(['error' => 'Email and password required'], 400);
}

try {
    // Get user from database
    $sql = "SELECT user_id, full_name, email, password, role FROM users WHERE email = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$input['email']]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($input['password'], $user['password'])) {
        // Generate token
        $token = generateToken($user['user_id']);
        $expiry = date('Y-m-d H:i:s', strtotime('+7 days')); // Token valid for 7 days
        
        // Save token to database
        $updateStmt = $pdo->prepare("UPDATE users SET api_token = ?, token_expiry = ? WHERE user_id = ?");
        $updateStmt->execute([$token, $expiry, $user['user_id']]);
        
        sendResponse([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'token' => $token,
                'user' => [
                    'id' => $user['user_id'],
                    'name' => $user['full_name'],
                    'email' => $user['email'],
                    'role' => $user['role']
                ]
            ]
        ], 200);
    } else {
        sendResponse(['error' => 'Invalid email or password'], 401);
    }
} catch (PDOException $e) {
    sendResponse(['error' => 'Login failed: ' . $e->getMessage()], 500);
}
?>