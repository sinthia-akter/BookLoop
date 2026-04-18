<?php
// api/books/create.php

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Use absolute paths
$rootPath = 'C:/xampp/htdocs/bookloop/';

require_once $rootPath . 'config/database.php';
require_once $rootPath . 'shared/utils.php';

// For now, let's skip auth to test if the rest works
// Comment out auth for testing
// require_once $rootPath . 'includes/auth.php';

// TEMPORARILY skip authentication for testing
// Just check if user is logged in via session or use a default user
// For testing, we'll use a hardcoded seller_id (CHANGE THIS TO YOUR ACTUAL USER ID)
$test_user_id = 1;  // Change this to a valid user_id from your users table

// Get input data
$input = getJsonInput();

// If no JSON input, try POST data
if (!$input) {
    $input = $_POST;
}

// Validate required fields
$requiredFields = ['title', 'author', 'genre', 'price', 'book_condition'];
$errors = validateRequired($input, $requiredFields);

if (!empty($errors)) {
    sendResponse(['error' => $errors, 'received_data' => $input], 400);
}

// Validate price is positive
if ($input['price'] <= 0) {
    sendResponse(['error' => 'Price must be greater than 0'], 400);
}

// Validate condition
$allowedConditions = ['Like New', 'Good', 'Acceptable'];
if (!in_array($input['book_condition'], $allowedConditions)) {
    sendResponse(['error' => 'Invalid condition. Must be: Like New, Good, or Acceptable'], 400);
}

// Insert book
$sql = "INSERT INTO books (title, author, genre, isbn, price, book_condition, description, image_url, seller_id, status) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

try {
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute([
        $input['title'],
        $input['author'],
        $input['genre'],
        $input['isbn'] ?? null,
        $input['price'],
        $input['book_condition'],
        $input['description'] ?? null,
        $input['image_url'] ?? null,
        $test_user_id,  // Using test user ID instead of token
        $input['status'] ?? 'available'
    ]);
    
    $bookId = $pdo->lastInsertId();
    
    sendResponse([
        'success' => true,
        'message' => 'Book added successfully',
        'book_id' => $bookId,
        'book_title' => $input['title']
    ], 201);
    
} catch (PDOException $e) {
    sendResponse([
        'error' => 'Failed to add book: ' . $e->getMessage(),
        'sql' => $sql
    ], 500);
}
?>