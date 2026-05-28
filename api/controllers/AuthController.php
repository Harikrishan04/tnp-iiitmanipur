<?php
/**
 * AuthController — Handles authentication API endpoints.
 *
 * Routes:
 *   POST /auth/login      → sendOtp (send OTP to email)
 *   POST /auth/verify-otp → verifyOtp (verify OTP, return JWT)
 *   POST /auth/refresh    → refresh (refresh JWT token)
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Config\AppConfig;
use App\Helpers\Response;
use App\Helpers\Validator;
use App\Helpers\Logger;
use App\Services\AuthService;

class AuthController
{
    private AuthService $authService;

    public function __construct()
    {
        $this->authService = new AuthService();
    }

    /**
     * POST /auth/login
     *
     * Request body: { "email": "...", "role": "student" }
     * Response:     { "status": "success", "message": "OTP sent..." }
     */
    public function login(array $params = [], ?array $user = null): void
    {
        $input = $this->getJsonInput();

        // Validate required fields
        $errors = Validator::requireFields($input, ['email', 'role']);
        if (!empty($errors)) {
            Response::error('Validation failed.', 422, $errors);
        }

        // Validate email format
        $email = Validator::email($input['email']);
        if ($email === false) {
            Response::error('Invalid email format.', 422, ['email' => 'Please enter a valid email address.']);
        }

        // Validate role
        $role = strtolower(trim($input['role']));
        if (!Validator::role($role)) {
            Response::error('Invalid role.', 422, ['role' => 'Please select a valid role.']);
        }

        // Delegate to service
        $result = $this->authService->sendOtp($email, $role);

        if ($result['success']) {
            Response::success(null, 200, $result['message']);
        } else {
            Response::error($result['message']);
        }
    }

    /**
     * POST /auth/verify-otp
     *
     * Request body: { "email": "...", "otp": "123456", "role": "student" }
     * Response:     { "status": "success", "data": { "token": "...", "user": {...} } }
     */
    public function verifyOtp(array $params = [], ?array $user = null): void
    {
        $input = $this->getJsonInput();

        // Validate required fields
        $errors = Validator::requireFields($input, ['email', 'otp', 'role']);
        if (!empty($errors)) {
            Response::error('Validation failed.', 422, $errors);
        }

        // Validate email
        $email = Validator::email($input['email']);
        if ($email === false) {
            Response::error('Invalid email format.', 422);
        }

        // Validate OTP format
        $otp = trim($input['otp']);
        if (!Validator::otp($otp)) {
            Response::error('OTP must be a 6-digit number.', 422, ['otp' => 'Enter a valid 6-digit OTP.']);
        }

        // Validate role
        $role = strtolower(trim($input['role']));
        if (!Validator::role($role)) {
            Response::error('Invalid role.', 422);
        }

        // Delegate to service
        $result = $this->authService->verifyOtp($email, $otp, $role);

        if ($result['success']) {
            Response::success([
                'token' => $result['token'],
                'user'  => $result['user'],
            ], 200, $result['message']);
        } else {
            Response::error($result['message']);
        }
    }

    /**
     * POST /auth/refresh
     *
     * Requires: valid JWT in Authorization header
     * Response: { "status": "success", "data": { "token": "..." } }
     */
    public function refresh(array $params = [], ?array $user = null): void
    {
        if ($user === null) {
            Response::unauthorized('Authentication required.');
        }

        $result = $this->authService->refreshToken($user);

        if ($result['success']) {
            Response::success(['token' => $result['token']], 200, 'Token refreshed.');
        } else {
            Response::error($result['message'], 401);
        }
    }

    /**
     * Parse JSON request body.
     *
     * @return array Decoded JSON input
     */
    private function getJsonInput(): array
    {
        $raw = file_get_contents('php://input');
        $input = json_decode($raw, true);

        if (!is_array($input)) {
            Response::error('Invalid JSON input.', 400);
        }

        return $input;
    }
}
