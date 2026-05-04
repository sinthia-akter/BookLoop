<?php
/**
 * Helper functions for BookLoop application
 */

// Redirect function
function redirect($url) {
    header("Location: $url");
    exit();
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Check user role
function hasRole($role) {
    return isset($_SESSION['role']) && $_SESSION['role'] == $role;
}

// Get user data by ID
function getUserById($conn, $user_id) {
    $sql = "SELECT * FROM users WHERE id = $user_id";
    $result = mysqli_query($conn, $sql);
    return mysqli_fetch_assoc($result);
}

// Get book by ID
function getBookById($conn, $book_id) {
    $sql = "SELECT * FROM books WHERE id = $book_id";
    $result = mysqli_query($conn, $sql);
    return mysqli_fetch_assoc($result);
}

// Format price
function formatPrice($price) {
    return '৳' . number_format($price, 2);
}

// Calculate cart total
function getCartTotal($conn, $user_id) {
    $sql = "SELECT SUM(b.price * c.quantity) as total 
            FROM cart c 
            JOIN books b ON c.book_id = b.id 
            WHERE c.user_id = $user_id";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    return $row['total'] ?? 0;
}

// Get cart item count
function getCartCount($conn, $user_id) {
    $sql = "SELECT SUM(quantity) as count FROM cart WHERE user_id = $user_id";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    return $row['count'] ?? 0;
}

// Sanitize input
function sanitize($conn, $input) {
    return mysqli_real_escape_string($conn, htmlspecialchars(trim($input)));
}

// Generate order number
function generateOrderNumber($order_id) {
    return 'BL-' . str_pad($order_id, 6, '0', STR_PAD_LEFT);
}

// Check if book belongs to seller
function isBookOwner($conn, $book_id, $seller_id) {
    $sql = "SELECT id FROM books WHERE id = $book_id AND seller_id = $seller_id";
    $result = mysqli_query($conn, $sql);
    return mysqli_num_rows($result) > 0;
}

// Get book condition badges
function getConditionBadge($condition) {
    $badges = [
        'new' => '<span class="badge badge-success">New</span>',
        'like_new' => '<span class="badge badge-info">Like New</span>',
        'good' => '<span class="badge badge-warning">Good</span>',
        'fair' => '<span class="badge badge-danger">Fair</span>'
    ];
    return $badges[$condition] ?? '<span class="badge">' . $condition . '</span>';
}

// Display flash messages
function displayFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $type = $_SESSION['flash_type'] ?? 'info';
        $message = $_SESSION['flash_message'];
        echo "<div class='alert alert-$type'>$message</div>";
        unset($_SESSION['flash_message']);
        unset($_SESSION['flash_type']);
    }
}

// Set flash message
function setFlashMessage($message, $type = 'info') {
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $type;
}

// Upload image function
function uploadImage($file, $target_dir = '../assets/uploads/books/') {
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    $filename = $file['name'];
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    
    if (!in_array($ext, $allowed)) {
        return ['error' => 'Only JPG, PNG, GIF files are allowed!'];
    }
    
    $new_filename = time() . '_' . rand(1000, 9999) . '.' . $ext;
    $upload_path = $target_dir . $new_filename;
    
    if (move_uploaded_file($file['tmp_name'], $upload_path)) {
        return ['success' => 'assets/uploads/books/' . $new_filename];
    } else {
        return ['error' => 'Failed to upload image!'];
    }
}

// Get user orders
function getUserOrders($conn, $user_id, $limit = null) {
    $sql = "SELECT * FROM orders WHERE user_id = $user_id ORDER BY created_at DESC";
    if ($limit) {
        $sql .= " LIMIT $limit";
    }
    return mysqli_query($conn, $sql);
}

// Get order items
function getOrderItems($conn, $order_id) {
    $sql = "SELECT oi.*, b.title, b.author, b.cover_image 
            FROM order_items oi 
            JOIN books b ON oi.book_id = b.id 
            WHERE oi.order_id = $order_id";
    return mysqli_query($conn, $sql);
}

