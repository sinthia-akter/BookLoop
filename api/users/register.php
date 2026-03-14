<?php
// api/users/register.php
require_once '../../config/database.php';
require_once '../../shared/utils.php';

// Get POST data
$input = getJsonInput();

// Validate required fields
$errors = validateRequired($input, ['name', 'email', 'password']);
if (!empty($errors)) {
    sendResponse(['error' => $errors], 400);
}

// Validate email format
if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
    sendResponse(['error' => 'Invalid email format'], 400);
}

// Check if email already exists
$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute([$input['email']]);
if ($stmt->fetch()) {
    sendResponse(['error' => 'Email already registered'], 409);
}

// Hash password
$hashedPassword = password_hash($input['password'], PASSWORD_DEFAULT);

// Insert user
$sql = "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)";
$role = isset($input['role']) ? $input['role'] : 'customer';

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$input['name'], $input['email'], $hashedPassword, $role]);
    
    $userId = $pdo->lastInsertId();
    
    sendResponse([
        'success' => true,
        'message' => 'User registered successfully',
        'user_id' => $userId
    ], 201);
    
} catch (PDOException $e) {
    sendResponse(['error' => 'Registration failed: ' . $e->getMessage()], 500);
}
?>