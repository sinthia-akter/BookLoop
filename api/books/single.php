<?php
// api/books/single.php
require_once '../../config/database.php';
require_once '../../shared/utils.php';

// Get book ID from URL parameter
$bookId = isset($_GET['id']) ? $_GET['id'] : null;

if (!$bookId) {
    sendResponse(['error' => 'Book ID required'], 400);
}

$sql = "SELECT b.*, u.name as seller_name 
        FROM books b 
        LEFT JOIN users u ON b.seller_id = u.id 
        WHERE b.id = ?";

$stmt = $pdo->prepare($sql);
$stmt->execute([$bookId]);
$book = $stmt->fetch();

if (!$book) {
    sendResponse(['error' => 'Book not found'], 404);
}

sendResponse(['book' => $book]);
?>