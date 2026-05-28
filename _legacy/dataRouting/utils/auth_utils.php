<?php
// utils/auth_utils.php
declare(strict_types=1);

class AuthUtils {
    
    /**
     * Generate a secure session token
     * @param string $email User email
     * @param string $role User role
     * @return string Base64 encoded token
     */
    public static function generateSessionToken(string $email, string $role): string {
        $randomString = bin2hex(random_bytes(16));
        $timestamp = time();
        return base64_encode("$email|$role|$timestamp|$randomString");
    }
    
    /**
     * Set secure session cookie
     * @param string $token Session token
     * @param int $expires Expiration time (default 30 days)
     */
    public static function setSessionCookie(string $token, int $expires = null): void {
        $expiryTime = $expires ?? (time() + 60 * 60 * 24 * 30); // 30 days default
        
        setcookie('session_token', $token, [
            'expires' => $expiryTime,
            'path' => '/',
            'httponly' => true,
            'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
            'samesite' => 'Strict',
        ]);
    }
    
    /**
     * Clear session cookie
     */
    public static function clearSessionCookie(): void {
        setcookie('session_token', '', [
            'expires' => time() - 3600,
            'path' => '/',
            'httponly' => true,
            'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
            'samesite' => 'Strict',
        ]);
    }
    
    /**
     * Validate email domain for restricted roles
     * @param string $email Email address
     * @param string $role User role
     * @return bool True if valid, false otherwise
     */
    public static function validateEmailDomain(string $email, string $role): bool {
        $restrictedRoles = ['student', 'coordinator', 'admin'];
        
        if (!in_array($role, $restrictedRoles)) {
            return true; // No restriction for other roles
        }
        
        $domain = strtolower(substr(strrchr($email, "@"), 1));
        return $domain === 'iiitmanipur.ac.in';
    }
    
    /**
     * Validate user role
     * @param string $role Role to validate
     * @return bool True if valid role
     */
    public static function isValidRole(string $role): bool {
        $allowedRoles = ['student', 'recruiter', 'coordinator', 'admin'];
        return in_array($role, $allowedRoles, true);
    }
    
    /**
     * Parse session token
     * @param string $token Base64 encoded token
     * @return array|false Array with email, role, timestamp, random or false if invalid
     */
    public static function parseSessionToken(string $token) {
        $decoded = base64_decode($token, true);
        
        if (!$decoded || substr_count($decoded, '|') !== 3) {
            return false;
        }
        
        $parts = explode('|', $decoded);
        
        return [
            'email' => $parts[0],
            'role' => $parts[1],
            'timestamp' => (int)$parts[2],
            'random' => $parts[3]
        ];
    }
    
    /**
     * Check if token is expired
     * @param int $timestamp Token timestamp
     * @param int $maxAge Maximum age in seconds (default 30 days)
     * @return bool True if expired
     */
    public static function isTokenExpired(int $timestamp, int $maxAge = null): bool {
        $maxAge = $maxAge ?? (60 * 60 * 24 * 30); // 30 days default
        return (time() - $timestamp) > $maxAge;
    }
    
    /**
     * Generate secure OTP
     * @return string 6-digit OTP
     */
    public static function generateOTP(): string {
        return str_pad((string)random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
    }
    
    /**
     * Check if OTP is expired
     * @param int $timestamp OTP generation timestamp
     * @param int $maxAge Maximum age in seconds (default 10 minutes)
     * @return bool True if expired
     */
    public static function isOTPExpired(int $timestamp, int $maxAge = 600): bool {
        return (time() - $timestamp) > $maxAge;
    }
    
    /**
     * Sanitize and validate email
     * @param string $email Email to validate
     * @return string|false Sanitized email or false if invalid
     */
    public static function validateEmail(string $email) {
        $email = filter_var(trim($email), FILTER_VALIDATE_EMAIL);
        return $email !== false ? strtolower($email) : false;
    }
    
    /**
     * Create standardized error response
     * @param string $message Error message
     * @param int $httpCode HTTP status code
     * @return void
     */
    public static function sendErrorResponse(string $message, int $httpCode = 400): void {
        http_response_code($httpCode);
        echo json_encode(['status' => 'error', 'message' => $message]);
        exit;
    }
    
    /**
     * Create standardized success response
     * @param array $data Response data
     * @return void
     */
    public static function sendSuccessResponse(array $data): void {
        echo json_encode(array_merge(['status' => 'success'], $data));
        exit;
    }
}