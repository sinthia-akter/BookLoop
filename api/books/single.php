<?php
// api/books/single.php
// Method: GET
// Description: Get a single book by ID

require_once 'C:/xampp/htdocs/bookloop/config/database.php';
require_once 'C:/xampp/htdocs/bookloop/shared/utils.php';

$bookId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($bookId <= 0) {
    sendResponse(['error' => 'Book ID is required'], 400);
}

$sql = "SELECT b.*, u.full_name as seller_name, u.email as seller_email 
        FROM books b 
        LEFT JOIN users u ON b.seller_id = u.user_id 
        WHERE b.book_id = ?";

$stmt = $pdo->prepare($sql);
$stmt->execute([$bookId]);
$book = $stmt->fetch();

if (!$book) {
    sendResponse(['error' => 'Book not found'], 404);
}

sendResponse([
    'success' => true,
    'book' => $book
]);
?>