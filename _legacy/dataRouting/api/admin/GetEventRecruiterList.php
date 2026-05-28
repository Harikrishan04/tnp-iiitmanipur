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

    $stmt = $pdo->query('SELECT EventId, EventTitle, EventType, EventLocation, VerificationStatus, PrimaryContactName,MaxParticipants,EventStatus FROM EventsRecruiterDetails');
    $recruiters = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['status' => 'success', 'recruiters' => $recruiters]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Server error: ' . $e->getMessage()]);
} 