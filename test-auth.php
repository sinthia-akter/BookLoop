<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Testing auth.php...<br>";
require_once 'includes/auth.php';
echo "✅ auth.php loaded successfully!<br>";
echo "verifyToken() function exists: " . (function_exists('verifyToken') ? 'Yes' : 'No');
?>