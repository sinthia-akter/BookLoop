<?php
// shared/utils.php

/**
 * Send JSON response with HTTP status code
 */
function sendResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header("Content-Type: application/json");
    echo json_encode($data);
    exit();
}

/**
 * Get JSON input from request body
 */
function getJsonInput() {
    $input = file_get_contents("php://input");
    return json_decode($input, true);
}

/**
 * Validate required fields
 */
function validateRequired($input, $fields) {
    $errors = [];
    foreach ($fields as $field) {
        if (!isset($input[$field]) || empty(trim($input[$field]))) {
            $errors[] = "$field is required";
        }
    }
    return $errors;
}

/**
 * Validate email format
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Check if email already exists in database
 */
function emailExists($pdo, $email) {
    $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    return $stmt->fetch() ? true : false;
}

/**
 * Generate a random token (for password reset, etc.)
 */
function generateToken($length = 32) {
    return bin2hex(random_bytes($length));
}

/**
 * Log debug messages to file
 */
function debugLog($message) {
    $logFile = __DIR__ . '/../debug.log';
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
}
?>