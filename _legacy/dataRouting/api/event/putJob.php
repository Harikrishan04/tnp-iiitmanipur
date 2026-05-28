<?php
// Set the content type to JSON for all responses
header('Content-Type: application/json');

// Include the database configuration file.
// This file is expected to contain the $pdo object for database connection.
require_once __DIR__ . '/../../config/db.php';

try {
    // 1. Check if the request method is POST.
    // This API endpoint is designed to receive data via POST requests.
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405); // Method Not Allowed
        echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed. This endpoint only accepts POST requests.']);
        exit;
    }

    // 2. Read and decode the JSON input from the request body.
    $input = json_decode(file_get_contents('php://input'), true);

    // 3. Validate the input.
    // For an upsert operation, at least the event_organiser_id and event_title are typically required.
    // p_event_id can be null for new events, so it's not strictly required here.
    if (!$input || !isset($input['p_event_organiser_id']) || !isset($input['p_event_title'])) {
        http_response_code(400); // Bad Request
        echo json_encode(['status' => 'error', 'message' => 'Invalid JSON input or missing required parameters (p_event_organiser_id, p_event_title).']);
        exit;
    }

    // 4. Define the expected parameters for the UpsertEvent stored procedure.
    // These names must match the parameter names in your MySQL stored procedure.
    $params = [
        'p_event_id',
        'p_event_organiser_id',
        'p_event_title',
        'p_event_type',
        'p_event_description',
        'p_event_document', // This should be a JSON string
        'p_event_location',
        'p_event_status'
    ];

    // 5. Prepare the arguments array for the stored procedure call.
    // Iterate through the defined parameters and fetch their values from the input.
    // Use null if a parameter is not provided in the input JSON.
    $args = [];
    foreach ($params as $param) {
        // For JSON fields, ensure they are encoded as JSON strings if they are passed as arrays/objects in PHP.
        // If the input already provides them as JSON strings, this step is not strictly necessary.
        if (in_array($param, ['p_event_document']) && isset($input[$param]) && is_array($input[$param])) {
            $args[] = json_encode($input[$param]);
        } else {
            $args[] = $input[$param] ?? null;
        }
    }

    // 6. Prepare the SQL statement to call the stored procedure.
    // The number of placeholders (?) must match the number of parameters in the stored procedure.
    $placeholders = implode(', ', array_fill(0, count($params), '?'));
    $stmt = $pdo->prepare("CALL UpsertEvent({$placeholders})");

    // 7. Execute the prepared statement with the arguments.
    $stmt->execute($args);

    // 8. Send a success response.
    http_response_code(200); // OK
    echo json_encode(['status' => 'success', 'message' => 'Event upserted successfully.']);

} catch (PDOException $e) {
    // 9. Handle database-related errors.
    http_response_code(500); // Internal Server Error
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Throwable $e) {
    // 10. Handle any other unexpected server errors.
    http_response_code(500); // Internal Server Error
    echo json_encode(['status' => 'error', 'message' => 'Server error: ' . $e->getMessage()]);
}
?>
