<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Testing utils.php...<br>";
require_once 'shared/utils.php';
echo "✅ utils.php loaded successfully!<br>";

// Test sendResponse function
echo "Testing sendResponse function...<br>";
// sendResponse(['test' => 'working'], 200);
?>