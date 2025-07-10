<?php
// dataRouting/api/auth/auth_handler.php
declare(strict_types=1);

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Logs
$logFile = __DIR__ . '/../../logs/auth_handler_debug.log';
$otpFile = __DIR__ . '/../../logs/otp_storage.json';
if (!is_dir(dirname($logFile))) {
    mkdir(dirname($logFile), 0755, true);
}

function authDebugLog($msg) {
    global $logFile;
    $time = date('Y-m-d H:i:s');
    if (is_array($msg) || is_object($msg)) {
        $msg = print_r($msg, true);
    }
    file_put_contents($logFile, "[{$time}] {$msg}" . PHP_EOL, FILE_APPEND | LOCK_EX);
}

// OTP file handling
function saveOtpToFile($key, $data) {
    global $otpFile;
    $otpStorage = file_exists($otpFile) ? json_decode(file_get_contents($otpFile), true) : [];
    $otpStorage[$key] = $data;
    file_put_contents($otpFile, json_encode($otpStorage), LOCK_EX);
}

function getOtpFromFile($key) {
    global $otpFile;
    if (!file_exists($otpFile)) return null;
    $otpStorage = json_decode(file_get_contents($otpFile), true);
    return $otpStorage[$key] ?? null;
}

function removeOtpFromFile($key) {
    global $otpFile;
    if (!file_exists($otpFile)) return;
    $otpStorage = json_decode(file_get_contents($otpFile), true);
    unset($otpStorage[$key]);
    file_put_contents($otpFile, json_encode($otpStorage), LOCK_EX);
}

authDebugLog("=== AUTH HANDLER START ===");

// Load dependencies
// Ensure these paths are correct for your project structure
require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../../config/db.php';
require __DIR__ . '/../../utils/TnpPortal/Mailer.php';

use PHPMailer\PHPMailer\Exception;
use TnpPortal\Mailer;

