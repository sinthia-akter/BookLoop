<?php
require_once 'includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'bookstore_owner') {
    header("Location: login.php");
    exit();
}

$book_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Check if book belongs to this seller
$sql = "SELECT cover_image FROM books WHERE id = $book_id AND seller_id = " . $_SESSION['user_id'];
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) > 0) {
    $book = mysqli_fetch_assoc($result);
    // Delete image file
    if ($book['cover_image'] && file_exists($book['cover_image'])) {
        unlink($book['cover_image']);
    }
    // Delete from database
    $delete_sql = "DELETE FROM books WHERE id = $book_id";
    mysqli_query($conn, $delete_sql);
}

header("Location: my_books.php");
exit();
?>