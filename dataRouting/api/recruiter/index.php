<?php
// dataRouting/api/recruiter/index.php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../utils/DatabaseUtils.php';

$pdo = require __DIR__ . '/../../config/db.php';

function getRecruiterList($pdo) {
    $stmt = $pdo->query('CALL GetRecruiterList()');
    $recruiters = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $recruiters;
}

function getRecruiterById($pdo, $id) {
    if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $id)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid recruiter_id format']);
        exit;
    }
    $stmt = $pdo->prepare('CALL GetRecruiterDetailsById(?)');
    $stmt->execute([$id]);
    $recruiter = $stmt->fetch(PDO::FETCH_ASSOC);
    $stmt->closeCursor();
    return $recruiter;
}

function upsertRecruiter($pdo, $input) {
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
    return true;
}

function updateRecruiterStatus($pdo, $id, $status) {
    // You may want to use a dedicated stored procedure for status update
    $stmt = $pdo->prepare('UPDATE recruiters SET status = ?, updated_at = NOW() WHERE recruiterId = ?');
    $stmt->execute([$status, $id]);
    return $stmt->rowCount() > 0;
}

try {
    $method = $_SERVER['REQUEST_METHOD'];
    $id = $_GET['id'] ?? null;
    
    if ($method === 'GET') {
        if ($id) {
            $recruiter = getRecruiterById($pdo, $id);
            if ($recruiter) {
                echo json_encode(['status' => 'success', 'recruiter' => $recruiter]);
            } else {
                http_response_code(404);
                echo json_encode(['status' => 'error', 'message' => 'Recruiter not found']);
            }
        } else {
            $recruiters = getRecruiterList($pdo);
            echo json_encode(['status' => 'success', 'recruiters' => $recruiters]);
        }
        exit;
    }
    
    if ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Invalid JSON input']);
            exit;
        }
        upsertRecruiter($pdo, $input);
        echo json_encode(['status' => 'success', 'message' => 'Recruiter upserted successfully']);
        exit;
    }
    
    if ($method === 'PATCH') {
        if (!$id) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Missing recruiter id']);
            exit;
        }
        $input = json_decode(file_get_contents('php://input'), true);
        $status = $input['status'] ?? null;
        if (!$status) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Missing status']);
            exit;
        }
        if (updateRecruiterStatus($pdo, $id, $status)) {
            echo json_encode(['status' => 'success', 'message' => 'Status updated']);
        } else {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Recruiter not found or status unchanged']);
        }
        exit;
    }

    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Server error: ' . $e->getMessage()]);
} 