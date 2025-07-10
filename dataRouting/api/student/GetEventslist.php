<?php
header('Content-Type: application/json');

require_once '../../config/db.php';
// Ensure $pdo is directly available after including db.php
// The line below assigns the return value of db.php to $pdo if it's the first inclusion
// If db.php has already been included, it will simply set $pdo to true, which is incorrect.
// The correct way is to rely on db.php to make $pdo available in the global scope.

if (!isset($pdo) || !$pdo) {
    error_log("Database connection failed in GetEventslist.php: PDO object not available.");
    echo json_encode(['status' => 'error', 'message' => 'Database connection error.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        // $pdo is already checked and should be a valid PDO object here
        $stmt = $pdo->query("SELECT
            EventId,
            EventTitle,
            EventType,
            EventStatus,
            EventLocation,
            EventDescription,
            EventDocument,
            EventStartDate,
            EventEndDate
            FROM EventDetailsView");
        $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['status' => 'success', 'events' => $events]);

    } catch (PDOException $e) {
        error_log("Database error in GetEventslist.php: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    } catch (Exception $e) {
        error_log("General error in GetEventslist.php: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'An unexpected error occurred.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
?> 