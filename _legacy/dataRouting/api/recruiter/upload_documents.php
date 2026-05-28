<?php
// JD Document Upload/Removal API for Jobs (events table)
header('Content-Type: application/json');
session_start();

$pdo = require __DIR__ . '/../../config/db.php';
if (!$pdo) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
    exit;
}

function error_response($msg, $code = 400) {
    http_response_code($code);
    echo json_encode(['status' => 'error', 'message' => $msg]);
    exit;
}
function sanitizeFolderName($name) {
    return preg_replace('/[^a-zA-Z0-9-_]/', '_', $name);
}
function debug_log($msg) {
    $logFile = __DIR__ . '/upload_debug.log';
    $time = date('Y-m-d H:i:s');
    if (is_array($msg) || is_object($msg)) {
        $msg = print_r($msg, true);
    }
    file_put_contents($logFile, "[{$time}] {$msg}\n", FILE_APPEND | LOCK_EX);
}

debug_log('--- New upload request ---');
debug_log(['_POST' => $_POST, '_FILES' => $_FILES, '_SESSION' => $_SESSION]);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    debug_log('Method Not Allowed');
    error_response('Method Not Allowed', 405);
}
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'recruiter') {
    debug_log('Unauthorized');
    error_response('Unauthorized', 401);
}

$recruiterId = $_SESSION['user_id'];
$eventId = $_POST['job_id'] ?? $_POST['event_id'] ?? '';
if (!$eventId) {
    debug_log('Missing job_id / event_id');
    error_response('Missing job_id or event_id');
}

// Fetch event (job) and check ownership via event_organiser_id
$stmt = $pdo->prepare('SELECT event_id, event_organiser_id, event_title, event_document FROM events WHERE event_id = ? AND event_organiser_id = ?');
$stmt->execute([$eventId, $recruiterId]);
$event = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$event) {
    debug_log('Job not found or not owned by recruiter');
    error_response('Job not found or not owned by recruiter', 403);
}

// Fetch recruiter for company name from company_details_json
$stmt2 = $pdo->prepare('SELECT recruiter_id, company_details_json FROM recruiters WHERE recruiter_id = ?');
$stmt2->execute([$recruiterId]);
$recruiter = $stmt2->fetch(PDO::FETCH_ASSOC);
if (!$recruiter) {
    debug_log('Recruiter not found');
    error_response('Recruiter not found', 403);
}

$companyDetails = $recruiter['company_details_json'];
$companyName = 'Company';
if ($companyDetails) {
    $decoded = is_string($companyDetails) ? json_decode($companyDetails, true) : $companyDetails;
    if (is_array($decoded) && !empty($decoded['company_name'])) {
        $companyName = $decoded['company_name'];
    }
}
$companyName = sanitizeFolderName($companyName);
$jobTitle = sanitizeFolderName($event['event_title']);

$baseDir = $_SERVER['DOCUMENT_ROOT'] . '/tnp@iiitmanipur/dataRouting/uploads/recruiter/';
$jobDir = $baseDir . $companyName . '/' . $jobTitle . '/';
debug_log(['recruiterId' => $recruiterId, 'companyName' => $companyName, 'jobTitle' => $jobTitle, 'jobDir' => $jobDir]);
if (!is_dir($jobDir)) {
    debug_log('Creating directory: ' . $jobDir);
    mkdir($jobDir, 0777, true);
}

// Existing documents: event_document can be JSON array or object
$existingFiles = [];
$ed = $event['event_document'];
if ($ed !== null) {
    if (is_string($ed)) {
        $ed = json_decode($ed, true);
    }
    if (is_array($ed)) {
        $existingFiles = array_values($ed);
    }
}

// Upload new files
if (!empty($_FILES['new_jd_documents']['name'][0])) {
    foreach ($_FILES['new_jd_documents']['tmp_name'] as $i => $tmpName) {
        if (!is_uploaded_file($tmpName)) {
            continue;
        }
        $origName = $_FILES['new_jd_documents']['name'][$i];
        $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
        if (!in_array($ext, ['pdf', 'doc', 'docx'])) {
            debug_log('Skipped file (invalid ext): ' . $origName);
            continue;
        }
        $safeName = sanitizeFolderName(pathinfo($origName, PATHINFO_FILENAME)) . '.' . $ext;
        $dest = $jobDir . $safeName;
        debug_log('Attempting to upload: ' . $tmpName . ' to ' . $dest);
        if (move_uploaded_file($tmpName, $dest)) {
            $relativePath = '/tnp@iiitmanipur/dataRouting/uploads/recruiter/' . $companyName . '/' . $jobTitle . '/' . $safeName;
            $existingFiles[] = $relativePath;
            debug_log('Uploaded: ' . $dest);
        } else {
            debug_log('Failed to move uploaded file: ' . $tmpName . ' to ' . $dest);
        }
    }
}

$newDocumentJson = json_encode(array_values($existingFiles));
$stmt = $pdo->prepare('UPDATE events SET event_document = ?, updated_at = CURRENT_TIMESTAMP WHERE event_id = ?');
$stmt->execute([$newDocumentJson, $eventId]);
debug_log('Updated event_document: ' . $newDocumentJson);

echo json_encode(['status' => 'success', 'job_description_path' => $existingFiles, 'event_document' => $existingFiles]);
