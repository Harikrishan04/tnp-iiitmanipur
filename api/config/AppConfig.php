<?php
/**
 * AppConfig — Application-wide constants and settings.
 */

declare(strict_types=1);

namespace App\Config;

class AppConfig
{
    /** Supported user roles */
    public const ROLES = ['student', 'recruiter', 'coordinator', 'admin'];

    /** Roles allowed to self-register (via OTP login) */
    public const SELF_REGISTER_ROLES = ['student', 'recruiter'];

    /** Roles that require pre-registration by admin */
    public const ADMIN_CREATED_ROLES = ['coordinator', 'admin'];

    /** Default pagination */
    public const DEFAULT_PAGE_SIZE = 20;
    public const MAX_PAGE_SIZE = 100;

    /** Upload limits */
    public const MAX_RESUME_SIZE_MB = 5;
    public const ALLOWED_RESUME_TYPES = ['application/pdf'];

    /**
     * Check if the app is in debug mode.
     */
    public static function isDebug(): bool
    {
        return ($_ENV['APP_DEBUG'] ?? 'false') === 'true';
    }

    /**
     * Get the application base URL.
     */
    public static function baseUrl(): string
    {
        return $_ENV['APP_URL'] ?? 'http://localhost';
    }
}
