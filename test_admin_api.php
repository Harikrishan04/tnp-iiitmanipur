<?php
/**
 * Integration Test Runner for Phase 5 (Admin Module).
 */

declare(strict_types=1);

require_once __DIR__ . '/api/vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Custom autoloader
spl_autoload_register(function (string $class) {
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/api/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $relativeClass = substr($class, $len);
    $parts = explode('\\', $relativeClass);
    $className = array_pop($parts);
    $dir = implode('/', array_map('strtolower', $parts));
    $file = $baseDir . $dir . '/' . $className . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

use App\Config\Database;

$db = Database::getInstance();

echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "   RUNNING TNP ADMIN MODULE API TESTS   \n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

// Helper function to execute cURL
function request(string $method, string $path, ?array $data = null, ?string $token = null): array
{
    $url = "http://localhost/tnp@iiitmanipur/api" . $path;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    
    $headers = ['Content-Type: application/json'];
    if ($token) {
        $headers[] = "Authorization: Bearer " . $token;
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    if ($data !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }

    $res = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return [
        'code' => $code,
        'body' => json_decode((string) $res, true) ?: $res
    ];
}

// 1. Setup Admin Account in DB
$email = 'testadmin@iiitmanipur.ac.in';
$db->exec("DELETE FROM admins WHERE admin_id IN (SELECT user_id FROM users WHERE email = '$email')");
$db->exec("DELETE FROM users WHERE email = '$email'");

$roleId = $db->query("SELECT role_id FROM roles WHERE name = 'admin'")->fetchColumn();
$adminId = $db->query("SELECT UUID()")->fetchColumn();

$db->prepare(
    "INSERT INTO users (user_id, email, phone, role_id, is_active, account_activated) 
     VALUES (?, ?, '9988776655', ?, TRUE, TRUE)"
)->execute([$adminId, $email, $roleId]);

$db->prepare(
    "INSERT INTO admins (admin_id, name, designation, access_level) 
     VALUES (?, 'System Admin', 'Director', 'admin')"
)->execute([$adminId]);

// 2. Mint Admin JWT directly
$token = App\Config\JwtConfig::issue([
    'sub'   => $adminId,
    'role'  => 'admin',
    'email' => $email,
]);

echo "  ✅ Admin successfully authorized.\n\n";

// 3. Test Sessions Listing
echo "━━━ PLACEMENT SESSIONS ━━━\n";
$sessionRes = request('GET', '/admin/sessions', null, $token);
if ($sessionRes['code'] === 200 && is_array($sessionRes['body']['data'])) {
    echo "  ✅ Sessions listed successfully.\n";
} else {
    echo "  ❌ Failed listing sessions: " . json_encode($sessionRes['body']) . "\n";
    exit(1);
}

// 4. Test Create Session
$sessionLabel = '2026-27';
// Clean up duplicate if any
$db->exec("DELETE FROM placement_sessions WHERE label = '$sessionLabel'");

$createSessionRes = request('POST', '/admin/sessions', [
    'label' => $sessionLabel,
    'start_date' => '2026-08-01',
    'end_date' => '2027-05-31'
], $token);

if ($createSessionRes['code'] === 201) {
    echo "  ✅ Session '$sessionLabel' created successfully.\n";
} else {
    echo "  ❌ Failed creating session: " . json_encode($createSessionRes['body']) . "\n";
    exit(1);
}

// 5. Test Activate Session
$newSessionId = $db->query("SELECT session_id FROM placement_sessions WHERE label = '$sessionLabel'")->fetchColumn();
$activateRes = request('PUT', "/admin/sessions/{$newSessionId}/activate", null, $token);

if ($activateRes['code'] === 200) {
    echo "  ✅ Session '$sessionLabel' activated successfully.\n";
    $isActive = (bool) $db->query("SELECT is_active FROM placement_sessions WHERE session_id = '{$newSessionId}'")->fetchColumn();
    if ($isActive) {
        echo "  ✅ DB verification: session is active.\n";
    } else {
        echo "  ❌ DB verification: session is NOT active.\n";
        exit(1);
    }
} else {
    echo "  ❌ Failed activating session: " . json_encode($activateRes['body']) . "\n";
    exit(1);
}

// 6. Test Announcements
echo "\n━━━ ANNOUNCEMENTS ━━━\n";
$annTitle = 'Urgent: Registration for Google Drive';
$annRes = request('POST', '/admin/announcements', [
    'title' => $annTitle,
    'body' => 'All students must fill details by tomorrow noon.',
    'priority' => 'urgent',
    'visible_to_roles' => ['student'],
    'targets' => [
        ['type' => 'branch', 'value' => 'CSE'],
        ['type' => 'year', 'value' => '2027']
    ]
], $token);

if ($annRes['code'] === 201) {
    echo "  ✅ Announcement posted successfully.\n";
    $newAnnId = $annRes['body']['data']['announcement_id'];
    
    // Verify target mappings
    $targetCount = (int) $db->query("SELECT COUNT(*) FROM announcement_targets WHERE announcement_id = '$newAnnId'")->fetchColumn();
    if ($targetCount === 2) {
        echo "  ✅ Targets mapped correctly in database.\n";
    } else {
        echo "  ❌ Targets mismatch: expected 2, found $targetCount.\n";
        exit(1);
    }
} else {
    echo "  ❌ Failed posting announcement: " . json_encode($annRes['body']) . "\n";
    exit(1);
}

// Test listing announcements
$listAnn = request('GET', '/admin/announcements', null, $token);
if ($listAnn['code'] === 200 && count($listAnn['body']['data']) > 0) {
    echo "  ✅ Announcements listed: " . count($listAnn['body']['data']) . " records found.\n";
} else {
    echo "  ❌ Failed listing announcements: " . json_encode($listAnn['body']) . "\n";
    exit(1);
}

// Test deleting announcement
$deleteRes = request('DELETE', "/admin/announcements/{$newAnnId}", null, $token);
if ($deleteRes['code'] === 200) {
    echo "  ✅ Announcement deleted successfully.\n";
} else {
    echo "  ❌ Failed deleting announcement: " . json_encode($deleteRes['body']) . "\n";
    exit(1);
}

// 7. Test Stats
echo "\n━━━ STATISTICS ━━━\n";
$statsRes = request('GET', '/admin/stats', null, $token);
if ($statsRes['code'] === 200 && isset($statsRes['body']['data']['placed_count'])) {
    echo "  ✅ Placement statistics retrieved successfully.\n";
} else {
    echo "  ❌ Failed retrieving stats: " . json_encode($statsRes['body']) . "\n";
    exit(1);
}

// 8. Test User Management
echo "\n━━━ USER MANAGEMENT ━━━\n";
$usersRes = request('GET', '/admin/users', null, $token);
if ($usersRes['code'] === 200 && is_array($usersRes['body']['data'])) {
    echo "  ✅ User list loaded successfully.\n";
    
    // Toggle active status
    $targetUserId = $usersRes['body']['data'][0]['user_id'];
    $currentStatus = (bool) $usersRes['body']['data'][0]['is_active'];
    $newStatus = !$currentStatus;
    
    $statusRes = request('PUT', "/admin/users/{$targetUserId}/status", ['is_active' => $newStatus], $token);
    if ($statusRes['code'] === 200) {
        echo "  ✅ User active status toggled successfully.\n";
        $dbStatus = (bool) $db->query("SELECT is_active FROM users WHERE user_id = '$targetUserId'")->fetchColumn();
        if ($dbStatus === $newStatus) {
            echo "  ✅ DB verification: status updated.\n";
            // Revert status
            $db->prepare("UPDATE users SET is_active = ? WHERE user_id = ?")->execute([$currentStatus ? 1 : 0, $targetUserId]);
        } else {
            echo "  ❌ DB verification: status update failed.\n";
            exit(1);
        }
    } else {
        echo "  ❌ Failed toggling user status: " . json_encode($statusRes['body']) . "\n";
        exit(1);
    }
} else {
    echo "  ❌ Failed listing users: " . json_encode($usersRes['body']) . "\n";
    exit(1);
}

echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "PASSED: All Phase 5 Admin tests passed successfully. ✅\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
