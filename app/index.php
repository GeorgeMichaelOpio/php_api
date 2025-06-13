<?php
// Enable error reporting for development (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set headers for JSON response and CORS
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Get request method and path
$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path_parts = explode('/', trim($path, '/'));

// Simple routing based on query parameter or path
$action = $_GET['action'] ?? 'default';

// Sample data storage (in production, use a database)
$sample_users = [
    ['id' => 1, 'name' => 'John Doe', 'email' => 'john@example.com', 'age' => 30],
    ['id' => 2, 'name' => 'Jane Smith', 'email' => 'jane@example.com', 'age' => 25],
    ['id' => 3, 'name' => 'Bob Johnson', 'email' => 'bob@example.com', 'age' => 35],
    ['id' => 4, 'name' => 'Alice Brown', 'email' => 'alice@example.com', 'age' => 28],
    ['id' => 5, 'name' => 'Charlie Wilson', 'email' => 'charlie@example.com', 'age' => 42]
];

$sample_products = [
    ['id' => 1, 'name' => 'Laptop', 'price' => 999.99, 'category' => 'Electronics'],
    ['id' => 2, 'name' => 'Phone', 'price' => 699.99, 'category' => 'Electronics'],
    ['id' => 3, 'name' => 'Book', 'price' => 19.99, 'category' => 'Education'],
    ['id' => 4, 'name' => 'Coffee Mug', 'price' => 12.99, 'category' => 'Home'],
    ['id' => 5, 'name' => 'Headphones', 'price' => 149.99, 'category' => 'Electronics']
];

// Function to send JSON response
function sendResponse($data, $status_code = 200) {
    http_response_code($status_code);
    echo json_encode($data, JSON_PRETTY_PRINT);
    exit();
}

// Function to send error response
function sendError($message, $status_code = 400) {
    sendResponse([
        'status' => 'error',
        'message' => $message,
        'timestamp' => date('Y-m-d H:i:s')
    ], $status_code);
}

// Main API logic
try {
    switch ($method) {
        case 'GET':
            handleGetRequest($action, $sample_users, $sample_products);
            break;
        
        case 'POST':
            handlePostRequest($action);
            break;
        
        default:
            sendError("Method $method not allowed", 405);
    }
} catch (Exception $e) {
    sendError("Server error: " . $e->getMessage(), 500);
}

