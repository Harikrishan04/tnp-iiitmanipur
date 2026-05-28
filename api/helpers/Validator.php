<?php
/**
 * Validator — Common input validation helpers.
 *
 * Usage:
 *   Validator::requireFields($input, ['email', 'role']);
 *   Validator::email($input['email']);
 *   Validator::uuid($input['id']);
 */

declare(strict_types=1);

namespace App\Helpers;

class Validator
{
    /**
     * Check that all required fields exist and are non-empty in the input array.
     *
     * @param array  $input    Input data
     * @param array  $fields   List of required field names
     * @return array           Associative array of field => error message (empty if valid)
     */
    public static function requireFields(array $input, array $fields): array
    {
        $errors = [];

        foreach ($fields as $field) {
            if (!isset($input[$field]) || (is_string($input[$field]) && trim($input[$field]) === '')) {
                $errors[$field] = ucfirst(str_replace('_', ' ', $field)) . ' is required.';
            }
        }

        return $errors;
    }

    /**
     * Validate an email address.
     *
     * @param string $email
     * @return string|false Normalized lowercase email, or false if invalid
     */
    public static function email(string $email): string|false
    {
        $clean = filter_var(trim($email), FILTER_VALIDATE_EMAIL);
        return $clean !== false ? strtolower($clean) : false;
    }

    /**
     * Validate an email belongs to the IIIT Manipur domain.
     */
    public static function isIiitEmail(string $email): bool
    {
        $domain = strtolower(substr(strrchr($email, '@'), 1));
        return $domain === 'iiitmanipur.ac.in';
    }

    /**
     * Validate a UUID v4 string.
     */
    public static function uuid(string $value): bool
    {
        return (bool) preg_match(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i',
            $value
        );
    }

    /**
     * Validate a 6-digit OTP.
     */
    public static function otp(string $value): bool
    {
        return (bool) preg_match('/^\d{6}$/', $value);
    }

    /**
     * Validate a phone number (10-15 digits).
     */
    public static function phone(string $value): bool
    {
        return (bool) preg_match('/^[0-9]{10,15}$/', $value);
    }

    /**
     * Validate a role string.
     *
     * @param string $role
     * @param array  $allowed Allowed role names
     */
    public static function role(string $role, array $allowed = ['student', 'recruiter', 'coordinator', 'admin']): bool
    {
        return in_array($role, $allowed, true);
    }

    /**
     * Sanitize a string — trim and strip tags.
     */
    public static function sanitize(string $value): string
    {
        return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Parse pagination parameters from input.
     *
     * @param array $input Query parameters
     * @return array ['page' => int, 'per_page' => int, 'offset' => int]
     */
    public static function pagination(array $input, int $defaultPerPage = 20, int $maxPerPage = 100): array
    {
        $page    = max(1, (int) ($input['page'] ?? 1));
        $perPage = min($maxPerPage, max(1, (int) ($input['per_page'] ?? $defaultPerPage)));
        $offset  = ($page - 1) * $perPage;

        return ['page' => $page, 'per_page' => $perPage, 'offset' => $offset];
    }
}
