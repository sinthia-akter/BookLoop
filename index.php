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
    $endpoint = $urlParts[1];   // e.g., 'profile', 'books', 'cart'
    
    if($resource == 'api') {
        switch($endpoint) {
            // ==========================================
            // Member 1: User Management Routes
            // ==========================================
            
            // GET /api/profile - View profile
            case 'profile':
                require_once 'api/users/profile.php';
                break;
            
            // POST /api/logout - Logout user
            case 'logout':
                require_once 'api/users/logout.php';
                break;
            
            // POST /api/request-reset - Request password reset
            case 'request-reset':
                require_once 'api/users/request_reset.php';
                break;
            
            // POST /api/reset-password - Complete password reset
            case 'reset-password':
                require_once 'api/users/reset_password.php';
                break;
            
            // PUT/POST /api/update-profile - Update user profile
            case 'update-profile':
                require_once 'api/users/update_profile.php';
                break;
            
            // POST /api/register - Register new user
            case 'register':
                require_once 'api/users/register.php';
                break;
            
            // POST /api/login - Login user
            case 'login':
                require_once 'api/users/login.php';
                break;
            
            // ==========================================
            // Member 2: Book Management Routes
            // ==========================================
            case 'books':
                require_once 'api/books/books.php';
                break;
            
            // ==========================================
            // Member 3: Cart & Search Routes
            // ==========================================
            case 'cart':
                require_once 'api/cart/cart.php';
                break;
            
            case 'search':
                require_once 'api/search/search.php';
                break;
            
            // ==========================================
            // Default: Endpoint not found
            // ==========================================
            default:
                sendResponse(false, 'Endpoint not found', null, 404);
        }
    } else {
        sendResponse(false, 'Invalid API route', null, 404);
    }
} else {
    // Root URL - Show API info
    sendResponse(true, 'BookLoop API is running', [
        'version' => '1.0',
        'endpoints' => [
            // Member 1 Endpoints
            'POST /api/register - Register new user',
            'POST /api/login - Login (returns token)',
            'GET /api/profile - View profile (requires token)',
            'PUT/POST /api/update-profile - Update profile (requires token)',
            'POST /api/logout - Logout (requires token)',
            'POST /api/request-reset - Request password reset',
            'POST /api/reset-password - Reset password with token',
            
            // Member 2 Endpoints
            'PUT /api/books/:id - Update book (requires token)',
            'DELETE /api/books/:id - Delete book (requires token)',
            
            // Member 3 Endpoints
            'PUT /api/cart/:item - Update cart item quantity (requires token)',
            'DELETE /api/cart/:item - Remove from cart (requires token)',
            'GET /api/search - Advanced search'
        ]
    ]);
}

/**
 * Send JSON response
 */
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