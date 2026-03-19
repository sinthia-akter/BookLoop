<?php
// api/users/request_reset.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../../config/database.php';
require_once '../../shared/utils.php';

// Set PHP to UTC
date_default_timezone_set('UTC');

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(['error' => 'Method not allowed. Use POST'], 405);
}

// Get input
$input = getJsonInput();
if (!$input) {
    sendResponse(['error' => 'Invalid JSON input'], 400);
}

// Validate email
if (!isset($input['email']) || empty($input['email'])) {
    sendResponse(['error' => 'Email is required'], 400);
}

if (!validateEmail($input['email'])) {
    sendResponse(['error' => 'Invalid email format'], 400);
}

try {
    // Check if user exists
    $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
    $stmt->execute([$input['email']]);
    $user = $stmt->fetch();

    // Always return same message for security
    if (!$user) {
        sendResponse([
            'success' => true,
            'message' => 'If your email is registered, you will receive reset instructions'
        ]);
    }

    // Generate reset token (32 characters)
    $resetToken = generateToken(32);
    
    // METHOD 1: Using PHP UTC time
    // $expiry = gmdate('Y-m-d H:i:s', time() + 3600); // 1 hour from now in UTC
    
    // METHOD 2: Using MySQL UTC time (RECOMMENDED)
    $updateStmt = $pdo->prepare("
        UPDATE users 
        SET reset_token = ?, 
            reset_expiry = UTC_TIMESTAMP() + INTERVAL 1 HOUR 
        WHERE email = ?
    ");
    $updateStmt->execute([$resetToken, $input['email']]);

    // Check if update was successful
    if ($updateStmt->rowCount() > 0) {
        // Get the saved values to confirm
        $checkStmt = $pdo->prepare("
            SELECT 
                reset_token, 
                reset_expiry,
                UTC_TIMESTAMP() as utc_now
            FROM users 
            WHERE email = ?
        ");
        $checkStmt->execute([$input['email']]);
        $result = $checkStmt->fetch();
        
        // For development only - shows token
        sendResponse([
            'success' => true,
            'message' => 'Reset token generated',
            'reset_token' => $result['reset_token'],
            'expiry_utc' => $result['reset_expiry'],
            'current_utc' => $result['utc_now'],
            'valid_for' => '1 hour',
            'note' => 'For testing only. In production, this would be emailed.'
        ]);
        
        // IN PRODUCTION - use this version (no token shown)
        /*
        sendResponse([
            'success' => true,
            'message' => 'If your email is registered, you will receive reset instructions'
        ]);
        */
    } else {
        sendResponse(['error' => 'Failed to generate reset token'], 500);
    }
    
} catch (PDOException $e) {
    debugLog("Reset request error: " . $e->getMessage());
    sendResponse(['error' => 'Database error occurred'], 500);
}
?>