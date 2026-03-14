<?php
// config/database.php

$host = 'localhost';
$dbname = 'bookloop_db';
$username = 'root';
$password = '';  // Leave empty for XAMPP, 'root' for MAMP

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // echo "Database connected successfully!"; // Uncomment to test
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>