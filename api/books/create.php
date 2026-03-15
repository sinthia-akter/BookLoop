<?php
// api/books/create.php
require_once '../../config/database.php';
require_once '../../shared/utils.php';
session_start();

// Check if user is logged in and is bookstore owner
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'bookstore_owner') {
    sendResponse(['error' => 'Only bookstore owners can add books'], 403);
}

$input = getJsonInput();

// Validate required fields
$errors = validateRequired($input, ['title', 'author', 'price']);
if (!empty($errors)) {
    sendResponse(['error' => $errors], 400);
}

// Insert book
$sql = "INSERT INTO books (title, author, isbn, price, `condition`, seller_id) 
        VALUES (?, ?, ?, ?, ?, ?)";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $input['title'],
        $input['author'],
        $input['isbn'] ?? null,
        $input['price'],
        $input['condition'] ?? 'Good',
        $_SESSION['user_id']
    ]);
    
    $bookId = $pdo->lastInsertId();
    
    sendResponse([
        'success' => true,
        'message' => 'Book added successfully',
        'book_id' => $bookId
    ], 201);
    
} catch (PDOException $e) {
    sendResponse(['error' => 'Failed to add book: ' . $e->getMessage()], 500);
}
?>