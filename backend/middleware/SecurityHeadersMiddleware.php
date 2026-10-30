<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Request;

/**
 * Adds defensive HTTP headers to every API response.
 */
class SecurityHeadersMiddleware implements Middleware
{
    public function __construct(array $params = []) {}

    public function handle(Request $request, callable $next): void
    {
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header('Referrer-Policy: no-referrer');
        header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
        header('Cross-Origin-Resource-Policy: same-origin');

        $next($request);
    }
}
