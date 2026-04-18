<?php
echo "<pre>";
echo "=== CHECKING FILES ===\n\n";

// Check if folders exist
$folders = ['config', 'shared', 'includes', 'api/books'];
foreach ($folders as $folder) {
    if (is_dir($folder)) {
        echo "✅ Folder exists: $folder/\n";
    } else {
        echo "❌ Folder MISSING: $folder/\n";
    }
}

echo "\n=== CHECKING FILES ===\n\n";

// Check if files exist
$files = [
    'config/database.php',
    'shared/utils.php',
    'includes/auth.php',
    'api/books/create.php'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        echo "✅ File exists: $file\n";
        echo "   Size: " . filesize($file) . " bytes\n";
    } else {
        echo "❌ File MISSING: $file\n";
    }
}

echo "\n=== CURRENT DIRECTORY ===\n";
echo "Current path: " . __DIR__ . "\n";

echo "\n=== PHP VERSION ===\n";
echo "PHP Version: " . phpversion() . "\n";

echo "</pre>";
?>