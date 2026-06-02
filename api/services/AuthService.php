<?php
/**
 * AuthService — Orchestrates the authentication flow.
 *
 * Responsibilities:
 *   - Send OTP: generate, hash, store in DB, email via Mailer
 *   - Verify OTP: validate hash, create user if new, mint JWT
 *   - Refresh token: validate existing token, issue new one
 *
 * Designed with Strategy pattern extensibility:
 *   Future auth providers (Google, LinkedIn) will implement the same
 *   interface and be routed via AuthController.
 */

declare(strict_types=1);

namespace App\Services;

use App\Config\AppConfig;
use App\Config\Database;
use App\Config\JwtConfig;
use App\Helpers\Logger;
use App\Models\OtpModel;
use App\Models\UserModel;
use PDO;

class AuthService
{
    private PDO $db;
    private UserModel $userModel;
    private OtpModel $otpModel;

    public function __construct()
    {
        $this->db        = Database::getInstance();
        $this->userModel = new UserModel($this->db);
        $this->otpModel  = new OtpModel($this->db);
    }

    /**
     * Send OTP to a user's email.
     *
     * Flow:
     *   1. Validate email domain (students/admins must be @iiitmanipur.ac.in)
     *   2. Find or prepare user
     *   3. Check rate limiting
     *   4. Generate OTP, hash it, store in DB
     *   5. Send via email
     *
     * @param string $email User email
     * @param string $role  Role name
     * @return array ['success' => bool, 'message' => string]
     */
    public function sendOtp(string $email, string $role): array
    {
        // Domain restriction for students and admins
        if (in_array($role, ['student', 'admin'], true)) {
            $domain = strtolower(substr(strrchr($email, '@'), 1));
            if ($domain !== 'iiitmanipur.ac.in') {
                return [
                    'success' => false,
                    'message' => "Only IIIT Manipur emails are allowed for {$role} role.",
                ];
            }
        }

        // Check if role allows self-registration
        $user = $this->userModel->findByEmailAndRole($email, $role);

        if (!$user && in_array($role, AppConfig::ADMIN_CREATED_ROLES, true)) {
            return [
                'success' => false,
                'message' => "Registration not allowed for {$role}. Please contact admin.",
            ];
        }

        // Check if email exists with a DIFFERENT role
        if (!$user) {
            $existingUser = $this->userModel->findByEmail($email);
            if ($existingUser) {
                return [
                    'success' => false,
                    'message' => 'This email is already registered with a different role.',
                ];
            }
        }

        // For new self-registering users, create the user now
        // (trigger auto-creates student/recruiter profile)
        if (!$user && in_array($role, AppConfig::SELF_REGISTER_ROLES, true)) {
            try {
                $userId = $this->userModel->create($email, $role);
                $user = $this->userModel->findById($userId);
                Logger::info('auth', "New {$role} user created", ['email' => $email, 'user_id' => $userId]);
            } catch (\Exception $e) {
                Logger::error('auth', "Failed to create user", ['email' => $email, 'error' => $e->getMessage()]);
                return ['success' => false, 'message' => 'Error creating user account.'];
            }
        }

        if (!$user) {
            return ['success' => false, 'message' => 'Unable to process login request.'];
        }

        // Check if account is active
        if (!$user['is_active']) {
            return ['success' => false, 'message' => 'Your account has been deactivated.'];
        }

        // Rate limit check
        if ($this->otpModel->isRateLimited($user['user_id'])) {
            return ['success' => false, 'message' => 'Too many OTP requests. Please wait a few minutes.'];
        }

        // Generate OTP (cryptographically random 6-digit)
        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $otpHash   = hash('sha256', $otp);
        $expiry    = (int) ($_ENV['OTP_EXPIRY_MINUTES'] ?? 5);

        // Store in database
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
        $this->otpModel->create($user['user_id'], $otpHash, 'login', $expiry, $ipAddress);

        // Send via email
        $emailSent = $this->sendOtpEmail($email, $otp);

        if (!$emailSent) {
            Logger::error('auth', "Failed to send OTP email", ['email' => $email]);
            return ['success' => false, 'message' => 'Failed to send OTP. Please try again.'];
        }

        Logger::info('auth', "OTP sent", ['email' => $email, 'role' => $role]);

        return ['success' => true, 'message' => 'OTP sent successfully to your email.'];
    }

