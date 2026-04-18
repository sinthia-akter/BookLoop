<?php
// api/books/update.php
// Method: PUT
// Description: Update a book

require_once 'C:/xampp/htdocs/bookloop/config/database.php';
require_once 'C:/xampp/htdocs/bookloop/shared/utils.php';

$bookId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($bookId <= 0) {
    sendResponse(['error' => 'Book ID is required'], 400);
}

// Check if book exists
$checkStmt = $pdo->prepare("SELECT book_id FROM books WHERE book_id = ?");
$checkStmt->execute([$bookId]);
if (!$checkStmt->fetch()) {
    sendResponse(['error' => 'Book not found'], 404);
}

$input = getJsonInput();

if (!$input) {
    sendResponse(['error' => 'No data provided'], 400);
}

// Fields that can be updated
$allowedFields = ['title', 'author', 'genre', 'isbn', 'price', 'book_condition', 'description', 'image_url', 'status'];
$updates = [];
$params = [];

foreach ($allowedFields as $field) {
    if (isset($input[$field]) && $input[$field] !== '') {
        $updates[] = "$field = ?";
        $params[] = $input[$field];
    }
}

if (empty($updates)) {
    sendResponse(['error' => 'No fields to update'], 400);
}

$params[] = $bookId;
$sql = "UPDATE books SET " . implode(', ', $updates) . " WHERE book_id = ?";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    
    sendResponse([
        'success' => true,
        'message' => 'Book updated successfully'
    ]);
} catch (PDOException $e) {
    sendResponse(['error' => 'Update failed: ' . $e->getMessage()], 500);
}
?>