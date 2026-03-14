<?php
// api/users/login.php
require_once '../../config/database.php';
require_once '../../shared/utils.php';

session_start();

$input = getJsonInput();

// Validate
$errors = validateRequired($input, ['email', 'password']);
if (!empty($errors)) {
    sendResponse(['error' => $errors], 400);
}

// Get user from database
$stmt = $pdo->prepare("SELECT user_id, name, email, password, role FROM users WHERE email = ?");
$stmt->execute([$input['email']]);
$user = $stmt->fetch();

if (!$user) {
    // Don't reveal if email exists or not (security best practice)
    sendResponse(['error' => 'Invalid email or password'], 401);
}

// 🔐 VERIFY PASSWORD HASH - This checks the hash!
if (!password_verify($input['password'], $user['password'])) {
    sendResponse(['error' => 'Invalid email or password'], 401);
}

// Remove password from response
unset($user['password']);

// Set session
$_SESSION['user_id'] = $user['user_id'];
$_SESSION['user_role'] = $user['role'];

sendResponse([
    'success' => true,
    'message' => 'Login successful',
    'user' => $user
]);
?>