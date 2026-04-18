<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Loading files...<br>";

require_once 'config/database.php';
echo "✅ database.php loaded<br>";

require_once 'shared/utils.php';
echo "✅ utils.php loaded<br>";

require_once 'includes/auth.php';
echo "✅ auth.php loaded<br>";

echo "<br>All files loaded successfully!<br>";
echo "PDO is working, utils functions are available, auth functions are available.";

// Test database connection
try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM books");
    $result = $stmt->fetch();
    echo "<br>✅ Database query successful! Total books: " . $result['count'];
} catch (Exception $e) {
    echo "<br>❌ Database query failed: " . $e->getMessage();
}
?>