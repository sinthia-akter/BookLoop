<?php
// api/books/list.php
// Method: GET
// Description: Get all books

require_once 'C:/xampp/htdocs/bookloop/config/database.php';
require_once 'C:/xampp/htdocs/bookloop/shared/utils.php';

$sql = "SELECT b.*, u.full_name as seller_name 
        FROM books b 
        LEFT JOIN users u ON b.seller_id = u.user_id 
        ORDER BY b.created_at DESC";

$stmt = $pdo->query($sql);
$books = $stmt->fetchAll();

sendResponse([
    'success' => true,
    'books' => $books,
    'total' => count($books)
]);
?>