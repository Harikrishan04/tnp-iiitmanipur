<?php
require_once __DIR__ . '/api/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Helper to make internal API calls simulating requests
function makeRequest($method, $uri, $body = null, $token = null) {
    // We will use standard HTTP via local curl
    $ch = curl_init('http://localhost/tnp@iiitmanipur/api' . $uri);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $headers = ['Content-Type: application/json'];
    if ($token) {
        $headers[] = 'Authorization: Bearer ' . $token;
    }
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if ($body) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        }
    } else if ($method === 'DELETE') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
    }
    
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $response = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return ['status' => $status, 'body' => json_decode($response, true) ?? $response];
}

// Ensure OTP hash is forced for the test users
$db = new PDO("mysql:host={$_ENV['DB_HOST']};dbname={$_ENV['DB_NAME']}", $_ENV['DB_USER'], $_ENV['DB_PASS']);
$forcedHash = hash('sha256', '123456');
$db->exec("DELETE FROM otp_requests WHERE user_id=(SELECT user_id FROM users WHERE email='testadmin@iiitmanipur.ac.in') OR user_id=(SELECT user_id FROM users WHERE email='test@iiitmanipur.ac.in')");
$db->exec("INSERT INTO otp_requests (user_id, otp_hash, channel, purpose, expires_at) SELECT user_id, '$forcedHash', 'email', 'login', DATE_ADD(NOW(), INTERVAL 1 HOUR) FROM users WHERE email IN ('testadmin@iiitmanipur.ac.in', 'test@iiitmanipur.ac.in')");

echo "=== STEP 1: Admin Login ===\n";
$adminAuth = makeRequest('POST', '/auth/verify-otp', ['email' => 'testadmin@iiitmanipur.ac.in', 'otp' => '123456', 'role' => 'admin']);
$adminToken = $adminAuth['body']['data']['token'] ?? null;
echo $adminToken ? "Admin Token Acquired.\n" : "Failed to get Admin Token.\n";

if (!$adminToken) die(print_r($adminAuth, true));

echo "\n=== STEP 2: Create Announcement (Email Disabled) ===\n";
$payload = [
    'title' => 'Manual Test Announcement',
    'body' => 'Checking if this works without sending an email.',
    'priority' => 'normal',
    'visible_to_roles' => ['student'],
    'status' => 'published',
    'send_email' => false
];
$createRes = makeRequest('POST', '/admin/announcements', $payload, $adminToken);
print_r($createRes);
$annId = $createRes['body']['data']['announcement_id'] ?? null;
echo $annId ? "Announcement Created: $annId\n" : "Failed to create announcement.\n";

echo "\n=== STEP 3: Verify Public Endpoint ===\n";
$publicRes = makeRequest('GET', '/public/announcements');
$foundPublic = false;
foreach ($publicRes['body']['data'] ?? [] as $ann) {
    if ($ann['announcement_id'] === $annId) $foundPublic = true;
}
echo "Appeared in Public Endpoint? " . ($foundPublic ? "YES" : "NO") . "\n";

echo "\n=== STEP 4: Student Login ===\n";
$studentAuth = makeRequest('POST', '/auth/verify-otp', ['email' => 'test@iiitmanipur.ac.in', 'otp' => '123456', 'role' => 'student']);
print_r($studentAuth);
$studentToken = $studentAuth['body']['data']['token'] ?? null;
echo $studentToken ? "Student Token Acquired.\n" : "Failed to get Student Token.\n";

echo "\n=== STEP 5: Student Fetch Announcements ===\n";
$studentAnns = makeRequest('GET', '/user/announcements', null, $studentToken);
print_r($studentAnns);
$foundStudent = false;
$isReadBefore = null;
foreach ($studentAnns['body']['data'] ?? [] as $ann) {
    if ($ann['announcement_id'] === $annId) {
        $foundStudent = true;
        $isReadBefore = $ann['is_read'];
    }
}
echo "Appeared in Student Feed? " . ($foundStudent ? "YES" : "NO") . "\n";
echo "Initial is_read state: " . ($isReadBefore ? "TRUE" : "FALSE") . "\n";

echo "\n=== STEP 6: Mark as Read ===\n";
$readRes = makeRequest('POST', "/user/announcements/{$annId}/read", null, $studentToken);
echo "Mark read response: " . ($readRes['body']['status'] ?? 'Unknown') . "\n";

echo "\n=== STEP 7: Verify is_read Updated ===\n";
$studentAnnsAfter = makeRequest('GET', '/user/announcements', null, $studentToken);
$isReadAfter = null;
foreach ($studentAnnsAfter['body']['data'] ?? [] as $ann) {
    if ($ann['announcement_id'] === $annId) {
        $isReadAfter = $ann['is_read'];
    }
}
echo "Final is_read state: " . ($isReadAfter ? "TRUE" : "FALSE") . "\n";

// Cleanup
$db->exec("DELETE FROM announcements WHERE announcement_id = '$annId'");
