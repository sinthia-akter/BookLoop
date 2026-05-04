<?php
// includes/auth.php - Authentication helper functions

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../shared/utils.php';

function verifyToken() {
    // Get headers
    $headers = getallheaders();
    
    // Check if Authorization header exists
    if(!isset($headers['Authorization'])) {
        sendResponse(['error' => 'No token provided'], 401);
    }
    
    // Extract token (format: "Bearer TOKEN_HERE")
    $authHeader = $headers['Authorization'];
    $token = str_replace('Bearer ', '', $authHeader);
    
    global $pdo;
    
    // Check if token exists in database
    $stmt = $pdo->prepare("SELECT user_id, email, role FROM users WHERE api_token = ? AND token_expiry > NOW()");
    $stmt->execute([$token]);
    $user = $stmt->fetch();
    
    if(!$user) {
        sendResponse(['error' => 'Invalid or expired token'], 401);
    }
    
    return $user;
}

?>