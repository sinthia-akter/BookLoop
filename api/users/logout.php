<?php
// api/users/logout.php
error_reporting(E_ALL);
ini_set('display_errors', 1);
date_default_timezone_set('UTC');
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

require_once '../../shared/utils.php';

session_start();
session_destroy();

sendResponse([
    'success' => true,
    'message' => 'Logged out successfully'
]);
?>