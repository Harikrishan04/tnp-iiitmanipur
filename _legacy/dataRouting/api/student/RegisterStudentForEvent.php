<?php
session_start();
header('Content-Type: application/json');

$pdo = require_once __DIR__ . '/../../config/db.php';
if (!$pdo) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed.']);
    exit;
}

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'student') {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized. Please log in as a student.']);
    exit;
}

$participant_id = $_SESSION['user_id'];

// Get JSON input from the request body
$input = json_decode(file_get_contents('php://input'), true);

// Validate input
if (!isset($input['event_id'], $input['status'], $input['document'])) {
    echo json_encode(['status' => 'error', 'message' => 'Missing required data.']);
    exit();
}

$event_id = $input['event_id'];
// The initial status will be 'registered' upon first application
// However, the procedure parameter requires a status, so we can send 'registered' or 'pending'
// based on your exact flow, but 'registered' is usually the default.
$status = $input['status']; // This should be 'registered' for initial application
$document = $input['document'];
$message = $input['message'] ?? NULL; // Message is optional

try {
    // Prepare the stored procedure call
    $stmt = $pdo->prepare("CALL ManageParticipantEntry(:participant_id, :event_id, :status, :message, :document)");

    // Bind parameters
    $stmt->bindParam(':participant_id', $participant_id, PDO::PARAM_STR);
    $stmt->bindParam(':event_id', $event_id, PDO::PARAM_STR);
    $stmt->bindParam(':status', $status, PDO::PARAM_STR);
    $stmt->bindParam(':message', $message, PDO::PARAM_STR);
    $stmt->bindParam(':document', $document, PDO::PARAM_STR);

    // Execute the procedure
    $stmt->execute();

    // Fetch the result message from the procedure
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    // If the procedure signals an error, catch it
    if ($result && isset($result['Message']) && strpos($result['Message'], 'Error') !== false) {
         echo json_encode(['status' => 'error', 'message' => $result['Message']]);
    } else {
        echo json_encode(['status' => 'success', 'message' => $result['Message'] ?? 'Application processed successfully.']);
    }

} catch (PDOException $e) {
    // Log the error for debugging
    error_log("Database Error in RegisterStudentForEvent.php: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Database operation failed: ' . $e->getMessage()]);
}
?>