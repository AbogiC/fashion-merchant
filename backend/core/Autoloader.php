<?php

declare(strict_types=1);

/**
 * Minimal PSR-4 style autoloader so the project runs without Composer.
 * Maps the "App\" namespace to this directory, lowercasing the first
 * segment so it matches the lowercase directory names (core, middleware, ...).
 */

namespace App\Core;

class Autoloader
{
    public static function register(): void
    {
        spl_autoload_register([self::class, 'load']);
    }

    public static function load(string $class): void
    {
        $prefix = 'App\\';
        if (!str_starts_with($class, $prefix)) {
            return;
        }

        $relative = substr($class, strlen($prefix));
        $parts = explode('\\', $relative);
        if (count($parts) === 1) {
            return;
        }

        // First segment = directory (kept lowercase), rest = PascalCase file name.
        $parts[0] = strtolower($parts[0]);
        $file = dirname(__DIR__) . '/' . implode('/', $parts) . '.php';

        if (is_file($file)) {
            require $file;
        }
    }
}
