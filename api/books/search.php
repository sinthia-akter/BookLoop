<?php
// api/books/search.php
require_once '../../config/database.php';
require_once '../../shared/utils.php';

// Get search parameters
$search = isset($_GET['q']) ? $_GET['q'] : '';
$minPrice = isset($_GET['min_price']) ? $_GET['min_price'] : null;
$maxPrice = isset($_GET['max_price']) ? $_GET['max_price'] : null;
$condition = isset($_GET['condition']) ? $_GET['condition'] : null;

// Build query
$sql = "SELECT b.*, u.name as seller_name 
        FROM books b 
        LEFT JOIN users u ON b.seller_id = u.id 
        WHERE b.status = 'available'";

$params = [];

if (!empty($search)) {
    $sql .= " AND (b.title LIKE ? OR b.author LIKE ? OR b.isbn LIKE ?)";
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

if ($minPrice !== null) {
    $sql .= " AND b.price >= ?";
    $params[] = $minPrice;
}

if ($maxPrice !== null) {
    $sql .= " AND b.price <= ?";
    $params[] = $maxPrice;
}

if ($condition !== null) {
    $sql .= " AND b.condition = ?";
    $params[] = $condition;
}

$sql .= " ORDER BY b.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$books = $stmt->fetchAll();

sendResponse([
    'books' => $books,
    'total' => count($books)
]);
?>
