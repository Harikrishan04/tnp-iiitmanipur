<?php
/**
 * Student API Router
 * Handles all student-related API endpoints
 */

// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Get the endpoint from the URL
$requestUri = $_SERVER['REQUEST_URI'];
$path = parse_url($requestUri, PHP_URL_PATH);
$pathParts = explode('/', trim($path, '/'));

// Find the position of 'student' in the path
$studentIndex = array_search('student', $pathParts);
if ($studentIndex === false) {
    http_response_code(404);
    echo json_encode(['error' => 'Student API not found']);
    exit;
}

// Get the action after 'student' in the path
$action = $pathParts[$studentIndex + 1] ?? '';

// Route to appropriate handler
switch ($action) {
    case 'get_by_id':
        require_once 'get_by_id.php';
        break;
    case 'get_all':
        require_once 'get_all.php';
        break;
    case 'create':
        require_once 'create.php';
        break;
    case 'update':
        require_once 'update.php';
        break;
    case 'delete':
        require_once 'delete.php';
        break;
    default:
        // If no specific action, include the main handler
        require_once 'student_handler.php';
        break;
} 