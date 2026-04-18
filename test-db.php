<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Testing database.php...<br>";
require_once 'config/database.php';
echo "✅ database.php loaded successfully!<br>";
echo "PDO connection is working!";
?>