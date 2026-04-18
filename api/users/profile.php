<?php
// api/users/profile.php
require_once '../../config/database.php';
require_once '../../shared/utils.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    sendResponse(['error' => 'Please login first'], 401);
}

try {
    $stmt = $pdo->prepare("SELECT user_id, full_name, email, phone, address, role, created_at FROM users WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        sendResponse(['error' => 'User not found'], 404);
    }

    sendResponse([
        'success' => true,
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
    
} catch (PDOException $e) {
    sendResponse(['error' => 'Database error: ' . $e->getMessage()], 500);
}
?>