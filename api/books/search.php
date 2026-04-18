<?php
// api/books/search.php
// Method: GET
// Description: Search books

require_once 'C:/xampp/htdocs/bookloop/config/database.php';
require_once 'C:/xampp/htdocs/bookloop/shared/utils.php';

$searchTerm = isset($_GET['q']) ? $_GET['q'] : '';
$genre = isset($_GET['genre']) ? $_GET['genre'] : '';
$minPrice = isset($_GET['min_price']) ? (float)$_GET['min_price'] : 0;
$maxPrice = isset($_GET['max_price']) ? (float)$_GET['max_price'] : 999999;

$sql = "SELECT * FROM books WHERE 1=1";
$params = [];

if (!empty($searchTerm)) {
    $sql .= " AND (title LIKE ? OR author LIKE ? OR genre LIKE ?)";
    $term = "%$searchTerm%";
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
}

if (!empty($genre)) {
    $sql .= " AND genre = ?";
    $params[] = $genre;
}

if ($minPrice > 0) {
    $sql .= " AND price >= ?";
    $params[] = $minPrice;
}

if ($maxPrice < 999999) {
    $sql .= " AND price <= ?";
    $params[] = $maxPrice;
}

$sql .= " ORDER BY created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$books = $stmt->fetchAll();

sendResponse([
    'success' => true,
    'books' => $books,
    'total' => count($books)
]);
?>