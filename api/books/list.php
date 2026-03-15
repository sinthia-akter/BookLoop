<?php
// api/books/list.php
require_once '../../config/database.php';
require_once '../../shared/utils.php';

// Get all available books
$sql = "SELECT b.*, u.name as seller_name 
        FROM books b 
        LEFT JOIN users u ON b.seller_id = u.id 
        WHERE b.status = 'available'
        ORDER BY b.created_at DESC";

$stmt = $pdo->query($sql);
$books = $stmt->fetchAll();

sendResponse(['books' => $books]);
?>