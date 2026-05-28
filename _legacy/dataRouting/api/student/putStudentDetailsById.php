<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/db.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
        exit;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input || !isset($input['p_student_id'])) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid JSON input or missing student_id']);
        exit;
    }

    // Extract and validate parameters
    $params = [
        'p_student_id',
        'p_roll_no',
        'p_name',
        'p_category',
        'p_date_of_birth',
        'p_gender',
        'p_blood_group',
        'p_phone_number',
        'p_locality',
        'p_city',
        'p_state',
        'p_country',
        'p_pincode',
        'p_program',
        'p_department',
        'p_current_semester',
        'p_cpi',
        'p_year_of_admission',
        'p_year_of_passing',
        'p_placement_interest',
        'p_comments',
        'p_personal_details_json',
        'p_education_details_json',
        'p_experiences_json',
        'p_skills_json',
        'p_documents_json'
    ];
    $args = [];
    foreach ($params as $param) {
        $args[] = $input[$param] ?? null;
    }

    // Prepare and execute the stored procedure
    $stmt = $pdo->prepare('CALL UpdateStudentById(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->execute($args);

    echo json_encode(['status' => 'success', 'message' => 'Student updated successfully']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Server error: ' . $e->getMessage()]);
}
?>
