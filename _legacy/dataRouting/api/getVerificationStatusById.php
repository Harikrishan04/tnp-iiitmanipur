<?php
header('Content-Type: application/json');

$pdo = require_once __DIR__ . '/../config/db.php';
if (!$pdo) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed.']);
    exit;
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed. This endpoint only supports GET requests.']);
        exit;
    }

    // Get verified_entity_id from query parameters (named 'verification_id' in the request)
    $verified_entity_id = $_GET['verification_id'] ?? null;

    // Validate if the parameter is provided
    if (!$verified_entity_id) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'The "verification_id" parameter (representing the entity ID to be verified) is required.']);
        exit;
    }

    // Validate UUID format
    if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $verified_entity_id)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid "verification_id" format. Please provide a valid UUID.']);
        exit;
    }

    // Prepare the SQL query to select a single verification record for the given entity ID
    $stmt = $pdo->prepare('SELECT status, verified_on, notes FROM verifications WHERE verified_entity_id = ? LIMIT 1');

    // Execute the statement with the verified_entity_id
    $stmt->execute([$verified_entity_id]);

    // Fetch the single result
    $verificationRecord = $stmt->fetch(PDO::FETCH_ASSOC);

    // Close the statement to free up resources
    $stmt->closeCursor();

    if (!$verificationRecord) {
        // If no verification record is found for the given entity ID, return 404 Not Found
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'No verification record found for the provided entity ID.']);
        exit;
    }

    // Return the successful response with the verification details
    http_response_code(200); // Explicitly set 200 OK for success
    echo json_encode([
        'status' => 'success',
        'message' => 'Verification record retrieved successfully.',
        'data' => $verificationRecord // Using 'data' for the single object
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    // Log the error for debugging, but don't expose too much detail in production
    error_log('Database error in get_verification_details.php: ' . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'A database error occurred. Please try again later.']);
} catch (Exception $e) {
    http_response_code(500);
    // Log other unexpected errors
    error_log('Server error in get_verification_details.php: ' . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'An unexpected server error occurred.']);
}
?>