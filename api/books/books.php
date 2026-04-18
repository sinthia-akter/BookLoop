<?php
// api/books/my-books.php
// Method: GET
// Description: Get all books for the logged-in seller

require_once '../../config/database.php';
require_once '../../shared/utils.php';
require_once '../../includes/auth.php';

$user = verifyToken();

// Only bookstore owners or admins can view their books
if ($user['role'] !== 'bookstore_owner' && $user['role'] !== 'admin') {
    sendResponse(['error' => 'Access denied'], 403);
}

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 12;
$offset = ($page - 1) * $limit;

$status = isset($_GET['status']) ? $_GET['status'] : null;

// Build query
$sql = "SELECT * FROM books WHERE seller_id = ?";
$params = [$user['user_id']];

if ($status) {
    $sql .= " AND status = ?";
    $params[] = $status;
}

// Get total count
$countStmt = $pdo->prepare(str_replace("*", "COUNT(*) as total", $sql));
$countStmt->execute($params);
$totalCount = $countStmt->fetch()['total'];

// Add pagination
$sql .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$books = $stmt->fetchAll();

sendResponse([
    'success' => true,
    'books' => $books,
    'pagination' => [
        'current_page' => $page,
        'per_page' => $limit,
        'total_items' => (int)$totalCount,
        'total_pages' => ceil($totalCount / $limit)
    ]
]);
?>