<?php
// api/users/logout.php
require_once '../../shared/utils.php';
session_start();

// Clear session
session_destroy();

sendResponse(['success' => true, 'message' => 'Logged out successfully']);
?>