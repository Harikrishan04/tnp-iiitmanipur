<?php
/**
 * API Front Controller — Single entry point for all API requests.
 *
 * Flow:
 *   1. Load environment (.env)
 *   2. Register PSR-4 autoloader
 *   3. Set response headers
 *   4. Parse request URI
 *   5. Dispatch to Router
 */

declare(strict_types=1);

// ─── Error Handling ──────────────────────────────────────────────────────────
error_reporting(E_ALL);
ini_set('display_errors', '0'); // Never display raw HTML errors in API responses
ini_set('log_errors', '1');

// Global exception handler to return 500 errors as JSON
set_exception_handler(function (\Throwable $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'error',
        'message' => 'Internal Server Error',
        'details' => $e->getMessage()
    ]);
    exit;
});

// ─── Composer Autoload ───────────────────────────────────────────────────────
require_once __DIR__ . '/vendor/autoload.php';

// ─── Load Environment ────────────────────────────────────────────────────────
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->safeLoad();

// Merge system environment variables into $_ENV for production/Render environments
foreach (getenv() as $key => $value) {
    if (!isset($_ENV[$key])) $_ENV[$key] = $value;
}
foreach ($_SERVER as $key => $value) {
    if (is_string($value) && !isset($_ENV[$key])) {
        $_ENV[$key] = $value;
    }
}

// ─── PSR-4 Autoloader for App namespace ──────────────────────────────────────
spl_autoload_register(function (string $class) {
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/';

    // Check if the class uses the App\ prefix
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    // Map namespace to directory structure
    $relativeClass = substr($class, $len);

    // Convert namespace separators to directory separators
    // App\Config\Database → config/Database.php
    // App\Controllers\AuthController → controllers/AuthController.php
    $parts = explode('\\', $relativeClass);
    $className = array_pop($parts);

    // Lowercase the directory parts to match our filesystem
    $dir = implode('/', array_map('strtolower', $parts));

    $file = $baseDir . $dir . '/' . $className . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});

// ─── Response Headers ────────────────────────────────────────────────────────
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// ─── Parse Request ───────────────────────────────────────────────────────────
$method = $_SERVER['REQUEST_METHOD'];
$uri    = $_SERVER['REQUEST_URI'];

// Remove the base path prefix to get the API-relative path
// Dynamically determine base path based on script location
$scriptName = $_SERVER['SCRIPT_NAME']; // e.g., /tnp@iiitmanipur/api/index.php or /api/index.php or /index.php
$basePath = dirname($scriptName);
if ($basePath === '\\' || $basePath === '/') {
    $basePath = '';
}

$path = parse_url($uri, PHP_URL_PATH);

if (str_starts_with($path, $basePath)) {
    $path = substr($path, strlen($basePath));
}

// Ensure leading slash, remove trailing slash
$path = '/' . ltrim($path, '/');
$path = rtrim($path, '/') ?: '/';

// ─── Route Dispatch ──────────────────────────────────────────────────────────
$router = new App\Routes\Router();

// Load route definitions
require_once __DIR__ . '/routes/api.php';

// Dispatch the request
$router->dispatch($method, $path);
