<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;

/**
 * CSRF defense for state-changing requests.
 *
 * Browsers can perform "simple" cross-site form POSTs but cannot set custom
 * request headers cross-origin without a CORS preflight. By requiring the
 * X-Requested-With header on every non-safe method, cross-site form/CSRF
 * attempts are blocked while our own SPA (which always sends it) passes.
 *
 * This works alongside SameSite cookies and the CORS preflight as defense in depth.
 */
class EnsureAjaxMiddleware implements Middleware
{
    public function __construct(array $params = []) {}

    public function handle(Request $request, callable $next): void
    {
        $method = $request->method();

        if (!in_array($method, ['GET', 'HEAD', 'OPTIONS'], true)) {
            $header = strtolower($request->header('X-Requested-With') ?? '');
            if ($header !== 'xmlhttprequest') {
                Response::error('Invalid request origin.', 419);
            }
        }

        $next($request);
    }
}
