<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/db.php';
$pdo = require __DIR__ . '/../../config/db.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
        exit;
    }

    // Get event_id from query parameters
    $event_id = $_GET['event_id'] ?? null;
    
    if (!$event_id) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'event_id parameter is required']);
        exit;
    }

    // Validate UUID format (assuming EventID is UUID)
    if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $event_id)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid event_id format']);
        exit;
    }

    $stmt = $pdo->prepare('SELECT * FROM EventsRecruiterDetails WHERE EventId = ?');
    $stmt->execute([$event_id]);
    $eventDetails = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Close the statement to free up resources
    $stmt->closeCursor();

    if (!$eventDetails) {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Event not found']);
        exit;
    }

    echo json_encode([
        'status' => 'success', 
        'event' => $eventDetails // Changed key to 'event' to reflect data
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Server error: ' . $e->getMessage()]);
} 