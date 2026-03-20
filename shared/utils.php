<?php
// shared/utils.php

// Set PHP to UTC
date_default_timezone_set('UTC');

function sendResponse($data, $statusCode = 200) {
    // Ensure statusCode is integer
    if (!is_int($statusCode)) {
        $statusCode = 400; // Default to 400 Bad Request if string passed
    }
    http_response_code($statusCode);
    header("Content-Type: application/json");
    echo json_encode($data);
    exit();
}

function getJsonInput() {
    $input = file_get_contents("php://input");
    return json_decode($input, true);
}

function validateRequired($input, $fields) {
    $errors = [];
    foreach ($fields as $field) {
        if (!isset($input[$field]) || empty(trim($input[$field]))) {
            $errors[] = "$field is required";
        }
    }
    return $errors;
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function emailExists($pdo, $email) {
    $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    return $stmt->fetch() ? true : false;
}

function generateToken($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

function debugLog($message) {
    $logFile = __DIR__ . '/../debug.log';
    $timestamp = gmdate('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp UTC] $message\n", FILE_APPEND);
}
?>