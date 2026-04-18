<?php
// api/users/register.php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

require_once '../../config/database.php';

// Helper function to get JSON input
function getJsonInput() {
    return json_decode(file_get_contents("php://input"), true);
}

// Helper function to send response
function sendResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data);
    exit();
}

// Validate required fields
function validateRequired($input, $fields) {
    $errors = [];
    foreach ($fields as $field) {
        if (empty($input[$field])) {
            $errors[] = "$field is required";
        }
    }
    return $errors;
}

// Get POST data
$input = getJsonInput();

if (!$input) {
    sendResponse(['error' => 'Invalid JSON input'], 400);
}

// Validate required fields
$errors = validateRequired($input, ['full_name', 'email', 'password']);
if (!empty($errors)) {
    sendResponse(['error' => $errors], 400);
}

// Validate email format
if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
    sendResponse(['error' => 'Invalid email format'], 400);
}

// Check if email already exists
$stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
$stmt->execute([$input['email']]);
if ($stmt->fetch()) {
    sendResponse(['error' => 'Email already registered'], 409);
}

// Hash password
$hashedPassword = password_hash($input['password'], PASSWORD_DEFAULT);

// Set role (default to 'customer')
$role = isset($input['role']) ? $input['role'] : 'customer';

// Validate role (optional but good practice)
$allowed_roles = ['customer', 'bookstore_owner', 'admin'];
if (!in_array($role, $allowed_roles)) {
    sendResponse(['error' => 'Invalid role. Allowed: customer, bookstore_owner, admin'], 400);
}

// Insert user - FIXED: Using correct column names from your database
$sql = "INSERT INTO users (full_name, email, password, role) VALUES (?, ?, ?, ?)";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$input['full_name'], $input['email'], $hashedPassword, $role]);
    
    $userId = $pdo->lastInsertId();
    
    // Don't return password in response
    sendResponse([
        'success' => true,
        'message' => 'User registered successfully',
        'user' => [
            'user_id' => $userId,
            'full_name' => $input['full_name'],
            'email' => $input['email'],
            'role' => $role
        ]
    ], 201);
    
} catch (PDOException $e) {
    sendResponse(['error' => 'Registration failed: ' . $e->getMessage()], 500);
}
?>