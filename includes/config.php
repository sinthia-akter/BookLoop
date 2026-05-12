<?php
// Database configuration
$host = "localhost";
$username = "root";
$password = "";
$database = "bookloop_db";

// Create connection
$conn = mysqli_connect($host, $username, $password, $database);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Set timezone
date_default_timezone_set("Asia/Dhaka");

// Start session for login
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Error reporting for development (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>