<?php
/**
 * test_coordinator_api.php — Phase 4 Coordinator Module API test runner.
 * Run: php test_coordinator_api.php
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

// Issue Coordinator JWT
$coordUserId = 'e533fa74-592d-11f1-bf45-cc4740c7c70f'; // Created coordinator user ID
$token = App\Config\JwtConfig::issue([
    'sub'   => $coordUserId,
    'role'  => 'coordinator',
    'email' => 'coordinator@iiitmanipur.ac.in',
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

section('COORDINATOR AUTHENTICATION');
$r = test('Access with coordinator token', 'GET', "{$base}/verifications", $token);
($r['http'] === 200)
    ? pass("Coordinator successfully authorized.")
    : fail("Expected 200, got {$r['http']}: " . json_encode($r['json']));

section('VERIFICATIONS LISTING');
$r = test('GET /verifications', 'GET', "{$base}/verifications", $token);
($r['json']['status'] ?? '') === 'success' && isset($r['json']['data'])
    ? pass("Verifications listed: " . count($r['json']['data']) . " records found.")
    : fail("Failed listing verifications: " . json_encode($r['json']));

section('VERIFICATION DETAIL');
// Using the known student verification ID: aae1c5ac-57e8-11f1-bf45-cc4740c7c70f
$verifId = 'aae1c5ac-57e8-11f1-bf45-cc4740c7c70f';
$r = test("GET /verifications/{$verifId}", 'GET', "{$base}/verifications/{$verifId}", $token);
($r['json']['status'] ?? '') === 'success' && isset($r['json']['data']['verification'])
    ? pass("Verification details fetched. Entity Type: " . $r['json']['data']['verification']['entity_type'])
    : fail("Failed fetching verification: " . json_encode($r['json']));

section('ASSIGN COORDINATOR');
$r = test("PUT /verifications/{$verifId}/assign", 'PUT', "{$base}/verifications/{$verifId}/assign", $token, [
    'assigned_coordinator_id' => $coordUserId,
]);
($r['json']['status'] ?? '') === 'success'
    ? pass("Coordinator assigned successfully.")
    : fail("Assignment failed: " . json_encode($r['json']));

section('UPDATE VERIFICATION STATUS');
$r = test("PUT /verifications/{$verifId}", 'PUT', "{$base}/verifications/{$verifId}", $token, [
    'status' => 'verified',
    'remark' => 'Profile checked and verified.',
]);
($r['json']['status'] ?? '') === 'success'
    ? pass("Verification status updated to verified.")
    : fail("Status update failed: " . json_encode($r['json']));

// Fetch detail to confirm
$r = test("GET /verifications/{$verifId}", 'GET', "{$base}/verifications/{$verifId}", $token);
($r['json']['data']['verification']['status'] ?? '') === 'verified' &&
($r['json']['data']['verification']['remark'] ?? '') === 'Profile checked and verified.'
    ? pass("Verification status and remark confirmed in database.")
    : fail("Verification mismatch: " . json_encode($r['json']));

// Check audit log
$logs = $r['json']['data']['logs'] ?? [];
(count($logs) > 0 && $logs[0]['to_status'] === 'verified')
    ? pass("Audit log generated successfully. Found " . count($logs) . " logs.")
    : fail("Audit log check failed: " . json_encode($logs));

// ─── Summary ─────────────────────────────────────────────────────────────────
echo "\n" . str_repeat('━', 40) . "\n";
echo "PASSED: {$ok} | FAILED: {$fail}\n";
if ($fail > 0) {
    echo "❌ Coordinator API tests failed.\n";
    exit(1);
} else {
    echo "✅ All Coordinator API tests passed successfully.\n";
    exit(0);
}
