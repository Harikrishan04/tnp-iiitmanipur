<?php
/**
 * Update Recruiter Verification Status API
 * TNP Portal - IIIT Manipur
 * 
 * Updates the verification status of a recruiter in the verifications table
 * Status options: verified, rejected, resubmit, blocked
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Only allow POST method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

// Database connection
$pdo = require_once '../../config/db.php';

if (!$pdo) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database connection failed'
    ]);
    exit;
}

try {
    
    // Get POST data
    $recruiter_id = $_POST['recruiter_id'] ?? '';
    $status = $_POST['status'] ?? '';
    $remark = $_POST['remark'] ?? '';
    $coordinator_id = $_POST['coordinator_id'] ?? '';
    
    // Validate required fields
    if (empty($recruiter_id) || empty($status) || empty($remark)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Missing required fields: recruiter_id, status, and remark are required'
        ]);
        exit;
    }
    
    // Validate status values
    $valid_statuses = ['verified', 'rejected', 'resubmit', 'blocked'];
    if (!in_array($status, $valid_statuses)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid status. Must be one of: ' . implode(', ', $valid_statuses)
        ]);
        exit;
    }
    
    // Check if recruiter exists
    $check_recruiter = $pdo->prepare("SELECT recruiter_id FROM recruiters WHERE recruiter_id = ?");
    $check_recruiter->execute([$recruiter_id]);
    
    if ($check_recruiter->rowCount() === 0) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Recruiter not found'
        ]);
        exit;
    }
    
    // Update verification status
    $update_query = "UPDATE verifications 
                     SET status = ?, 
                         notes = ?, 
                         verified_by_user_id = ?, 
                         verified_on = NOW(), 
                         updated_at = NOW()
                     WHERE verified_entity_id = ? 
                     AND verified_entity_type = 'recruiter'";
    
    $update_stmt = $pdo->prepare($update_query);
    $update_result = $update_stmt->execute([$status, $remark, $coordinator_id, $recruiter_id]);
    
    if ($update_result) {
        // Check if any rows were affected
        if ($update_stmt->rowCount() > 0) {
            echo json_encode([
                'status' => 'success',
                'message' => "Recruiter status updated to '$status' successfully",
                'data' => [
                    'recruiter_id' => $recruiter_id,
                    'new_status' => $status,
                    'remark' => $remark,
                    'verified_by' => $coordinator_id,
                    'verified_on' => date('Y-m-d H:i:s')
                ]
            ]);
        } else {
            // No rows affected - verification record might not exist, create one
            $insert_query = "INSERT INTO verifications 
                            (verified_entity_id, verified_entity_type, verified_by_user_id, verified_on, status, notes, created_at, updated_at)
                            VALUES (?, 'recruiter', ?, NOW(), ?, ?, NOW(), NOW())";
            
            $insert_stmt = $pdo->prepare($insert_query);
            $insert_result = $insert_stmt->execute([$recruiter_id, $coordinator_id, $status, $remark]);
            
            if ($insert_result) {
                echo json_encode([
                    'status' => 'success',
                    'message' => "Recruiter verification record created and status set to '$status' successfully",
                    'data' => [
                        'recruiter_id' => $recruiter_id,
                        'new_status' => $status,
                        'remark' => $remark,
                        'verified_by' => $coordinator_id,
                        'verified_on' => date('Y-m-d H:i:s')
                    ]
                ]);
            } else {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Failed to create verification record'
                ]);
            }
        }
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to update recruiter status'
        ]);
    }
    
} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}
?>
