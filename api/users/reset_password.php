<?php
// api/users/reset_password.php
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

// Validate required fields
$errors = validateRequired($input, ['reset_token', 'new_password']);
if (!empty($errors)) {
    sendResponse(['error' => $errors], 400);
}

// Validate password strength
if (strlen($input['new_password']) < 6) {
    sendResponse(['error' => 'Password must be at least 6 characters'], 400);
}

try {
    // Check token using UTC_TIMESTAMP()
    $stmt = $pdo->prepare("
        SELECT user_id, email 
        FROM users 
        WHERE reset_token = ? AND reset_expiry > UTC_TIMESTAMP()
    ");
    $stmt->execute([$input['reset_token']]);
    $user = $stmt->fetch();

    // If token not found or expired
    if (!$user) {
        // Check if token exists but expired
        $checkStmt = $pdo->prepare("
            SELECT reset_expiry, UTC_TIMESTAMP() as utc_now 
            FROM users 
            WHERE reset_token = ?
        ");
        $checkStmt->execute([$input['reset_token']]);
        $expired = $checkStmt->fetch();
        
        if ($expired) {
            sendResponse([
                'error' => 'Reset token has expired. Please request a new one.',
                'data' => [
                    'expired_at' => $expired['reset_expiry'],
                    'current_utc' => $expired['utc_now']
                ]
            ], 400);
        } else {
            sendResponse(['error' => 'Invalid reset token'], 400);
        }
    }

    // Hash the new password
    $hashedPassword = password_hash($input['new_password'], PASSWORD_DEFAULT);

    // Update password and clear reset token
    $updateStmt = $pdo->prepare("
        UPDATE users 
        SET password = ?, 
            reset_token = NULL, 
            reset_expiry = NULL 
        WHERE user_id = ?
    ");
    $updateStmt->execute([$hashedPassword, $user['user_id']]);

    // Log the password reset
    debugLog("Password reset successful for user: " . $user['email']);

    // Send success response
    sendResponse([
        'success' => true,
        'message' => 'Password reset successful. You can now login with your new password.'
    ], 200);

} catch (PDOException $e) {
    debugLog("Reset password error: " . $e->getMessage());
    sendResponse(['error' => 'Database error occurred'], 500);
}
?>