<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Env;
use App\Core\Request;

/**
 * Handles Cross-Origin Resource Sharing.
 *
 * Because the SPA sends credentials (cookies), the allowed origin must be an
 * explicit value (not "*") and Access-Control-Allow-Credentials must be true.
 */
class CorsMiddleware implements Middleware
{
    public function __construct(array $params = []) {}

    public function handle(Request $request, callable $next): void
    {
        $allowed = Env::get('CORS_ORIGIN', '');
        $origin = $request->header('Origin');

        if ($origin !== null) {
            // Support a comma separated list of allowed origins.
            $origins = array_filter(array_map('trim', explode(',', $allowed)));
            if (in_array($origin, $origins, true) || $allowed === '*') {
                header('Access-Control-Allow-Origin: ' . $origin);
                header('Vary: Origin');
            } elseif ($allowed === '*') {
                header('Access-Control-Allow-Origin: *');
            }
        }

        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-HTTP-Method-Override');
        header('Access-Control-Max-Age: 86400');

        // Respond to preflight requests immediately.
        if ($request->method() === 'OPTIONS') {
            http_response_code(204);
            exit;
        }

        $next($request);
    }
}
