<?php
header('Content-Type: application/json');

$pdo = require_once __DIR__ . '/../../config/db.php';
if (!$pdo) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed.']);
    exit;
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
        exit;
    }

    $event_id = isset($_GET['event_id']) ? trim((string) $_GET['event_id']) : '';
    if ($event_id === '') {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Event ID is required.']);
        exit;
    }
    if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $event_id)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid event_id format.']);
        exit;
    }

    // 2. Prepare the SQL statement with a placeholder
    // Assuming 'event_specific_participants' is the VIEW you created earlier
    $stmt = $pdo->prepare('SELECT * FROM eventParticipantsList WHERE event_id = ?');

    // 3. Execute the statement, passing the event_id as a parameter
    $stmt->execute([$event_id]);

    // 4. Fetch all results
    $EventParticipantsList = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['status' => 'success', 'participantsList' => $EventParticipantsList]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Server error: ' . $e->getMessage()]);
}

?>