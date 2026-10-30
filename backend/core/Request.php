<?php

declare(strict_types=1);

namespace App\Core;

/**
 * A thin wrapper around the superglobals so handlers and middleware never
 * touch $_GET/$_POST/$_SERVER directly.
 */
class Request
{
    private ?array $body = null;
    private ?array $user = null;
    private array $params = [];

    public function method(): string
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

        // Support method spoofing via the "_method" form field or X-HTTP-Method-Override.
        $override = $this->header('X-HTTP-Method-Override');
        if ($method === 'POST' && $override !== null) {
            $method = strtoupper($override);
        }

        return $method;
    }

    public function path(): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url($uri, PHP_URL_PATH);
        return $path === false ? '/' : $path;
    }

    /** Decode the JSON request body once and cache it. */
    public function body(): array
    {
        if ($this->body !== null) {
            return $this->body;
        }

        $this->body = [];

        $method = $this->method();
        if (in_array($method, ['GET', 'HEAD'], true)) {
            return $this->body;
        }

        $raw = file_get_contents('php://input');
        if ($raw === false || $raw === '') {
            return $this->body;
        }

        $decoded = json_decode($raw, true);
        if (is_array($decoded)) {
            $this->body = $decoded;
        }

        return $this->body;
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $this->body()[$key] ?? $default;
    }

    public function query(string $key, mixed $default = null): mixed
    {
        return $_GET[$key] ?? $default;
    }

    /** Case-insensitive header lookup. */
    public function header(string $name, ?string $default = null): ?string
    {
        $name = strtolower($name);

        // Map common CGI-style headers.
        $map = ['content-type' => 'CONTENT_TYPE', 'content-length' => 'CONTENT_LENGTH'];
        if (isset($map[$name]) && isset($_SERVER[$map[$name]])) {
            return $_SERVER[$map[$name]];
        }

        foreach ($_SERVER as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $header = strtolower(str_replace('_', '-', substr($key, 5)));
                if ($header === $name) {
                    return $value;
                }
            }
        }

        return $default;
    }

    public function ip(): string
    {
        // Only trust X-Forwarded-For when explicitly enabled (e.g. behind a trusted proxy).
        if (Env::bool('TRUSTED_PROXY', false)) {
            $forwarded = $this->header('X-Forwarded-For');
            if ($forwarded !== null) {
                return trim(explode(',', $forwarded)[0]);
            }
        }
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    public function cookie(string $name, ?string $default = null): ?string
    {
        return $_COOKIE[$name] ?? $default;
    }

    public function setUser(array $user): void
    {
        $this->user = $user;
    }

    public function user(): ?array
    {
        return $this->user;
    }

    public function setParams(array $params): void
    {
        $this->params = $params;
    }

    public function param(string $name, mixed $default = null): mixed
    {
        return $this->params[$name] ?? $default;
    }
}
