<?php
// Helper functions

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] == 'admin';
}

function isBookstoreOwner() {
    return isset($_SESSION['role']) && $_SESSION['role'] == 'bookstore_owner';
}

function redirect($url) {
    header("Location: $url");
    exit();
}

function displayAlert($message, $type = 'success') {
    return "<div class='alert alert-$type'>$message</div>";
}

function getCartCount($conn, $user_id) {
    $sql = "SELECT SUM(quantity) as total FROM cart WHERE user_id = $user_id";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    return $row['total'] ? $row['total'] : 0;
}
?>