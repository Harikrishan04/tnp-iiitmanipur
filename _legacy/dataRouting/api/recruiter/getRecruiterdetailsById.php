<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/db.php';

try {
    // Check request method
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
        exit;
    }

    // Get database connection
    $pdo = require __DIR__ . '/../../config/db.php';
    if (!$pdo) {
        throw new Exception('Database connection failed');
    }

    // Get recruiter_id from query parameters
    $recruiter_id = $_GET['recruiter_id'] ?? null;
    
    if (!$recruiter_id) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'recruiter_id parameter is required']);
        exit;
    }

    // Validate UUID format
    if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $recruiter_id)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid recruiter_id format']);
        exit;
    }

    // Call the stored procedure
    $stmt = $pdo->prepare('SELECT * FROM RecruiterList WHERE recruiterId = ?');
    $stmt->execute([$recruiter_id]);
    $recruiter = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Close the statement to free up resources
    $stmt->closeCursor();

    if (!$recruiter) {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Recruiter not found']);
        exit;
    }

    echo json_encode([
        'status' => 'success', 
        'recruiter' => $recruiter
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Server error: ' . $e->getMessage()]);
} 