// Update order status
function updateOrderStatus($conn, $order_id, $status) {
    $status = sanitize($conn, $status);
    $sql = "UPDATE orders SET order_status = '$status' WHERE id = $order_id";
    return mysqli_query($conn, $sql);
}

// Get seller's sales
function getSellerSales($conn, $seller_id) {
    $sql = "SELECT SUM(oi.quantity * oi.price) as total_sales 
            FROM order_items oi 
            WHERE oi.seller_id = $seller_id";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    return $row['total_sales'] ?? 0;
}

// Get seller's books count
function getSellerBooksCount($conn, $seller_id) {
    $sql = "SELECT COUNT(*) as count FROM books WHERE seller_id = $seller_id";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    return $row['count'];
}

// Get random books (for recommendations)
function getRandomBooks($conn, $limit = 4, $exclude_id = null) {
    $sql = "SELECT * FROM books";
    if ($exclude_id) {
        $sql .= " WHERE id != $exclude_id";
    }
    $sql .= " ORDER BY RAND() LIMIT $limit";
    return mysqli_query($conn, $sql);
}

// Search books function
function searchBooks($conn, $query, $filters = []) {
    $search = sanitize($conn, $query);
    $sql = "SELECT * FROM books WHERE title LIKE '%$search%' OR author LIKE '%$search%'";
    
    if (isset($filters['genre']) && !empty($filters['genre'])) {
        $genre = sanitize($conn, $filters['genre']);
        $sql .= " AND genre = '$genre'";
    }
    
    if (isset($filters['min_price']) && !empty($filters['min_price'])) {
        $min_price = intval($filters['min_price']);
        $sql .= " AND price >= $min_price";
    }
    
    if (isset($filters['max_price']) && !empty($filters['max_price'])) {
        $max_price = intval($filters['max_price']);
        $sql .= " AND price <= $max_price";
    }
    
    if (isset($filters['condition']) && !empty($filters['condition'])) {
        $condition = sanitize($conn, $filters['condition']);
        $sql .= " AND book_condition = '$condition'";
    }
    
    $sql .= " ORDER BY created_at DESC";
    return mysqli_query($conn, $sql);
}

// Debug function (for development)
function debug($data) {
    echo '<pre>';
    var_dump($data);
    echo '</pre>';
}

// Age calculation from timestamp
function timeAgo($timestamp) {
    $time_ago = strtotime($timestamp);
    $current_time = time();
    $time_difference = $current_time - $time_ago;
    $seconds = $time_difference;
    
    $minutes = round($seconds / 60);
    $hours = round($seconds / 3600);
    $days = round($seconds / 86400);
    $weeks = round($seconds / 604800);
    $months = round($seconds / 2629440);
    $years = round($seconds / 31553280);
    
    if ($seconds <= 60) {
        return "Just Now";
    } else if ($minutes <= 60) {
        return ($minutes == 1) ? "1 minute ago" : "$minutes minutes ago";
    } else if ($hours <= 24) {
        return ($hours == 1) ? "1 hour ago" : "$hours hours ago";
    } else if ($days <= 7) {
        return ($days == 1) ? "yesterday" : "$days days ago";
    } else if ($weeks <= 4.3) {
        return ($weeks == 1) ? "1 week ago" : "$weeks weeks ago";
    } else if ($months <= 12) {
        return ($months == 1) ? "1 month ago" : "$months months ago";
    } else {
        return ($years == 1) ? "1 year ago" : "$years years ago";
    }
}

// Validate email
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Generate random token
function generateToken($length = 32) {
    return bin2hex(random_bytes($length));
}

// Log user activity (optional)
function logActivity($conn, $user_id, $action, $details = null) {
    $user_id = intval($user_id);
    $action = sanitize($conn, $action);
    $details = $details ? sanitize($conn, $details) : 'NULL';
    $ip = $_SERVER['REMOTE_ADDR'];
    
    $sql = "INSERT INTO activity_logs (user_id, action, details, ip_address, created_at) 
            VALUES ($user_id, '$action', '$details', '$ip', NOW())";
    return mysqli_query($conn, $sql);
}
?>