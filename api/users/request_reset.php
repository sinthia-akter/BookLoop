<?php
// api/users/request_reset.php
error_reporting(E_ALL);
ini_set('display_errors', 1);
date_default_timezone_set('UTC');

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../shared/utils.php';

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
        ], 200);
    }

    // Generate reset token
    $resetToken = generateToken(32);
    
    // Save reset token using MySQL UTC time
    $updateStmt = $pdo->prepare("
        UPDATE users 
        SET reset_token = ?, 
            reset_expiry = UTC_TIMESTAMP() + INTERVAL 1 HOUR 
        WHERE email = ?
    ");
    $updateStmt->execute([$resetToken, $input['email']]);

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
            'data' => [
                'reset_token' => $result['reset_token'],
                'expiry_utc' => $result['reset_expiry'],
                'current_utc' => $result['utc_now'],
                'valid_for' => '1 hour',
                'note' => 'For testing only. In production, this would be emailed.'
            ]
        ], 200);
    } else {
        sendResponse(['error' => 'Failed to generate reset token'], 500);
    }
    
} catch (PDOException $e) {
    debugLog("Reset request error: " . $e->getMessage());
    sendResponse(['error' => 'Database error occurred'], 500);
}
?>