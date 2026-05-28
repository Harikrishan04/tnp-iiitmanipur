<?php
/**
 * api_test.php — Phase 2 API test runner.
 * Run: php api_test.php
 */

require '/var/www/html/tnp@iiitmanipur/api/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable('/var/www/html/tnp@iiitmanipur');
$dotenv->load();

spl_autoload_register(function ($c) {
    $prefix = 'App\\';
    $base   = '/var/www/html/tnp@iiitmanipur/api/';
    $len    = strlen($prefix);
    if (strncmp($prefix, $c, $len) !== 0) return;
    $rel  = substr($c, $len);
    $parts = explode('\\', $rel);
    $cn   = array_pop($parts);
    $dir  = implode('/', array_map('strtolower', $parts));
    $file = $base . $dir . '/' . $cn . '.php';
    if (file_exists($file)) require_once $file;
});

$db = App\Config\Database::getInstance();

// Get test student
$stmt = $db->prepare('SELECT user_id, email FROM users WHERE email LIKE ?');
$stmt->execute(['newstudent@%']);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo "ERROR: No test student found. Run: POST /auth/login with newstudent@iiitmanipur.ac.in\n";
    exit(1);
}

$token = App\Config\JwtConfig::issue([
    'sub'   => $user['user_id'],
    'role'  => 'student',
    'email' => $user['email'],
]);

$base = 'http://localhost/tnp@iiitmanipur/api';
$ok   = 0;
$fail = 0;

function test(string $label, string $method, string $url, string $token, ?array $body = null): array {
    $headers = ["Authorization: Bearer {$token}", "Content-Type: application/json", "Accept: application/json"];
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST  => $method,
        CURLOPT_HTTPHEADER     => $headers,
    ]);
    if ($body) curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
    $resp   = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $json = json_decode($resp, true) ?? ['status' => 'parse_error', 'raw' => substr($resp, 0, 120)];
    return ['label' => $label, 'http' => $status, 'json' => $json];
}

function pass(string $msg): void { global $ok;  $ok++;  echo "  ✅ {$msg}\n"; }
function fail(string $msg): void { global $fail; $fail++; echo "  ❌ {$msg}\n"; }
function section(string $title): void { echo "\n━━━ {$title} ━━━\n"; }

// ─── Tests ───────────────────────────────────────────────────────────────────

section('AUTH GUARD');
$r = test('No token → 401', 'GET', "{$base}/students/me", '');
($r['http'] === 401 || ($r['json']['status'] ?? '') === 'error')
    ? pass("No token → error ({$r['http']}): " . ($r['json']['message'] ?? ''))
    : fail("Expected error, got {$r['http']}: " . json_encode($r['json']));

$r = test('Wrong role (student token → coordinator route)', 'GET', "{$base}/students", $token);
($r['http'] === 403 || ($r['json']['status'] ?? '') === 'error')
    ? pass("Role guard → error ({$r['http']}): " . ($r['json']['message'] ?? ''))
    : fail("Expected 403, got {$r['http']}: " . json_encode($r['json']));

section('STUDENT PROFILE');
$r = test('GET /students/me', 'GET', "{$base}/students/me", $token);
($r['json']['status'] ?? '') === 'success' && isset($r['json']['data']['student_id'])
    ? pass("Profile fetched | email: " . ($r['json']['data']['email'] ?? ''))
    : fail("Got: " . json_encode($r['json']));

$r = test('PUT /students/me (update)', 'PUT', "{$base}/students/me", $token, [
    'name' => 'Test Student', 'current_semester' => 6, 'cpi' => 8.5,
]);
($r['json']['status'] ?? '') === 'success'
    ? pass("Profile updated: " . ($r['json']['message'] ?? ''))
    : fail("Got: " . json_encode($r['json']));

$r = test('GET /students/me (verify update)', 'GET', "{$base}/students/me", $token);
$d = $r['json']['data'] ?? [];
($d['name'] === 'Test Student' && (float)$d['cpi'] === 8.5)
    ? pass("Update verified | name: {$d['name']} | cpi: {$d['cpi']}")
    : fail("Data mismatch: name={$d['name']} cpi={$d['cpi']}");

$r = test('PUT /students/me (invalid CPI > 10)', 'PUT', "{$base}/students/me", $token, ['cpi' => 11.0]);
($r['json']['status'] ?? '') === 'error'
    ? pass("Validation rejected CPI=11")
    : fail("Expected error for CPI=11, got: " . json_encode($r['json']));

section('LOOKUPS');
$r = test('GET /students/lookups', 'GET', "{$base}/students/lookups", $token);
$depts = $r['json']['data']['departments'] ?? [];
$progs = $r['json']['data']['programs'] ?? [];
(count($depts) > 0 && count($progs) > 0)
    ? pass("Lookups | depts: " . count($depts) . " | programs: " . count($progs))
    : fail("Got: " . json_encode($r['json']));

section('JOBS');
$r = test('GET /jobs (eligible — no active session seeded)', 'GET', "{$base}/jobs", $token);
($r['json']['status'] ?? '') === 'success'
    ? pass("Jobs endpoint OK | total: " . ($r['json']['meta']['total'] ?? 0) . " (0 expected — no session seeded)")
    : fail("Got: " . json_encode($r['json']));

section('APPLICATIONS');
$r = test('GET /applications/me', 'GET', "{$base}/applications/me", $token);
($r['json']['status'] ?? '') === 'success'
    ? pass("Applications endpoint OK | total: " . ($r['json']['meta']['total'] ?? 0))
    : fail("Got: " . json_encode($r['json']));

$r = test('POST /applications (no job_id → 422)', 'POST', "{$base}/applications", $token, []);
($r['json']['status'] ?? '') === 'error'
    ? pass("Missing job_id rejected: " . ($r['json']['message'] ?? ''))
    : fail("Expected 422, got: " . json_encode($r['json']));

$r = test('POST /applications (fake job → not found)', 'POST', "{$base}/applications", $token, [
    'job_id' => '00000000-0000-0000-0000-000000000000',
]);
($r['json']['status'] ?? '') === 'error'
    ? pass("Fake job_id rejected: " . ($r['json']['message'] ?? ''))
    : fail("Expected error, got: " . json_encode($r['json']));

// ─── Summary ─────────────────────────────────────────────────────────────────
echo "\n" . str_repeat('━', 40) . "\n";
echo "PASSED: {$ok} | FAILED: {$fail}\n";
if ($fail > 0) {
    echo "❌ Fix failures before proceeding.\n";
    exit(1);
} else {
    echo "✅ All Phase 2 API tests passed.\n";
    exit(0);
}
