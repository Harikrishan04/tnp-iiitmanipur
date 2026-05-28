<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/db.php';

try {
    // Check request method
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed. This endpoint only supports GET requests.']);
        exit;
    }

    // Get database connection
    // The require_once already executes the db.php and should return the PDO object
    $pdo = require __DIR__ . '/../../config/db.php';
    if (!$pdo) {
        throw new Exception('Database connection failed. Check db.php configuration.');
    }

    // Get recruiter_id from query parameters
    $recruiter_id = $_GET['recruiter_id'] ?? null;

    // Corrected variable name from $event_id to $recruiter_id
    if (!$recruiter_id) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'recruiter_id parameter is required.']);
        exit;
    }

    // Validate UUID format
    if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $recruiter_id)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid recruiter_id format. Please provide a valid UUID.']);
        exit;
    }

    // Prepare the SQL query to select jobs for the given OrganiserId
    // The 'Status' column was not in your view definition.
    // If you need 'Status', you'd need to join with 'verifications' again in the view
    // or fetch it separately. For now, I'm removing it as it's not in RecruiterJobsList.
    $stmt = $pdo->prepare('SELECT EventID, OrganiserId, Event,Type,Posted,Location,Status FROM RecruiterJobsList WHERE OrganiserId = ?');

    // Execute the statement with the recruiter_id
    $stmt->execute([$recruiter_id]);

    // Fetch ALL results since a recruiter can have multiple jobs
    $recruiterJobs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Close the statement to free up resources
    $stmt->closeCursor();

    if (empty($recruiterJobs)) {
        // Return 200 OK with an empty array if no jobs are found, which is a common practice
        // Or you could return 404 if you strictly mean the *recruiter* was not found
        // For a list of jobs, an empty list is often success.
        http_response_code(200);
        echo json_encode(['status' => 'success', 'message' => 'No jobs found for this recruiter.', 'jobs' => []]);
        exit;
    }

    echo json_encode([
        'status' => 'success',
        'message' => 'Jobs retrieved successfully.',
        'jobs' => $recruiterJobs // Changed 'recruiter' to 'jobs' for clarity as it's a list
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    // Log the error for debugging, but don't expose too much detail in production
    error_log('Database error: ' . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'A database error occurred. Please try again later.']);
} catch (Exception $e) {
    http_response_code(500);
    // Log other unexpected errors
    error_log('Server error: ' . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'An unexpected server error occurred.']);
}
?>