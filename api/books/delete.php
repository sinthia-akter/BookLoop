<?php
// api/books/delete.php
// Method: DELETE
// Description: Delete a book

require_once 'C:/xampp/htdocs/bookloop/config/database.php';
require_once 'C:/xampp/htdocs/bookloop/shared/utils.php';

$bookId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($bookId <= 0) {
    sendResponse(['error' => 'Book ID is required'], 400);
}

// Check if book exists
$checkStmt = $pdo->prepare("SELECT title FROM books WHERE book_id = ?");
$checkStmt->execute([$bookId]);
$book = $checkStmt->fetch();

if (!$book) {
    sendResponse(['error' => 'Book not found'], 404);
}

try {
    $stmt = $pdo->prepare("DELETE FROM books WHERE book_id = ?");
    $stmt->execute([$bookId]);
    
    sendResponse([
        'success' => true,
        'message' => 'Book "' . $book['title'] . '" deleted successfully'
    ]);
} catch (PDOException $e) {
    sendResponse(['error' => 'Delete failed: ' . $e->getMessage()], 500);
}
?>