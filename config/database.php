<?php
// config/database.php
$host = 'localhost';
$dbname = 'bookloop_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // Set MySQL to UTC
    $pdo->exec("SET time_zone = '+00:00'");
    
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>