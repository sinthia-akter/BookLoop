<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

require_once __DIR__ . '/../../config/database.php';

// Get JSON input
$input = file_get_contents("php://input");
$data = json_decode($input, true);

if (!$data) {
    echo json_encode(["success" => false, "message" => "Invalid JSON"]);
    exit();
}

if (empty($data['email']) || empty($data['password'])) {
    echo json_encode(["success" => false, "message" => "Email and password required"]);
    exit();
}

try {
    // Get user from database
    $sql = "SELECT user_id, full_name, email, password, role FROM users WHERE email = :email";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['email' => $data['email']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && password_verify($data['password'], $user['password'])) {
        // Start session
        session_start();
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['user_name'] = $user['full_name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        
        echo json_encode([
            "success" => true,
            "message" => "Login successful",
            "user" => [
                "id" => $user['user_id'],
                "name" => $user['full_name'],
                "email" => $user['email'],
                "role" => $user['role']
            ]
        ]);
    } else {
        echo json_encode([
            "success" => false, 
            "message" => "Invalid email or password"
        ]);
    }
} catch (PDOException $e) {
    echo json_encode([
        "success" => false, 
        "message" => "Login failed: " . $e->getMessage()
    ]);
}
?>