<?php
/**
 * API Endpoint: Get Participant Applications
 * Fetches data from ParticipantDetailsView for display in the coordinator dashboard.
 * TNP Portal - IIIT Manipur
 */

header('Content-Type: application/json');
session_start();

// Basic security check: Only allow if user is logged in and is a coordinator
// Uncomment and adjust for your actual login logic
// if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'coordinator') {
//     echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
//     exit();
// }

// Include database connection
// Changed path and method to match the provided snippet
require_once __DIR__ . '/../../config/db.php';
$pdo = require __DIR__ . '/../../config/db.php';

try {
    // Check for GET request method
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
        exit;
    }

    // Fetch data from the EventsRecruiterDetails view
    // Changed query and fetched data key to match the provided snippet
    $stmt = $pdo->query('SELECT * FROM StudentEventApplicationsView');
    $Participant = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Changed response key from 'applications' to 'Participant'
    echo json_encode(['status' => 'success', 'Participant' => $Participant]);

} catch (PDOException $e) {
    // Set HTTP response code for database errors
    http_response_code(500);
    // Log the error for debugging (e.g., to a file or error monitoring service)
    error_log("Database error in GetParticipantApplications.php: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Throwable $e) {
    // Set HTTP response code for general server errors
    http_response_code(500);
    error_log("General error in GetParticipantApplications.php: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Server error: ' . $e->getMessage()]);
}
?>
