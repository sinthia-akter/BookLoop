<?php
// api/users/update_profile.php
error_reporting(E_ALL);
ini_set('display_errors', 1);
date_default_timezone_set('UTC');
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: PUT, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Allow PUT or POST
if ($_SERVER['REQUEST_METHOD'] != 'PUT' && $_SERVER['REQUEST_METHOD'] != 'POST') {
    sendResponse(['error' => 'Method not allowed. Use PUT or POST'], 405);
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../shared/utils.php';
require_once __DIR__ . '/../../includes/auth.php';

// Verify token and get user
$user = verifyToken();

// Get data
$input = getJsonInput();
if (!$input || empty($input)) {
    sendResponse(['error' => 'No data provided'], 400);
}

// Fields that can be updated
$allowedFields = ['full_name', 'email', 'phone', 'address'];
$updates = [];
$params = [];

// Build update query dynamically
foreach ($allowedFields as $field) {
    if (isset($input[$field]) && trim($input[$field]) !== '') {
        $updates[] = "$field = ?";
        $params[] = trim($input[$field]);
    }
}

if (empty($updates)) {
    sendResponse(['error' => 'No valid fields to update'], 400);
}

// If email is being updated, check if it already exists
if (isset($input['email'])) {
    if (!validateEmail($input['email'])) {
        sendResponse(['error' => 'Invalid email format'], 400);
    }
    
    $checkStmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ? AND user_id != ?");
    $checkStmt->execute([$input['email'], $user['user_id']]);
    if ($checkStmt->fetch()) {
        sendResponse(['error' => 'Email already exists'], 400);
    }
}

// If phone is being updated, check if it already exists
if (isset($input['phone'])) {
    // Validate phone format
    if (!preg_match('/^[0-9]{10,15}$/', $input['phone'])) {
        sendResponse(['error' => 'Phone must be 10-15 digits only'], 400);
    }
    
    // Check if phone exists for another user
    $checkStmt = $pdo->prepare("SELECT user_id FROM users WHERE phone = ? AND user_id != ?");
    $checkStmt->execute([$input['phone'], $user['user_id']]);
    if ($checkStmt->fetch()) {
        sendResponse(['error' => 'Phone number already exists'], 400);
    }
}

// Add user_id to params
$params[] = $user['user_id'];

try {
    $sql = "UPDATE users SET " . implode(', ', $updates) . " WHERE user_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    
    if ($stmt->rowCount() > 0) {
        // Fetch updated user
        $fetchStmt = $pdo->prepare("SELECT user_id, full_name, email, phone, address, role, created_at FROM users WHERE user_id = ?");
        $fetchStmt->execute([$user['user_id']]);
        $updatedUser = $fetchStmt->fetch();
        
        sendResponse([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => [
                'user' => [
                    'id' => $updatedUser['user_id'],
                    'name' => $updatedUser['full_name'],
                    'email' => $updatedUser['email'],
                    'phone' => $updatedUser['phone'],
                    'address' => $updatedUser['address'],
                    'role' => $updatedUser['role'],
                    'member_since' => $updatedUser['created_at']
                ]
            ]
        ], 200);
    } else {
        sendResponse(['error' => 'No changes made'], 400);
    }
    
} catch (PDOException $e) {
    sendResponse(['error' => 'Database error: ' . $e->getMessage()], 500);
}
?>