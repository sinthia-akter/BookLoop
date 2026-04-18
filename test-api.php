<?php
// test-api.php - Simple test without auth

require_once 'config/database.php';
require_once 'shared/utils.php';

sendResponse([
    'success' => true,
    'message' => 'API is working!',
    'timestamp' => date('Y-m-d H:i:s')
]);
?>