    /**
     * Verify OTP and return JWT token.
     *
     * @param string $email User email
     * @param string $otp   6-digit OTP entered by user
     * @param string $role  Role name
     * @return array ['success' => bool, 'token' => string|null, 'user' => array|null, 'message' => string]
     */
    public function verifyOtp(string $email, string $otp, string $role): array
    {
        $user = $this->userModel->findByEmailAndRole($email, $role);

        if (!$user) {
            return ['success' => false, 'message' => 'No account found for this email and role.'];
        }

        if (!$user['is_active']) {
            return ['success' => false, 'message' => 'Your account has been deactivated.'];
        }

        // Hash the entered OTP and validate against DB
        $otpHash = hash('sha256', $otp);
        $result  = $this->otpModel->validate($user['user_id'], $otpHash, 'login');

        $errorMessages = [
            'not_found'    => 'No OTP found. Please request a new one.',
            'expired'      => 'OTP has expired. Please request a new one.',
            'used'         => 'This OTP has already been used.',
            'invalid'      => 'Invalid OTP. Please try again.',
            'max_attempts' => 'Maximum attempts exceeded. Please request a new OTP.',
        ];

        if ($result !== 'ok') {
            Logger::warning('auth', "OTP verification failed: {$result}", ['email' => $email]);
            return ['success' => false, 'message' => $errorMessages[$result] ?? 'Verification failed.'];
        }

        // Update login timestamp
        $this->userModel->updateLoginTimestamp($user['user_id']);

        // Mint JWT
        $token = JwtConfig::issue([
            'sub'   => $user['user_id'],
            'role'  => $role,
            'email' => $email,
        ]);

        Logger::info('auth', "Login successful", ['email' => $email, 'role' => $role]);

        return [
            'success' => true,
            'message' => 'OTP verified successfully.',
            'token'   => $token,
            'user'    => [
                'id'    => $user['user_id'],
                'email' => $user['email'],
                'role'  => $role,
            ],
        ];
    }

    /**
     * Refresh an existing JWT token.
     *
     * @param array $claims Current token claims
     * @return array ['success' => bool, 'token' => string|null]
     */
    public function refreshToken(array $claims): array
    {
        // Verify user still exists and is active
        $user = $this->userModel->findById($claims['sub']);

        if (!$user || !$user['is_active']) {
            return ['success' => false, 'message' => 'Account not found or deactivated.'];
        }

        $token = JwtConfig::issue([
            'sub'   => $user['user_id'],
            'role'  => $user['role_name'],
            'email' => $user['email'],
        ]);

        return ['success' => true, 'token' => $token];
    }

    /**
     * Send OTP email using the Mailer utility.
     *
     * @param string $email Recipient
     * @param string $otp   Plain text OTP
     * @return bool
     */
    private function sendOtpEmail(string $email, string $otp): bool
    {
        try {
            // Use the existing Mailer from the legacy codebase
            // It's autoloaded via the vendor symlink
            if (class_exists(\TnpPortal\Mailer::class)) {
                return \TnpPortal\Mailer::sendOtp($email, $otp);
            }

            // Fallback: use PHPMailer directly
            return $this->sendOtpViaPhpMailer($email, $otp);
        } catch (\Exception $e) {
            Logger::error('auth', "Email send error", ['email' => $email, 'error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Fallback PHPMailer implementation.
     */
    private function sendOtpViaPhpMailer(string $email, string $otp): bool
    {
        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

        $mail->isSMTP();
        $mail->Host       = $_ENV['SMTP_HOST'] ?? 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = $_ENV['SMTP_USER'] ?? $_ENV['SMTP_USERNAME'] ?? '';
        $mail->Password   = $_ENV['SMTP_PASS'] ?? $_ENV['SMTP_PASSWORD'] ?? '';
        $mail->SMTPSecure = 'tls';
        $mail->Port       = (int) ($_ENV['SMTP_PORT'] ?? 587);

        $mail->setFrom(
            $_ENV['SMTP_FROM_EMAIL'] ?? 'noreply@iiitmanipur.ac.in',
            $_ENV['SMTP_FROM_NAME'] ?? 'TNP Cell'
        );
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = 'Your TNP Portal Login OTP';
        $mail->Body    = "
            <div style='font-family: Inter, sans-serif; max-width: 480px; margin: 0 auto; padding: 32px;'>
                <h2 style='color: #8B2A8B; margin-bottom: 16px;'>TNP Portal — Login OTP</h2>
                <p style='font-size: 16px; color: #333;'>Your One-Time Password is:</p>
                <div style='font-size: 32px; font-weight: bold; letter-spacing: 8px; color: #C03854;
                            background: #fdf2f8; padding: 16px 24px; border-radius: 12px;
                            text-align: center; margin: 16px 0;'>{$otp}</div>
                <p style='font-size: 14px; color: #666;'>Valid for {$_ENV['OTP_EXPIRY_MINUTES']} minutes. Do not share this code.</p>
                <hr style='margin: 24px 0; border: none; border-top: 1px solid #eee;'>
                <p style='font-size: 12px; color: #999;'>IIIT Manipur — Training & Placement Cell</p>
            </div>
        ";
        $mail->AltBody = "Your TNP Portal OTP is: {$otp}. Valid for {$_ENV['OTP_EXPIRY_MINUTES']} minutes.";

        return $mail->send();
    }
}
