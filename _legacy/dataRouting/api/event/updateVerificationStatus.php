<?php
/**
 * Update Event Verification Status API
 * TNP Portal - IIIT Manipur
 *
 * Updates the verification status of an event in the verifications table.
 * Status options: verified, rejected, resubmit, blocked.
 * This endpoint allows updating the status and an associated remark.
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Consider restricting this in production
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Only allow POST method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed.']);
    exit;
}

// Database connection
$pdo = require_once '../../config/db.php';

if (!$pdo) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database connection failed.'
    ]);
    exit;
}

try {
    // Get POST data
    $event_id = $_POST['event_id'] ?? '';
    // Retrieve status and remark directly from the POST request
    $status = 'pending';
    $remark =  "Thanks for posting your job with us! Your submission is currently under review by our team. We're working to approve it as quickly as possible, and it should be live shortly.

We appreciate your patience!
";

    
    // Validate required fields
    if (empty($event_id) || empty($status) || empty($remark)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Missing required fields: event_id, status, and remark are all necessary.'
        ]);
        exit;
    }

    // Validate status values against allowed options
    // $valid_statuses = ['verified', 'rejected', 'resubmit', 'blocked'];
    // // If 'pending' can also be a status set by this update endpoint, add it to $valid_statuses.
    // // For example: $valid_statuses = ['pending', 'verified', 'rejected', 'resubmit', 'blocked'];
    // if (!in_array($status, $valid_statuses)) {
    //     echo json_encode([
    //         'status' => 'error',
    //         'message' => 'Invalid status provided. Status must be one of: ' . implode(', ', $valid_statuses) . '.'
    //     ]);
    //     exit;
    // }

    // Check if the event exists before attempting to update its verification status
    $check_event_stmt = $pdo->prepare("SELECT event_id FROM events WHERE event_id = ?");
    $check_event_stmt->execute([$event_id]);

    if ($check_event_stmt->rowCount() === 0) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Event not found with the provided event_id.'
        ]);
        exit;
    }

    // Attempt to update the existing verification record for the event
    $update_query = "UPDATE verifications
                     SET status = ?,
                         notes = ?,
                         updated_at = NOW()
                     WHERE verified_entity_id = ?
                     AND verified_entity_type = 'event'";

    $update_stmt = $pdo->prepare($update_query);
    // Corrected parameters for the UPDATE statement
    $update_result = $update_stmt->execute([$status, $remark, $event_id]);

    if ($update_result) {
        // If an existing verification record was updated
        if ($update_stmt->rowCount() > 0) {
            echo json_encode([
                'status' => 'success',
                'message' => "Event verification status updated to '$status' with the provided remark.",
                'data' => [
                    'event_id' => $event_id,
                    'new_status' => $status,
                    'remark' => $remark
                ]
            ]);
        } else {
            // No rows were affected by UPDATE, meaning no existing verification record for this event.
            // In this case, insert a new verification record.
            $insert_query = "INSERT INTO verifications
                            (verified_entity_id, verified_entity_type, verified_by_user_id, verified_on, status, notes, created_at, updated_at)
                            VALUES (?, 'event', ?, NOW(), ?, ?, NOW(), NOW())";

            $insert_stmt = $pdo->prepare($insert_query);
            $insert_result = $insert_stmt->execute([$event_id, $coordinator_id, $status, $remark]);

            if ($insert_result) {
                echo json_encode([
                    'status' => 'success',
                    'message' => "New event verification record created with status '$status' and remark.",
                    'data' => [
                        'event_id' => $event_id,
                        'new_status' => $status,
                        'remark' => $remark,
                        'verified_by' => $coordinator_id,
                        'verified_on' => date('Y-m-d H:i:s')
                    ]
                ]);
            } else {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Failed to create a new verification record for the event.'
                ]);
            }
        }
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to perform the update or insert operation for event verification.'
        ]);
    }

} catch (PDOException $e) {
    // Catch database-related errors
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    // Catch any other unexpected errors
    echo json_encode([
        'status' => 'error',
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}
?>