class AuthUtils {
    public static function generateOTP(): string {
        return str_pad((string)random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
    }

    public static function validateEmail(string $email) {
        $email = filter_var(trim($email), FILTER_VALIDATE_EMAIL);
        return $email !== false ? strtolower($email) : false;
    }

    public static function validateEmailDomain(string $email, string $role): bool {
        // Only restrict domain for student and admin roles as per previous logic
        $restrictedRoles = ['student', 'admin'];
        if (!in_array($role, $restrictedRoles)) {
            return true; // Allow other roles (like coordinator, recruiter) from any domain for OTP sending
        }
        $domain = strtolower(substr(strrchr($email, "@"), 1));
        return $domain === 'iiitmanipur.ac.in';
    }

    public static function isValidRole(string $role): bool {
        $allowedRoles = ['student', 'recruiter', 'coordinator', 'admin'];
        return in_array($role, $allowedRoles, true);
    }

    public static function generateSessionToken(string $email, string $role): string {
        $randomString = bin2hex(random_bytes(16));
        $timestamp = time();
        return base64_encode("$email|$role|$timestamp|$randomString");
    }

    public static function sendErrorResponse(string $message, int $httpCode = 400): never {
        http_response_code($httpCode);
        echo json_encode(['status' => 'error', 'message' => $message]);
        exit;
    }

    public static function sendSuccessResponse(array $data): never {
        echo json_encode(array_merge(['status' => 'success'], $data));
        exit;
    }
}

try {
    $rawInput = file_get_contents('php://input');
    authDebugLog("Raw Input: " . $rawInput);
    $input = json_decode($rawInput, true);
    if (!is_array($input)) {
        authDebugLog("ERROR: Invalid JSON input");
        AuthUtils::sendErrorResponse('Invalid JSON input.');
    }

    $action = $input['action'] ?? '';
    $emailInput = $input['email'] ?? '';
    $validatedEmail = AuthUtils::validateEmail($emailInput);
    if (!$validatedEmail) {
        authDebugLog("ERROR: Invalid email: $emailInput");
        AuthUtils::sendErrorResponse('Invalid email format.');
    }
    $email = $validatedEmail;

    if ($action === 'send_otp') {
        $role = $input['role'] ?? '';
        if (!$email || !$role) {
            AuthUtils::sendErrorResponse('Email and role are required.');
        }
        if (!AuthUtils::isValidRole($role)) {
            AuthUtils::sendErrorResponse('Invalid role.');
        }
        // Domain validation for sending OTP
        if (!AuthUtils::validateEmailDomain($email, $role)) {
            AuthUtils::sendErrorResponse("Only IIIT Manipur emails allowed for $role role.");
        }
        authDebugLog("Processing send_otp for email: $email, role: $role");

        // --- Coordinator login logging ---
        if ($role === 'coordinator') {
            authDebugLog("COORDINATOR LOGIN ATTEMPT (send_otp): $email");
        }

        // Generate OTP and store
        $otp = AuthUtils::generateOTP();
        $otpKey = $email . '|' . $role;
        saveOtpToFile($otpKey, [
            'otp' => $otp,
            'role' => $role,
            'timestamp' => time()
        ]);

        if (Mailer::sendOtp($email, $otp)) {
            authDebugLog("OTP sent successfully to $email");
            // --- Coordinator login logging ---
            if ($role === 'coordinator') {
                authDebugLog("COORDINATOR OTP SENT: $email");
            }
            AuthUtils::sendSuccessResponse(['message' => 'OTP sent successfully']);
        } else {
            // --- Coordinator login logging ---
            if ($role === 'coordinator') {
                authDebugLog("COORDINATOR OTP SEND FAILED: $email");
            }
            throw new Exception("Failed to send OTP to $email");
        }
    } elseif ($action === 'verify_otp') {
        $otpInput = $input['otp'] ?? '';
        $role = $input['role'] ?? '';
        if (!$email || !$otpInput || !$role) {
            AuthUtils::sendErrorResponse('Email, role, and OTP are required.');
        }

        // --- Coordinator login logging ---
        if ($role === 'coordinator') {
            authDebugLog("COORDINATOR OTP VERIFY ATTEMPT: $email");
        }

        $otpKey = $email . '|' . $role;
        $otpData = getOtpFromFile($otpKey);
        if (!$otpData) {
            // --- Coordinator login logging ---
            if ($role === 'coordinator') {
                authDebugLog("COORDINATOR OTP VERIFY FAILED (no OTP found): $email");
            }
            AuthUtils::sendErrorResponse('No OTP found for this email and role.');
        }

        $storedOtp = $otpData['otp'] ?? null;
        $storedRole = $otpData['role'] ?? null;
        $otpTimestamp = $otpData['timestamp'] ?? 0;

        if (time() - $otpTimestamp > 300) {
            removeOtpFromFile($otpKey);
            // --- Coordinator login logging ---
            if ($role === 'coordinator') {
                authDebugLog("COORDINATOR OTP VERIFY FAILED (expired): $email");
            }
            AuthUtils::sendErrorResponse('OTP expired.');
        }

        if ($storedOtp !== $otpInput) {
            // --- Coordinator login logging ---
            if ($role === 'coordinator') {
                authDebugLog("COORDINATOR OTP VERIFY FAILED (invalid OTP): $email");
            }
            AuthUtils::sendErrorResponse('Invalid OTP.');
        }

        // Get role ID from role name
        $stmt = $pdo->prepare("SELECT id FROM roles WHERE name = ?");
        $stmt->execute([$storedRole]);
        $roleData = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$roleData) {
            authDebugLog("ERROR: Invalid role name: $storedRole");
            AuthUtils::sendErrorResponse('Invalid role specified.');
        }

        $roleId = $roleData['id'];

        // Fetch user by email and role_id
        $stmt = $pdo->prepare("SELECT user_id, user_name FROM users WHERE user_email = ? AND role_id = ?");
        $stmt->execute([$email, $roleId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            // If user not found with specific email and role_id
            if (in_array($storedRole, ['coordinator', 'admin'])) {
                // For coordinator and admin roles, registration is not allowed if not pre-registered
                authDebugLog("COORDINATOR/ADMIN LOGIN FAILED (user not pre-registered for role $storedRole): $email");
                AuthUtils::sendErrorResponse("Registration not allowed for $storedRole. Please contact admin.");
            }

            // For other roles (student, recruiter), check if email exists with a different role
            $stmt = $pdo->prepare("SELECT user_id FROM users WHERE user_email = ?");
            $stmt->execute([$email]);
            $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($existingUser) {
                authDebugLog("User with email $email already exists with different role");
                AuthUtils::sendErrorResponse("Email already registered with a different role. Please contact admin.");
            }

            // Create new user (only for student and recruiter, if not already handled by previous checks)
            $userId = sprintf(
                '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                mt_rand(0, 0xffff), mt_rand(0, 0xffff),
                mt_rand(0, 0xffff),
                mt_rand(0, 0x0fff) | 0x4000,
                mt_rand(0, 0x3fff) | 0x8000,
                mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
            );

            // Extract name from email (before @) or use provided name
            $userName = $input['name'] ?? explode('@', $email)[0];

            try {
                $stmt = $pdo->prepare("
                    INSERT INTO users (user_id, user_email, user_name, role_id, is_active, is_verified, created_at, updated_at)
                    VALUES (?, ?, ?, ?, 1, 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
                ");
                $stmt->execute([$userId, $email, $userName, $roleId]);
                authDebugLog("New user created: $email with ID: $userId");
            } catch (PDOException $e) {
                authDebugLog("Database error creating user: " . $e->getMessage());
                AuthUtils::sendErrorResponse("Error creating user account. Please try again.");
            }
        } else {
            $userId = $user['user_id'];

            // Update last login
            try {
                $stmt = $pdo->prepare("UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE user_id = ?");
                $stmt->execute([$userId]);
            } catch (PDOException $e) {
                authDebugLog("Database error updating last login: " . $e->getMessage());
            }
        }

        // Generate session token
        $token = AuthUtils::generateSessionToken($email, $storedRole);

        // Clean up OTP
        removeOtpFromFile($otpKey);

        // --- Coordinator login logging ---
        if ($role === 'coordinator') {
            authDebugLog("COORDINATOR LOGIN SUCCESS: $email, id: $userId");
        }

        AuthUtils::sendSuccessResponse([
            'message' => 'OTP verified successfully.',
            'email' => $email,
            'role' => $storedRole,
            'token' => $token,
            'id' => $userId
        ]);
    } else {
        AuthUtils::sendErrorResponse('Invalid action specified.');
    }

} catch (Exception $e) {
    authDebugLog("Exception: " . $e->getMessage());
    AuthUtils::sendErrorResponse('Server error. Try again.');
}

authDebugLog("=== AUTH HANDLER END ===");
?>
