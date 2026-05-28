<?php
/**
 * Logger — Thin Monolog wrapper for application logging.
 *
 * Usage:
 *   Logger::info('auth', 'OTP sent to user@example.com');
 *   Logger::error('db', 'Connection failed', ['host' => 'localhost']);
 */

declare(strict_types=1);

namespace App\Helpers;

use Monolog\Logger as MonologLogger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\RotatingFileHandler;

class Logger
{
    /** @var array<string, MonologLogger> Channel cache */
    private static array $channels = [];

    /** Base log directory */
    private static function logDir(): string
    {
        return dirname(__DIR__, 2) . '/logs';
    }

    /**
     * Get or create a Monolog logger for the given channel.
     *
     * @param string $channel Channel name (e.g., 'auth', 'db', 'api')
     * @return MonologLogger
     */
    private static function getChannel(string $channel): MonologLogger
    {
        if (!isset(self::$channels[$channel])) {
            $logDir = self::logDir();

            if (!is_dir($logDir)) {
                mkdir($logDir, 0755, true);
            }

            $logger = new MonologLogger($channel);
            $logger->pushHandler(
                new RotatingFileHandler("{$logDir}/{$channel}.log", 30, MonologLogger::DEBUG)
            );

            self::$channels[$channel] = $logger;
        }

        return self::$channels[$channel];
    }

    public static function debug(string $channel, string $message, array $context = []): void
    {
        self::getChannel($channel)->debug($message, $context);
    }

    public static function info(string $channel, string $message, array $context = []): void
    {
        self::getChannel($channel)->info($message, $context);
    }

    public static function warning(string $channel, string $message, array $context = []): void
    {
        self::getChannel($channel)->warning($message, $context);
    }

    public static function error(string $channel, string $message, array $context = []): void
    {
        self::getChannel($channel)->error($message, $context);
    }
}
