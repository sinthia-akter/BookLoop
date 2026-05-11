<?php
// api/orders/list.php
require_once '../../config/database.php';
require_once '../../shared/utils.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    sendResponse(['error' => 'Please login first'], 401);
}

// Get user's orders
$sql = "SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$_SESSION['user_id']]);
$orders = $stmt->fetchAll();

// Get items for each order
foreach ($orders as &$order) {
    $stmt = $pdo->prepare("
        SELECT oi.*, b.title, b.author 
        FROM order_items oi 
        JOIN books b ON oi.book_id = b.id 
        WHERE oi.order_id = ?
    ");
    $stmt->execute([$order['id']]);
    $order['items'] = $stmt->fetchAll();
}

sendResponse(['orders' => $orders]);
?>