// Handle GET requests
function handleGetRequest($action, $users, $products) {
    switch ($action) {
        case 'default':
        case 'hello':
            sendResponse([
                'status' => 'success',
                'message' => 'Hello from PHP API!',
                'data' => [
                    'id' => 1,
                    'name' => 'Sample Item',
                    'description' => 'This is a sample response from the PHP API'
                ],
                'timestamp' => date('Y-m-d H:i:s'),
                'server_info' => [
                    'php_version' => phpversion(),
                    'server_time' => date('Y-m-d H:i:s T'),
                    'method' => 'GET'
                ]
            ]);
            break;
        
        case 'users':
            $page = (int)($_GET['page'] ?? 1);
            $limit = (int)($_GET['limit'] ?? 10);
            $offset = ($page - 1) * $limit;
            
            $paged_users = array_slice($users, $offset, $limit);
            
            sendResponse([
                'status' => 'success',
                'message' => 'Users retrieved successfully',
                'data' => $paged_users,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => count($users),
                    'total_pages' => ceil(count($users) / $limit)
                ],
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            break;
        
        case 'user':
            $id = (int)($_GET['id'] ?? 0);
            $user = array_filter($users, fn($u) => $u['id'] === $id);
            
            if (empty($user)) {
                sendError("User with ID $id not found", 404);
            }
            
            sendResponse([
                'status' => 'success',
                'message' => 'User retrieved successfully',
                'data' => array_values($user)[0],
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            break;
        
        case 'products':
            $category = $_GET['category'] ?? null;
            $filtered_products = $category ? 
                array_filter($products, fn($p) => strtolower($p['category']) === strtolower($category)) : 
                $products;
            
            sendResponse([
                'status' => 'success',
                'message' => 'Products retrieved successfully',
                'data' => array_values($filtered_products),
                'filters' => ['category' => $category],
                'total_count' => count($filtered_products),
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            break;
        
        case 'stats':
            sendResponse([
                'status' => 'success',
                'message' => 'Statistics retrieved successfully',
                'data' => [
                    'total_users' => count($users),
                    'total_products' => count($products),
                    'average_user_age' => round(array_sum(array_column($users, 'age')) / count($users), 2),
                    'average_product_price' => round(array_sum(array_column($products, 'price')) / count($products), 2),
                    'categories' => array_unique(array_column($products, 'category'))
                ],
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            break;
        
        case 'random':
            $random_data = [
                'random_number' => rand(1, 1000),
                'random_user' => $users[array_rand($users)],
                'random_product' => $products[array_rand($products)],
                'random_quote' => [
                    "Life is what happens to you while you're busy making other plans.",
                    "The future belongs to those who believe in the beauty of their dreams.",
                    "It is during our darkest moments that we must focus to see the light.",
                    "The only impossible journey is the one you never begin.",
                    "Success is not final, failure is not fatal: it is the courage to continue that counts."
                ][array_rand([0, 1, 2, 3, 4])]
            ];
            
            sendResponse([
                'status' => 'success',
                'message' => 'Random data generated successfully',
                'data' => $random_data,
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            break;
        
        case 'time':
            sendResponse([
                'status' => 'success',
                'message' => 'Server time information',
                'data' => [
                    'current_time' => date('Y-m-d H:i:s'),
                    'timezone' => date_default_timezone_get(),
                    'timestamp' => time(),
                    'iso_8601' => date('c'),
                    'day_of_week' => date('l'),
                    'day_of_year' => date('z') + 1
                ],
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            break;
        
        default:
            sendError("Unknown action: $action. Available actions: hello, users, user, products, stats, random, time", 400);
    }
}

// Handle POST requests
function handlePostRequest($action) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    switch ($action) {
        case 'echo':
            sendResponse([
                'status' => 'success',
                'message' => 'Data echoed successfully',
                'data' => [
                    'received_data' => $input,
                    'method' => 'POST',
                    'content_type' => $_SERVER['CONTENT_TYPE'] ?? 'unknown'
                ],
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            break;
        
        case 'calculate':
            $num1 = (float)($input['num1'] ?? 0);
            $num2 = (float)($input['num2'] ?? 0);
            $operation = $input['operation'] ?? 'add';
            
            $result = 0;
            switch ($operation) {
                case 'add':
                    $result = $num1 + $num2;
                    break;
                case 'subtract':
                    $result = $num1 - $num2;
                    break;
                case 'multiply':
                    $result = $num1 * $num2;
                    break;
                case 'divide':
                    if ($num2 == 0) {
                        sendError("Division by zero is not allowed", 400);
                    }
                    $result = $num1 / $num2;
                    break;
                default:
                    sendError("Unknown operation: $operation", 400);
            }
            
            sendResponse([
                'status' => 'success',
                'message' => 'Calculation completed successfully',
                'data' => [
                    'num1' => $num1,
                    'num2' => $num2,
                    'operation' => $operation,
                    'result' => $result
                ],
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            break;
        
        default:
            sendError("Unknown POST action: $action. Available actions: echo, calculate", 400);
    }
}

// If no action is specified and it's a simple GET request, show API documentation
if ($method === 'GET' && !isset($_GET['action'])) {
    sendResponse([
        'status' => 'success',
        'message' => 'Welcome to the PHP API!',
        'data' => [
            'id' => 1,
            'name' => 'Sample Item',
            'description' => 'This is the default response'
        ],
        'available_endpoints' => [
            'GET /?action=hello' => 'Default hello message',
            'GET /?action=users' => 'Get all users (supports pagination: page, limit)',
            'GET /?action=user&id=1' => 'Get specific user by ID',
            'GET /?action=products' => 'Get all products (supports category filter)',
            'GET /?action=stats' => 'Get statistics about users and products',
            'GET /?action=random' => 'Get random data',
            'GET /?action=time' => 'Get server time information',
            'POST /?action=echo' => 'Echo back the sent JSON data',
            'POST /?action=calculate' => 'Perform calculations (send: num1, num2, operation)'
        ],
        'timestamp' => date('Y-m-d H:i:s'),
        'server_info' => [
            'php_version' => phpversion(),
            'server_time' => date('Y-m-d H:i:s T')
        ]
    ]);
}
?>
