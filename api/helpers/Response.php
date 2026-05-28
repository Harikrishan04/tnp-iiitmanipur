<?php
/**
 * Response — Standardized JSON response helper.
 *
 * Usage:
 *   Response::success(['user' => $data]);
 *   Response::error('Validation failed', 422, ['email' => 'Required']);
 *   Response::paginated($rows, $page, $perPage, $total);
 */

declare(strict_types=1);

namespace App\Helpers;

class Response
{
    /**
     * Send a success JSON response and exit.
     *
     * @param mixed  $data     Response payload
     * @param int    $httpCode HTTP status code (default 200)
     * @param string $message  Optional success message
     */
    public static function success(mixed $data = null, int $httpCode = 200, string $message = ''): never
    {
        http_response_code($httpCode);
        $response = ['status' => 'success'];

        if ($message !== '') {
            $response['message'] = $message;
        }

        if ($data !== null) {
            $response['data'] = $data;
        }

        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Send a paginated success response and exit.
     *
     * @param array $rows    Data rows for current page
     * @param int   $page    Current page number
     * @param int   $perPage Items per page
     * @param int   $total   Total matching records
     */
    public static function paginated(array $rows, int $page, int $perPage, int $total): never
    {
        http_response_code(200);
        echo json_encode([
            'status' => 'success',
            'data'   => $rows,
            'meta'   => [
                'page'       => $page,
                'per_page'   => $perPage,
                'total'      => $total,
                'total_pages' => (int) ceil($total / max($perPage, 1)),
            ],
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Send an error JSON response and exit.
     *
     * @param string   $message  Human-readable error message
     * @param int      $httpCode HTTP status code (default 400)
     * @param array    $errors   Optional field-level error details
     */
    public static function error(string $message, int $httpCode = 400, array $errors = []): never
    {
        http_response_code($httpCode);
        $response = [
            'status'  => 'error',
            'message' => $message,
        ];

        if (!empty($errors)) {
            $response['errors'] = $errors;
        }

        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Send a 404 not found response.
     */
    public static function notFound(string $message = 'Resource not found.'): never
    {
        self::error($message, 404);
    }

    /**
     * Send a 401 unauthorized response.
     */
    public static function unauthorized(string $message = 'Authentication required.'): never
    {
        self::error($message, 401);
    }

    /**
     * Send a 403 forbidden response.
     */
    public static function forbidden(string $message = 'Access denied.'): never
    {
        self::error($message, 403);
    }
}
