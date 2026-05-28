<?php
/**
 * AuthMiddleware — Extracts and validates JWT from the Authorization header.
 *
 * Usage:
 *   $user = AuthMiddleware::authenticate();          // dies with 401 if invalid
 *   $user = AuthMiddleware::authenticateOptional();   // returns null if no token
 *   AuthMiddleware::requireRole($user, ['admin']);    // dies with 403 if wrong role
 */

declare(strict_types=1);

namespace App\Middleware;

use App\Config\JwtConfig;
use App\Helpers\Response;

class AuthMiddleware
{
    /**
     * Extract the Bearer token from the Authorization header.
     *
     * @return string|null Raw JWT string, or null if not present
     */
    private static function extractToken(): ?string
    {
        // Check Authorization header
        $header = $_SERVER['HTTP_AUTHORIZATION']
            ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION']
            ?? '';

        if (preg_match('/^Bearer\s+(.+)$/i', $header, $matches)) {
            return $matches[1];
        }

        // Fallback: check for Apache mod_rewrite passing via getallheaders()
        if (function_exists('getallheaders')) {
            $headers = getallheaders();
            $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';
            if (preg_match('/^Bearer\s+(.+)$/i', $authHeader, $matches)) {
                return $matches[1];
            }
        }

        return null;
    }

    /**
     * Authenticate the request. Returns decoded user claims or dies with 401.
     *
     * @return array User claims: ['sub', 'role', 'email', 'iat', 'exp']
     */
    public static function authenticate(): array
    {
        $token = self::extractToken();

        if ($token === null) {
            Response::unauthorized('Missing authentication token.');
        }

        $claims = JwtConfig::validate($token);

        if ($claims === null) {
            Response::unauthorized('Invalid or expired token.');
        }

        return $claims;
    }

    /**
     * Optionally authenticate. Returns claims if token present and valid, null otherwise.
     *
     * @return array|null
     */
    public static function authenticateOptional(): ?array
    {
        $token = self::extractToken();

        if ($token === null) {
            return null;
        }

        return JwtConfig::validate($token);
    }

    /**
     * Require the authenticated user to have one of the specified roles.
     * Dies with 403 if role doesn't match.
     *
     * @param array  $user          Decoded JWT claims
     * @param array  $allowedRoles  e.g. ['admin', 'coordinator']
     */
    public static function requireRole(array $user, array $allowedRoles): void
    {
        if (!in_array($user['role'] ?? '', $allowedRoles, true)) {
            Response::forbidden('You do not have permission to access this resource.');
        }
    }
}
