<?php
// includes/auth.php

require_once __DIR__ . '/../config/database.php';

function verifyToken() {
    // Get all headers (case-insensitive)
    $headers = array();
    foreach (getallheaders() as $name => $value) {
        $headers[strtolower($name)] = $value;
    }
    
    // Check for authorization header
    if (!isset($headers['authorization'])) {
        sendResponse(['error' => 'No token provided'], 401);
    }
    
    $authHeader = $headers['authorization'];
    $token = str_replace('Bearer ', '', $authHeader);
    $token = trim($token); // Remove any whitespace
    
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT user_id, full_name, email, role FROM users WHERE api_token = ? AND token_expiry > NOW()");
    $stmt->execute([$token]);
    $user = $stmt->fetch();
    
    if (!$user) {
        sendResponse(['error' => 'Invalid or expired token'], 401);
    }
    
    return $user;
}
?>