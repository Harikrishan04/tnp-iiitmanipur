<?php
declare(strict_types=1);
header('Content-Type: application/json');
session_start();

$logFile = __DIR__ . '/../../logs/student_api.log';
if (!is_dir(dirname($logFile))) {
    mkdir(dirname($logFile), 0777, true);
}

function studentApiLog($msg) {
    global $logFile;
    $time = date('Y-m-d H:i:s');
    if (is_array($msg) || is_object($msg)) {
        $msg = print_r($msg, true);
    }
    file_put_contents($logFile, "[{$time}] {$msg}" . PHP_EOL, FILE_APPEND | LOCK_EX);
}

require_once __DIR__ . '/../../config/db.php';
$pdo = require __DIR__ . '/../../config/db.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'PUT' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
        studentApiLog('Method Not Allowed: ' . $_SERVER['REQUEST_METHOD']);
        exit;
    }

    // if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['student', 'coordinator', 'admin'])) {
    //     http_response_code(401);
    //     echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    //     studentApiLog('Unauthorized access');
    //     exit;
    // }

    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid JSON input']);
        studentApiLog('Invalid JSON input');
        exit;
    }

    $studentId = $input['student_id'] ?? '';
    if (!$studentId) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Missing student_id']);
        studentApiLog('Missing student_id');
        exit;
    }

    // Validate UUID format
    if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $studentId)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid student_id format']);
        studentApiLog('Invalid student_id format: ' . $studentId);
        exit;
    }

    // Check permissions
    // if ($_SESSION['user_role'] === 'student') {
    //     // Students can only update their own data
    //     if ($_SESSION['user_id'] !== $studentId) {
    //         http_response_code(403);
    //         echo json_encode(['status' => 'error', 'message' => 'Access denied']);
    //         studentApiLog('Access denied: student ' . $_SESSION['user_id'] . ' tried to update ' . $studentId);
    //         exit;
    //     }
    // }

    // Call the stored procedure to update student data
    $stmt = $pdo->prepare("CALL UpdateStudentById(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $result = $stmt->execute([
        $studentId,
        $input['roll_no'] ?? null,
        $input['name'] ?? null,
        $input['category'] ?? null,
        $input['date_of_birth'] ?? null,
        $input['gender'] ?? null,
        $input['blood_group'] ?? null,
        $input['phone_number'] ?? null,
        $input['locality'] ?? null,
        $input['city'] ?? null,
        $input['state'] ?? null,
        $input['country'] ?? null,
        $input['pincode'] ?? null,
        $input['program'] ?? null,
        $input['department'] ?? null,
        $input['current_semester'] ?? null,
        $input['cpi'] ?? null,
        $input['year_of_admission'] ?? null,
        $input['year_of_passing'] ?? null,
        $input['placement_interest'] ?? null,
        $input['comments'] ?? null,
        isset($input['personal_details_json']) ? json_encode($input['personal_details_json']) : null,
        isset($input['education_details_json']) ? json_encode($input['education_details_json']) : null,
        isset($input['experiences_json']) ? json_encode($input['experiences_json']) : null,
        isset($input['additional_details_json']) ? json_encode($input['additional_details_json']) : null,
        isset($input['documents_json']) ? json_encode($input['documents_json']) : null
    ]);

    if ($result) {
        $rowCount = $stmt->rowCount();
        if ($rowCount > 0) {
            studentApiLog("Student updated: $studentId");
            echo json_encode([
                'status' => 'success', 
                'message' => 'Student updated successfully',
                'rows_affected' => $rowCount
            ]);
        } else {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Student not found or no changes made']);
            studentApiLog("Student not found or no changes: $studentId");
        }
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Failed to update student']);
        studentApiLog('Failed to update student: ' . $studentId);
    }

} catch (PDOException $e) {
    studentApiLog('PDOException: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Throwable $e) {
    studentApiLog('General Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Server error: ' . $e->getMessage()]);
} 