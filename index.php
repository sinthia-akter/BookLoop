<?php
// index.php - Main router

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Get URL parameter
$url = isset($_GET['url']) ? $_GET['url'] : '';
$url = rtrim($url, '/');
$urlParts = explode('/', $url);

// Route to appropriate API file
if(count($urlParts) >= 2) {
    $resource = $urlParts[0];  // e.g., 'api'
    $endpoint = $urlParts[1];   // e.g., 'books'
    
    if($resource == 'api') {
        switch($endpoint) {
            // Member 2 route
            case 'books':
                require_once 'api/books/books.php';
                break;
            
            default:
                sendResponse(false, 'Endpoint not found', null, 404);
        }
    } else {
        sendResponse(false, 'Invalid API route', null, 404);
    }
} else {
    sendResponse(true, 'BookLoop API is running', [
        'version' => '1.0',
        'endpoints' => [
            '/api/books/:id (PUT, DELETE)'
        ]
    ]);
}

function sendResponse($success, $message, $data = null, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit();
}
?>