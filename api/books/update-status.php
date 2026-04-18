<?php
// api/books/update-status.php
// Method: PATCH
// Description: Quickly update book status

require_once '../../config/database.php';
require_once '../../shared/utils.php';
require_once '../../includes/auth.php';

$user = verifyToken();

$bookId = isset($_GET['id']) ? (int)$_GET['id'] : null;

if (!$bookId) {
    sendResponse(['error' => 'Book ID is required'], 400);
}

// Check ownership
$checkStmt = $pdo->prepare("SELECT seller_id FROM books WHERE book_id = ?");
$checkStmt->execute([$bookId]);
$book = $checkStmt->fetch();

if (!$book) {
    sendResponse(['error' => 'Book not found'], 404);
}

if ($book['seller_id'] != $user['user_id'] && $user['role'] !== 'admin') {
    sendResponse(['error' => 'You can only update your own books'], 403);
}

$input = getJsonInput();

if (!isset($input['status'])) {
    sendResponse(['error' => 'Status is required'], 400);
}

$allowedStatuses = ['available', 'sold', 'pending'];
if (!in_array($input['status'], $allowedStatuses)) {
    sendResponse(['error' => 'Invalid status'], 400);
}

$stmt = $pdo->prepare("UPDATE books SET status = ? WHERE book_id = ?");
$stmt->execute([$input['status'], $bookId]);

sendResponse([
    'success' => true,
    'message' => 'Book status updated successfully',
    'new_status' => $input['status']
]);
?>