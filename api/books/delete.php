<?php
// api/books/delete.php
require_once '../../config/database.php';
require_once '../../shared/utils.php';
session_start();

$bookId = isset($_GET['id']) ? $_GET['id'] : null;

if (!$bookId) {
    sendResponse(['error' => 'Book ID required'], 400);
}

// Check if user owns this book
$stmt = $pdo->prepare("SELECT seller_id FROM books WHERE id = ?");
$stmt->execute([$bookId]);
$book = $stmt->fetch();

if (!$book) {
    sendResponse(['error' => 'Book not found'], 404);
}

if ($book['seller_id'] != $_SESSION['user_id'] && $_SESSION['user_role'] !== 'admin') {
    sendResponse(['error' => 'You can only delete your own books'], 403);
}

try {
    $stmt = $pdo->prepare("DELETE FROM books WHERE id = ?");
    $stmt->execute([$bookId]);
    
    sendResponse(['success' => true, 'message' => 'Book deleted successfully']);
    
} catch (PDOException $e) {
    sendResponse(['error' => 'Delete failed: ' . $e->getMessage()], 500);
}
?>
