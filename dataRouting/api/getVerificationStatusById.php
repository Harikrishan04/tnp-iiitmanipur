<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');

// Include database connection
$pdo = require_once __DIR__ . '/../config/db.php';

// Validate PDO instance
if (!($pdo instanceof PDO)) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Failed to establish database connection']);
    exit;
}

/**
 * Fetch verification status by verified_entity_id from VerificationStatus view
 * @param PDO $pdo
 * @param string $verified_entity_id
 * @return array|null
 */
function getVerificationStatusById($pdo, $verified_entity_id) {
    // Validate UUID format
    if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $verified_entity_id)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid recruiter_id format']);
        exit;
    }

    // Query the VerificationStatus view
    $stmt = $pdo->prepare('
        SELECT status, verified_on, notes
        FROM VerificationStatus
        WHERE verified_entity_id = ?
        LIMIT 1
    ');
    $stmt->execute([$verified_entity_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $stmt->closeCursor();
    
    return $result;
}

try {
    // Validate request method
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
        exit;
    }

    // Get and validate recruiter_id (mapped to verified_entity_id)
    $verified_entity_id = $_GET['verification_id'] ?? null;
    if (!$verified_entity_id) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'verification_id parameter is required']);
        exit;
    }

    // Fetch verification status from view
    $status = getVerificationStatusById($pdo, $verified_entity_id);

    // Check if record exists
    if (!$status) {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Verification record not found for this recruiter']);
        exit;
    }

    // Return response matching frontend expectations
    echo json_encode([
        'status' => 'success',
        'verificationStatus' => $status['status'] ?? '',
        'verified_on' => $status['verified_on'] ?? null,
        'notes' => $status['notes'] ?? null,
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Server error: ' . $e->getMessage()]);
}