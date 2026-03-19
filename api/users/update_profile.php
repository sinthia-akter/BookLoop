<?php
// api/users/update-profile.php
error_reporting(E_ALL);
ini_set('display_errors', 1);
date_default_timezone_set('UTC');
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: PUT, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Allow POST if PUT not supported (some servers)
if ($_SERVER['REQUEST_METHOD'] != 'PUT' && $_SERVER['REQUEST_METHOD'] != 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed. Use PUT or POST']);
    exit();
}

require_once '../../config/database.php';
require_once '../../shared/utils.php';

// Start session and check login
session_start();
if (!isset($_SESSION['user_id'])) {
    sendResponse(['error' => 'Please login first'], 401);
}

// Get data (works for both PUT and POST)
if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
    $input = getJsonInput();
} else {
    // For POST, check both JSON and form data
    $input = getJsonInput();
    if (!$input) {
        $input = $_POST; // Fallback to form data
    }
}

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
    $checkStmt->execute([$input['email'], $_SESSION['user_id']]);
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
    $checkStmt->execute([$input['phone'], $_SESSION['user_id']]);
    if ($checkStmt->fetch()) {
        sendResponse(['error' => 'Phone number already exists'], 400);
    }
}

// Add user_id to params
$params[] = $_SESSION['user_id'];

try {
    $sql = "UPDATE users SET " . implode(', ', $updates) . " WHERE user_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    
    if ($stmt->rowCount() > 0) {
        if (isset($input['full_name'])) {
            $_SESSION['user_name'] = $input['full_name'];
        }
        
        // Fetch updated user
        $fetchStmt = $pdo->prepare("SELECT user_id, full_name, email, phone, address, role, created_at FROM users WHERE user_id = ?");
        $fetchStmt->execute([$_SESSION['user_id']]);
        $user = $fetchStmt->fetch();
        
        sendResponse([
            'success' => true,
            'message' => 'Profile updated successfully',
            'user' => [
                'id' => $user['user_id'],
                'name' => $user['full_name'],
                'email' => $user['email'],
                'phone' => $user['phone'],
                'address' => $user['address'],
                'role' => $user['role'],
                'member_since' => $user['created_at']
            ]
        ]);
    } else {
        sendResponse(['error' => 'No changes made'], 400);
    }
    
} catch (PDOException $e) {
    sendResponse(['error' => 'Database error: ' . $e->getMessage()], 500);
}
?>