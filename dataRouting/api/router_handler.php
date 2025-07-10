<?php
// api/router_handler.php
declare(strict_types=1);

require_once '../config/db.php';
require_once '../utils/auth_utils.php';
require_once '../utils/Router.php';

/**
 * Get current user from session or token
 * @param PDO $pdo Database connection
 * @return array|false User data or false if not authenticated
 */
function getCurrentUser(PDO $pdo) {
    // Check for session token
    if (isset($_COOKIE['session_token'])) {
        $tokenData = AuthUtils::parseSessionToken($_COOKIE['session_token']);
        
        if ($tokenData && !AuthUtils::isTokenExpired($tokenData['timestamp'])) {
            // Get user data from database
            try {
                $stmt = $pdo->prepare("
                    SELECT u.user_id, u.email, u.user_name, u.role_id, r.name as role_name
                    FROM users u
                    JOIN roles r ON u.role_id = r.id
                    WHERE u.email = ? AND u.is_active = 1
                ");
                $stmt->execute([$tokenData['email']]);
                return $stmt->fetch(PDO::FETCH_ASSOC);
            } catch (Exception $e) {
                error_log("Error getting user data: " . $e->getMessage());
                return false;
            }
        }
    }
    
    return false;
}

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    $pdo = require '../config/db.php';
    if (!$pdo) {
        throw new Exception('Database connection failed');
    }
    
    // Get current user from session or token
    $user = getCurrentUser($pdo);
    
    if (!$user) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized', 'redirect' => '/login']);
        exit;
    }
    
    $router = new Router();
    $router->setUser($user, $pdo);
    
    $action = $_GET['action'] ?? 'navigation';
    
    switch ($action) {
        case 'navigation':
            $activeRoute = $_GET['route'] ?? '';
            $menuType = $_GET['type'] ?? 'nav'; // nav, sidebar, breadcrumb
            
            switch ($menuType) {
                case 'sidebar':
                    $html = $router->generateSidebarMenu($activeRoute);
                    break;
                case 'breadcrumb':
                    $html = $router->generateBreadcrumb($activeRoute);
                    break;
                default:
                    $html = $router->generateNavigationMenu($activeRoute);
            }
            
            echo json_encode([
                'success' => true,
                'html' => $html,
                'user' => [
                    'name' => $user['user_name'] ?? '',
                    'role' => $router->getUserRole(),
                    'email' => $user['email'] ?? ''
                ]
            ]);
            break;
            
        case 'routes':
            $accessibleRoutes = $router->getAccessibleRoutes();
            echo json_encode([
                'success' => true,
                'routes' => $accessibleRoutes,
                'user_role' => $router->getUserRole()
            ]);
            break;
            
        case 'check_access':
            $routeName = $_GET['route'] ?? '';
            if (empty($routeName)) {
                http_response_code(400);
                echo json_encode(['error' => 'Route name required']);
                exit;
            }
            
            $canAccess = $router->canAccessRoute($routeName);
            echo json_encode([
                'success' => true,
                'can_access' => $canAccess,
                'route' => $routeName
            ]);
            break;
            
        case 'permissions':
            $permissions = $router->getUserPermissions();
            echo json_encode([
                'success' => true,
                'permissions' => $permissions,
                'user_role' => $router->getUserRole()
            ]);
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Invalid action']);
    }
    
} catch (Exception $e) {
    error_log("Router handler error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
} 