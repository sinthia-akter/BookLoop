<?php
// api/books/update.php
require_once '../../config/database.php';
require_once '../../shared/utils.php';
session_start();

$input = getJsonInput();
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
    sendResponse(['error' => 'You can only edit your own books'], 403);
}

// Build update query dynamically
$updateFields = [];
$params = [];

if (isset($input['title'])) {
    $updateFields[] = "title = ?";
    $params[] = $input['title'];
}
if (isset($input['author'])) {
    $updateFields[] = "author = ?";
    $params[] = $input['author'];
}
if (isset($input['price'])) {
    $updateFields[] = "price = ?";
    $params[] = $input['price'];
}
if (isset($input['condition'])) {
    $updateFields[] = "`condition` = ?";
    $params[] = $input['condition'];
}

if (empty($updateFields)) {
    sendResponse(['error' => 'No fields to update'], 400);
}

// Add book ID to params
$params[] = $bookId;

$sql = "UPDATE books SET " . implode(", ", $updateFields) . " WHERE id = ?";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    
    sendResponse(['success' => true, 'message' => 'Book updated successfully']);
    
} catch (PDOException $e) {
    sendResponse(['error' => 'Update failed: ' . $e->getMessage()], 500);
}
?>
