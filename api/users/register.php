<?php
// api/users/register.php
error_reporting(E_ALL);
ini_set('display_errors', 1);
date_default_timezone_set('UTC');
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../shared/utils.php';

// Get POST data
$input = getJsonInput();

if (!$input) {
    sendResponse(['error' => 'Invalid JSON input'], 400);
}

// Validate required fields
$errors = validateRequired($input, ['full_name', 'email', 'password', 'phone']);
if (!empty($errors)) {
    sendResponse(['error' => $errors], 400);
}

// Validate email format
if (!validateEmail($input['email'])) {
    sendResponse(['error' => 'Invalid email format'], 400);
}

// Validate phone format (10-15 digits)
if (!preg_match('/^[0-9]{10,15}$/', $input['phone'])) {
    sendResponse(['error' => 'Phone must be 10-15 digits only'], 400);
}

// Check if email already exists
$stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
$stmt->execute([$input['email']]);
if ($stmt->fetch()) {
    sendResponse(['error' => 'Email already registered'], 409);
}

// Check if phone already exists
$stmt = $pdo->prepare("SELECT user_id FROM users WHERE phone = ?");
$stmt->execute([$input['phone']]);
if ($stmt->fetch()) {
    sendResponse(['error' => 'Phone number already registered'], 409);
}

// Hash password
$hashedPassword = password_hash($input['password'], PASSWORD_DEFAULT);

// Set role (default to 'customer')
$role = isset($input['role']) ? $input['role'] : 'customer';

// Validate role 
$allowed_roles = ['customer', 'bookstore_owner', 'admin'];
if (!in_array($role, $allowed_roles)) {
    sendResponse(['error' => 'Invalid role. Allowed: customer, bookstore_owner, admin'], 400);
}

// Insert user with phone
$sql = "INSERT INTO users (full_name, email, password, role, phone) VALUES (?, ?, ?, ?, ?)";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $input['full_name'], 
        $input['email'], 
        $hashedPassword, 
        $role,
        $input['phone']
    ]);
    
    $userId = $pdo->lastInsertId();
    
    sendResponse([
        'success' => true,
        'message' => 'User registered successfully',
        'user' => [
            'user_id' => $userId,
            'full_name' => $input['full_name'],
            'email' => $input['email'],
            'phone' => $input['phone'],
            'role' => $role
        ]
    ], 201);
    
} catch (PDOException $e) {
    // Check if it's a duplicate phone error
    if ($e->errorInfo[1] == 1062) {
        sendResponse(['error' => 'Phone number already exists'], 409);
    } else {
        sendResponse(['error' => 'Registration failed: ' . $e->getMessage()], 500);
    }
}
?>