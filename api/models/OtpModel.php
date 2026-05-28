<?php
/**
 * OtpModel — Data access layer for the `otp_requests` table.
 *
 * Replaces the legacy JSON file-based OTP storage with database-backed
 * storage supporting hashing, expiry, and attempt tracking.
 */

declare(strict_types=1);

namespace App\Models;

use PDO;

class OtpModel
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Create a new OTP request in the database.
     *
     * @param string $userId   User ID
     * @param string $otpHash  SHA-256 hash of the OTP
     * @param string $purpose  OTP purpose (login, verify_profile, sensitive_action)
     * @param int    $expiryMinutes  Expiry time in minutes
     * @param string|null $ipAddress Client IP address
     * @return string OTP request ID
     */
    public function create(
        string $userId,
        string $otpHash,
        string $purpose = 'login',
        int $expiryMinutes = 5,
        ?string $ipAddress = null
    ): string {
        // Invalidate all previous unused OTPs for this user + purpose
        $this->invalidatePending($userId, $purpose);

        $stmt = $this->db->prepare(
            "INSERT INTO otp_requests (user_id, otp_hash, channel, purpose, expires_at, ip_address)
             VALUES (?, ?, 'email', ?, DATE_ADD(NOW(), INTERVAL ? MINUTE), ?)"
        );
        $stmt->execute([$userId, $otpHash, $purpose, $expiryMinutes, $ipAddress]);

        // Get the auto-generated otp_id
        $stmt = $this->db->prepare(
            "SELECT otp_id FROM otp_requests
             WHERE user_id = ? AND otp_hash = ? AND purpose = ?
             ORDER BY created_at DESC LIMIT 1"
        );
        $stmt->execute([$userId, $otpHash, $purpose]);
        $row = $stmt->fetch();

        return $row['otp_id'];
    }

    /**
     * Validate an OTP and mark it as used if valid.
     *
     * @param string $userId
     * @param string $otpHash SHA-256 hash of the entered OTP
     * @param string $purpose
     * @return string Result: 'ok', 'not_found', 'expired', 'used', 'invalid', 'max_attempts'
     */
    public function validate(string $userId, string $otpHash, string $purpose = 'login'): string
    {
        // Find the most recent matching OTP
        $stmt = $this->db->prepare(
            "SELECT otp_id, attempts, expires_at, used_at, is_invalidated
             FROM otp_requests
             WHERE user_id = ? AND purpose = ? AND is_invalidated = FALSE
             ORDER BY created_at DESC
             LIMIT 1
             FOR UPDATE"
        );
        $stmt->execute([$userId, $purpose]);
        $otp = $stmt->fetch();

        if (!$otp) {
            return 'not_found';
        }

        if ($otp['is_invalidated']) {
            return 'invalid';
        }

        if ($otp['used_at'] !== null) {
            return 'used';
        }

        if (strtotime($otp['expires_at']) < time()) {
            return 'expired';
        }

        $maxAttempts = (int) ($_ENV['OTP_MAX_ATTEMPTS'] ?? 3);
        if ($otp['attempts'] >= $maxAttempts) {
            return 'max_attempts';
        }

        // Increment attempt counter
        $this->incrementAttempts($otp['otp_id']);

        // Check if hash matches (compare against stored hash)
        // We need to fetch the stored hash to compare
        $stmt = $this->db->prepare(
            "SELECT otp_hash FROM otp_requests WHERE otp_id = ?"
        );
        $stmt->execute([$otp['otp_id']]);
        $row = $stmt->fetch();

        if ($row['otp_hash'] !== $otpHash) {
            return 'invalid';
        }

        // Mark as used
        $stmt = $this->db->prepare(
            "UPDATE otp_requests SET used_at = NOW() WHERE otp_id = ?"
        );
        $stmt->execute([$otp['otp_id']]);

        return 'ok';
    }

    /**
     * Invalidate all pending OTPs for a user + purpose.
     */
    public function invalidatePending(string $userId, string $purpose = 'login'): void
    {
        $stmt = $this->db->prepare(
            "UPDATE otp_requests
             SET is_invalidated = TRUE
             WHERE user_id = ? AND purpose = ? AND used_at IS NULL AND is_invalidated = FALSE"
        );
        $stmt->execute([$userId, $purpose]);
    }

    /**
     * Increment the attempt counter for an OTP.
     */
    private function incrementAttempts(string $otpId): void
    {
        $stmt = $this->db->prepare(
            "UPDATE otp_requests SET attempts = attempts + 1 WHERE otp_id = ?"
        );
        $stmt->execute([$otpId]);
    }

    /**
     * Check rate limit — count OTPs created in the last N minutes.
     *
     * @param string $userId
     * @param int    $windowMinutes Time window in minutes
     * @param int    $maxCount      Max allowed in window
     * @return bool  True if rate limit exceeded
     */
    public function isRateLimited(string $userId, int $windowMinutes = 5, int $maxCount = 3): bool
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) AS cnt FROM otp_requests
             WHERE user_id = ? AND created_at > DATE_SUB(NOW(), INTERVAL ? MINUTE)"
        );
        $stmt->execute([$userId, $windowMinutes]);
        $row = $stmt->fetch();

        return ($row['cnt'] ?? 0) >= $maxCount;
    }
}
