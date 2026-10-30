<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Helpers for sending JSON responses and managing cookies.
 */
class Response
{
    /** Send a JSON response and stop execution. */
    public static function json(mixed $data, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    /** Send a JSON error payload and stop execution. */
    public static function error(string $message, int $status = 400, array $errors = []): never
    {
        $payload = ['message' => $message];
        if ($errors) {
            $payload['errors'] = $errors;
        }
        self::json($payload, $status);
    }

    /** Set an HttpOnly cookie. Flags default to secure in production. */
    public static function setCookie(
        string $name,
        string $value,
        int $ttl,
        array $options = [],
    ): void {
        $secure    = $options['secure'] ?? self::isHttps();
        $path      = $options['path'] ?? '/';
        $domain    = $options['domain'] ?? '';
        $samesite  = $options['samesite'] ?? 'Lax';

        $parts = [
            rawurlencode($name) . '=' . rawurlencode($value),
            'Max-Age=' . $ttl,
            'Path=' . $path,
        ];
        if ($domain !== '') {
            $parts[] = 'Domain=' . $domain;
        }
        $parts[] = 'HttpOnly';
        $parts[] = 'SameSite=' . $samesite;
        if ($secure) {
            $parts[] = 'Secure';
        }

        header('Set-Cookie: ' . implode('; ', $parts), false);
    }

    /** Delete a cookie by setting it expired. */
    public static function clearCookie(string $name, array $options = []): void
    {
        self::setCookie($name, '', 0, $options);
    }

    public static function isHttps(): bool
    {
        if (!empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off') {
            return true;
        }
        $proto = $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '';
        return str_contains((string) $proto, 'https');
    }
}
