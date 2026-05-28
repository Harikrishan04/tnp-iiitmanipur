<?php
/**
 * Router — Lightweight RESTful route dispatcher.
 *
 * Supports:
 *   - HTTP method matching (GET, POST, PUT, DELETE)
 *   - Path parameters (e.g., /jobs/{id})
 *   - Middleware (auth, role checks) per route
 *   - Route grouping by prefix
 *
 * Usage in routes/api.php:
 *   $router->post('/auth/login', [AuthController::class, 'login']);
 *   $router->get('/students/me', [StudentController::class, 'me'], ['auth']);
 *   $router->get('/jobs/{id}', [JobController::class, 'show'], ['auth']);
 */

declare(strict_types=1);

namespace App\Routes;

use App\Helpers\Response;
use App\Middleware\AuthMiddleware;

class Router
{
    /** @var array Registered routes grouped by HTTP method */
    private array $routes = [];

    /** @var string Current route prefix for grouping */
    private string $prefix = '';

    /**
     * Add a route group with a shared prefix.
     *
     * @param string   $prefix   URL prefix (e.g., '/auth')
     * @param callable $callback Receives $router to register sub-routes
     */
    public function group(string $prefix, callable $callback): void
    {
        $previousPrefix = $this->prefix;
        $this->prefix .= $prefix;
        $callback($this);
        $this->prefix = $previousPrefix;
    }

    /**
     * Register a GET route.
     */
    public function get(string $path, array $handler, array $middleware = []): void
    {
        $this->addRoute('GET', $path, $handler, $middleware);
    }

    /**
     * Register a POST route.
     */
    public function post(string $path, array $handler, array $middleware = []): void
    {
        $this->addRoute('POST', $path, $handler, $middleware);
    }

    /**
     * Register a PUT route.
     */
    public function put(string $path, array $handler, array $middleware = []): void
    {
        $this->addRoute('PUT', $path, $handler, $middleware);
    }

    /**
     * Register a DELETE route.
     */
    public function delete(string $path, array $handler, array $middleware = []): void
    {
        $this->addRoute('DELETE', $path, $handler, $middleware);
    }

    /**
     * Internal: register a route entry.
     */
    private function addRoute(string $method, string $path, array $handler, array $middleware): void
    {
        $fullPath = $this->prefix . $path;

        $this->routes[$method][] = [
            'pattern'    => $this->pathToRegex($fullPath),
            'handler'    => $handler,
            'middleware'  => $middleware,
            'paramNames' => $this->extractParamNames($fullPath),
        ];
    }

    /**
     * Convert a route path to a regex pattern.
     * e.g., '/jobs/{id}' → '#^/jobs/([^/]+)$#'
     */
    private function pathToRegex(string $path): string
    {
        $pattern = preg_replace('#\{([a-zA-Z_]+)\}#', '([^/]+)', $path);
        return '#^' . $pattern . '$#';
    }

    /**
     * Extract named parameter placeholders from a path.
     * e.g., '/jobs/{id}/rounds' → ['id']
     */
    private function extractParamNames(string $path): array
    {
        preg_match_all('#\{([a-zA-Z_]+)\}#', $path, $matches);
        return $matches[1];
    }

    /**
     * Dispatch the current request to the matching route.
     *
     * @param string $method  HTTP method (GET, POST, PUT, DELETE)
     * @param string $uri     Request URI path (e.g., '/jobs/abc-123')
     */
    public function dispatch(string $method, string $uri): void
    {
        // Handle CORS preflight
        if ($method === 'OPTIONS') {
            http_response_code(204);
            exit;
        }

        $routes = $this->routes[$method] ?? [];

        foreach ($routes as $route) {
            if (preg_match($route['pattern'], $uri, $matches)) {
                array_shift($matches); // Remove full match

                // Build named params
                $params = [];
                foreach ($route['paramNames'] as $index => $name) {
                    $params[$name] = $matches[$index] ?? null;
                }

                // Run middleware
                $user = $this->runMiddleware($route['middleware']);

                // Instantiate controller and call method
                [$controllerClass, $methodName] = $route['handler'];
                $controller = new $controllerClass();
                $controller->$methodName($params, $user);
                return;
            }
        }

        // No route matched
        Response::notFound("Route not found: {$method} {$uri}");
    }

    /**
     * Run middleware stack. Returns authenticated user claims or null.
     *
     * @param array $middleware List of middleware identifiers
     * @return array|null       User claims if authenticated
     */
    private function runMiddleware(array $middleware): ?array
    {
        $user = null;

        foreach ($middleware as $mw) {
            if ($mw === 'auth') {
                $user = AuthMiddleware::authenticate();
            } elseif (str_starts_with($mw, 'role:')) {
                $roles = explode(',', substr($mw, 5));
                if ($user === null) {
                    $user = AuthMiddleware::authenticate();
                }
                AuthMiddleware::requireRole($user, $roles);
            }
        }

        return $user;
    }
}
