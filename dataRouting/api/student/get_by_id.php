<?php
/**
 * Get Student By ID API Endpoint
 * Handles GET requests to retrieve student information by ID
 */

declare(strict_types=1);

require_once '../../config/db.php';
require_once '../../utils/auth_utils.php';

/**
 * Logger class for API operations
 */
class ApiLogger {
    private $logDir;
    
    public function __construct() {
        $this->logDir = __DIR__ . '/../../logs';
        if (!is_dir($this->logDir)) {
            mkdir($this->logDir, 0755, true);
        }
    }
    
    /**
     * Log API operation
     * @param string $operation Operation name
     * @param array $data Request/Response data
     * @param string $level Log level (INFO, ERROR, WARNING)
     * @param string $userId User ID performing the operation
     */
    public function log($operation, $data = [], $level = 'INFO', $userId = null) {
        $timestamp = date('Y-m-d H:i:s');
        $logEntry = [
            'timestamp' => $timestamp,
            'level' => $level,
            'operation' => $operation,
            'user_id' => $userId,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'request_method' => $_SERVER['REQUEST_METHOD'] ?? 'unknown',
            'request_uri' => $_SERVER['REQUEST_URI'] ?? 'unknown',
            'data' => $data
        ];
        
        $logFile = $this->logDir . '/api_' . date('Y-m-d') . '.log';
        $logLine = json_encode($logEntry) . "\n";
        
        file_put_contents($logFile, $logLine, FILE_APPEND | LOCK_EX);
    }
}

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
                    SELECT u.user_id, u.user_email, u.user_name, u.role_id, r.name as role_name
                    FROM users u
                    JOIN roles r ON u.role_id = r.id
                    WHERE u.user_email = ? AND u.is_active = 1
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

/**
 * Validate UUID format
 * @param string $uuid UUID to validate
 * @return bool True if valid UUID
 */
function isValidUUID($uuid) {
    return preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $uuid);
}

// Initialize logger
$logger = new ApiLogger();

try {
    $pdo = require '../../config/db.php';
    if (!$pdo) {
        throw new Exception('Database connection failed');
    }
    
    // Get current user from session or token
    $user = getCurrentUser($pdo);
    
    if (!$user) {
        $logger->log('UNAUTHORIZED_ACCESS', [
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ], 'WARNING');
        
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized', 'redirect' => '/login']);
        exit;
    }
    
    // Log the API access
    $logger->log('API_ACCESS', [
        'endpoint' => 'student/get_by_id',
        'user_id' => $user['user_id'],
        'user_role' => $user['role_name']
    ], 'INFO', $user['user_id']);
    
    // Validate request method
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        $logger->log('INVALID_METHOD', [
            'method' => $_SERVER['REQUEST_METHOD'],
            'expected' => 'GET'
        ], 'WARNING', $user['user_id']);
        
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        exit;
    }
    
    // Get student ID from request
    $studentId = $_GET['student_id'] ?? '';
    
    if (empty($studentId)) {
        $logger->log('MISSING_STUDENT_ID', [
            'user_id' => $user['user_id']
        ], 'WARNING', $user['user_id']);
        
        http_response_code(400);
        echo json_encode(['error' => 'Student ID is required']);
        exit;
    }
    
    // Validate UUID format
    if (!isValidUUID($studentId)) {
        $logger->log('INVALID_STUDENT_ID_FORMAT', [
            'student_id' => $studentId,
            'user_id' => $user['user_id']
        ], 'WARNING', $user['user_id']);
        
        http_response_code(400);
        echo json_encode(['error' => 'Invalid student ID format']);
        exit;
    }
    
    // Check if user has permission to access this student
    // For now, allow access if user is admin, coordinator, or the student themselves
    $canAccess = false;
    
    if ($user['role_name'] === 'admin' || $user['role_name'] === 'coordinator') {
        $canAccess = true;
    } elseif ($user['role_name'] === 'student' && $user['user_id'] === $studentId) {
        $canAccess = true;
    }
    
    if (!$canAccess) {
        $logger->log('ACCESS_DENIED', [
            'user_id' => $user['user_id'],
            'user_role' => $user['role_name'],
            'requested_student_id' => $studentId
        ], 'WARNING', $user['user_id']);
        
        http_response_code(403);
        echo json_encode(['error' => 'Access denied']);
        exit;
    }
    
    try {
        // Call the stored procedure
        $stmt = $pdo->prepare("CALL GetStudentById(?)");
        $stmt->execute([$studentId]);
        $student = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$student) {
            $logger->log('STUDENT_NOT_FOUND', [
                'student_id' => $studentId,
                'user_id' => $user['user_id']
            ], 'INFO', $user['user_id']);
            
            http_response_code(404);
            echo json_encode(['error' => 'Student not found']);
            exit;
        }
        
        // Log successful retrieval
        $logger->log('STUDENT_RETRIEVED', [
            'student_id' => $studentId,
            'student_name' => $student['name'] ?? 'unknown',
            'user_id' => $user['user_id']
        ], 'INFO', $user['user_id']);
        
        // Return student data
        echo json_encode([
            'success' => true,
            'data' => $student
        ]);
        
    } catch (Exception $e) {
        $logger->log('DATABASE_ERROR', [
            'error' => $e->getMessage(),
            'student_id' => $studentId,
            'user_id' => $user['user_id']
        ], 'ERROR', $user['user_id']);
        
        http_response_code(500);
        echo json_encode(['error' => 'Database error occurred']);
        exit;
    }
    
} catch (Exception $e) {
    $logger->log('SYSTEM_ERROR', [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ], 'ERROR');
    
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
} 