<?php
// Ensure this path is correct for your project structure
$pdo = require __DIR__ . '/../../config/db.php'; 

// Set content type to JSON
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Check if an ID is provided for fetching a single coordinator
        if (isset($_GET['id'])) {
            fetchCoordinatorById($_GET['id']);
        } else {
            fetchAllCoordinators();
        }
        break;

    case 'POST':
        upsertCoordinator();
        break;

    case 'DELETE':
        deleteCoordinator();
        break;

    default:
        http_response_code(405); // Method Not Allowed
        echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
        break;
}

function fetchAllCoordinators() {
    global $pdo;
    try {
        $stmt = $pdo->query("SELECT * FROM coordinators");
        $coordinators = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['status' => 'success', 'coordinators' => $coordinators]);
    } catch (PDOException $e) {
        http_response_code(500); // Internal Server Error
        echo json_encode(['status' => 'error', 'message' => 'Failed to fetch coordinators', 'details' => $e->getMessage()]);
    }
}

function fetchCoordinatorById($coordinatorId) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT * FROM coordinators WHERE coordinator_id = :coordinator_id");
        $stmt->bindParam(':coordinator_id', $coordinatorId, PDO::PARAM_STR);
        $stmt->execute();
        $coordinator = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($coordinator) {
            echo json_encode(['status' => 'success', 'coordinator' => $coordinator]);
        } else {
            http_response_code(404); // Not Found
            echo json_encode(['status' => 'error', 'message' => 'Coordinator not found.']);
        }
    } catch (PDOException $e) {
        http_response_code(500); // Internal Server Error
        echo json_encode(['status' => 'error', 'message' => 'Failed to fetch coordinator details', 'details' => $e->getMessage()]);
    }
}

function upsertCoordinator() {
    global $pdo;
    $input = json_decode(file_get_contents('php://input'), true);

    $coordinator_id = $input['p_coordinator_id'] ?? null;
    $name = $input['p_name'] ?? null;
    $email = $input['p_email'] ?? null;
    $phone_number = $input['p_phone_number'] ?? null;
    $department = $input['p_department'] ?? null;
    $semester = $input['p_semester'] ?? null;
    $designation = $input['p_designation'] ?? null;
    $team = $input['p_team'] ?? null;

    if (!$name || !$email || !$department || !$designation) {
        http_response_code(400); // Bad Request
        echo json_encode(['status' => 'error', 'message' => 'Missing required input for coordinator.']);
        return;
    }

    try {
        // Assuming UpsertCoordinator is a stored procedure that handles both insert and update
        $stmt = $pdo->prepare("CALL UpsertCoordinator(:coordinator_id, :name, :email, :phone_number, :department, :semester, :designation, :team, @p_result_id, @p_operation)");
        $stmt->bindParam(':coordinator_id', $coordinator_id, PDO::PARAM_STR);
        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->bindParam(':phone_number', $phone_number, PDO::PARAM_STR);
        $stmt->bindParam(':department', $department, PDO::PARAM_STR);
        $stmt->bindParam(':semester', $semester, PDO::PARAM_INT);
        $stmt->bindParam(':designation', $designation, PDO::PARAM_STR);
        $stmt->bindParam(':team', $team, PDO::PARAM_STR);

        $stmt->execute();
        echo json_encode(['status' => 'success', 'message' => 'Coordinator upserted successfully.']);
    } catch (PDOException $e) {
        http_response_code(500); // Internal Server Error
        echo json_encode(['status' => 'error', 'message' => 'Failed to upsert coordinator', 'details' => $e->getMessage()]);
    }
}

function deleteCoordinator() {
    global $pdo;
    $input = json_decode(file_get_contents('php://input'), true);

    $coordinator_id = $input['coordinator_id'] ?? null;

    if (!$coordinator_id) {
        http_response_code(400); // Bad Request
        echo json_encode(['status' => 'error', 'message' => 'Coordinator ID is required for deletion.']);
        return;
    }

    try {
        $stmt = $pdo->prepare("DELETE FROM coordinators WHERE coordinator_id = :coordinator_id");
        $stmt->bindParam(':coordinator_id', $coordinator_id, PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            echo json_encode(['status' => 'success', 'message' => 'Coordinator deleted successfully.']);
        } else {
            http_response_code(404); // Not Found
            echo json_encode(['status' => 'error', 'message' => 'Coordinator not found or already deleted.']);
        }
    } catch (PDOException $e) {
        http_response_code(500); // Internal Server Error
        echo json_encode(['status' => 'error', 'message' => 'Failed to delete coordinator', 'details' => $e->getMessage()]);
    }
}
?>