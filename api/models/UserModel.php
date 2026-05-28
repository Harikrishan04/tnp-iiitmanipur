<?php
/**
 * UserModel — Data access layer for the `users` table.
 */

declare(strict_types=1);

namespace App\Models;

use PDO;

class UserModel
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Find a user by email.
     *
     * @return array|null User row or null
     */
    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT u.user_id, u.email, u.phone, u.role_id, u.is_active,
                    u.account_activated, u.first_login_at, u.last_login_at,
                    r.name AS role_name
             FROM users u
             JOIN roles r ON r.role_id = u.role_id
             WHERE u.email = ?"
        );
        $stmt->execute([$email]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Find a user by email AND role name.
     *
     * @return array|null User row or null
     */
    public function findByEmailAndRole(string $email, string $roleName): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT u.user_id, u.email, u.phone, u.role_id, u.is_active,
                    u.account_activated, u.first_login_at, u.last_login_at,
                    r.name AS role_name
             FROM users u
             JOIN roles r ON r.role_id = u.role_id
             WHERE u.email = ? AND r.name = ?"
        );
        $stmt->execute([$email, $roleName]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Find a user by user_id.
     *
     * @return array|null User row or null
     */
    public function findById(string $userId): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT u.user_id, u.email, u.phone, u.role_id, u.is_active,
                    u.account_activated, u.first_login_at, u.last_login_at,
                    r.name AS role_name
             FROM users u
             JOIN roles r ON r.role_id = u.role_id
             WHERE u.user_id = ?"
        );
        $stmt->execute([$userId]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Create a new user. Returns the generated user_id.
     * The trg_after_user_insert trigger auto-creates student/recruiter profile rows.
     *
     * @param string $email
     * @param string $roleName  Role name (e.g., 'student', 'recruiter')
     * @return string           The new user_id (UUID)
     */
    public function create(string $email, string $roleName): string
    {
        // Look up role_id by name — never hardcode
        $stmt = $this->db->prepare("SELECT role_id FROM roles WHERE name = ?");
        $stmt->execute([$roleName]);
        $role = $stmt->fetch();

        if (!$role) {
            throw new \RuntimeException("Invalid role: {$roleName}");
        }

        $userId = $this->generateUuid();

        $stmt = $this->db->prepare(
            "INSERT INTO users (user_id, email, role_id, is_active, account_activated, preferred_otp_channel)
             VALUES (?, ?, ?, TRUE, FALSE, 'email')"
        );
        $stmt->execute([$userId, $email, $role['role_id']]);

        return $userId;
    }

    /**
     * Update last_login_at and mark account as activated.
     */
    public function updateLoginTimestamp(string $userId): void
    {
        $stmt = $this->db->prepare(
            "UPDATE users
             SET last_login_at    = NOW(),
                 account_activated = TRUE,
                 first_login_at   = COALESCE(first_login_at, NOW())
             WHERE user_id = ?"
        );
        $stmt->execute([$userId]);
    }

    /**
     * Generate a UUID v4.
     */
    private function generateUuid(): string
    {
        // Use ramsey/uuid if available, fallback to MySQL UUID()
        if (class_exists(\Ramsey\Uuid\Uuid::class)) {
            return \Ramsey\Uuid\Uuid::uuid4()->toString();
        }

        $stmt = $this->db->query("SELECT UUID() AS id");
        return $stmt->fetch()['id'];
    }
}
