<?php
header('Content-Type: application/json');

require_once '../../config/db.php';
// $pdo is now directly available from db.php after the above require_once
require_once '../../utils/auth_utils.php';

// Check if $pdo is set from db.php
if (!isset($pdo) || !$pdo) {
    error_log("Database connection failed in ManageEventById.php: PDO object not available.");
    echo json_encode(['status' => 'error', 'message' => 'Database connection error.']);
    exit();
}

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method. Only POST is allowed.']);
    exit();
}

// Get raw POST data
$input = file_get_contents('php://input');
error_log("Raw POST data: " . $input); // Log raw input

$data = json_decode($input, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    error_log("JSON Decode Error: " . json_last_error_msg());
    echo json_encode(['status' => 'error', 'message' => 'Invalid JSON received.']);
    exit();
}

error_log("Decoded POST data: " . print_r($data, true)); // Log decoded data

// Validate and assign input data
$eventId = $data['event_id'] ?? null;
$eventStartDate = $data['start_datetime'] ?? null;
$eventEndDate = $data['end_datetime'] ?? null;
$eventStatus = $data['event_status'] ?? null;
$maxParticipants = $data['max_applications'] ?? null;

// Strict validation for truly required fields
if (empty($eventId)) {
    echo json_encode(['status' => 'error', 'message' => 'Missing or empty event_id.']);
    exit();
}
if (empty($eventStatus)) {
    echo json_encode(['status' => 'error', 'message' => 'Missing or empty event_status.']);
    exit();
}

// Convert empty strings to null for database if they were sent as empty strings
// The frontend should ideally send null directly, but this adds robustness
$eventStartDate = ($eventStartDate === '') ? null : $eventStartDate;
$eventEndDate = ($eventEndDate === '') ? null : $eventEndDate;
$maxParticipants = ($maxParticipants === '') ? null : $maxParticipants;

try {
    // The $pdo variable is already the PDO object from db.php
    $stmt = $pdo->prepare("CALL ManageEventById(?, ?, ?, ?, ?)");
    $stmt->execute([$eventId, $eventStartDate, $eventEndDate, $eventStatus, $maxParticipants]);

    echo json_encode(['status' => 'success', 'message' => 'Event updated successfully.']);

} catch (PDOException $e) {
    if ($e->getCode() === '45000') {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    } else {
        error_log("Database error in ManageEventById.php: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
} catch (Exception $e) {
    error_log("General error in ManageEventById.php: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'An unexpected error occurred.']);
}

?> 