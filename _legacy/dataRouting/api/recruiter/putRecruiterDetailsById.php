<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/db.php';
$pdo = require __DIR__ . '/../../config/db.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
        exit;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid JSON input']);
        exit;
    }

    // Extract and validate all parameters
    $params = [
        'p_recruiter_id',
        'p_primary_contact_name',
        'p_primary_contact_position',
        'p_primary_contact_email',
        'p_primary_contact_phone',
        'p_primary_contact_linkedin_profile',
        'p_alt_contact_name',
        'p_alt_contact_position',
        'p_alt_contact_email',
        'p_alt_contact_phone',
        'p_alt_contact_linkedin_profile',
        'p_remark',
        'p_company_details_json'
    ];
    $args = [];
    foreach ($params as $param) {
        $args[] = $input[$param] ?? null;
    }

    $stmt = $pdo->prepare('CALL UpsertRecruiter(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->execute($args);

    echo json_encode(['status' => 'success', 'message' => 'Recruiter upserted successfully']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Server error: ' . $e->getMessage()]);
} 