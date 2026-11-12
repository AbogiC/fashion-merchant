<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;

/**
 * Restricts a route to authenticated admin users.
 *
 * Must run AFTER AuthMiddleware, which decodes the JWT and attaches the
 * user claims to the request. The "role" claim is set at token issue time
 * from the users table, so only accounts whose role is "admin" may pass.
 */
class AdminMiddleware implements Middleware
{
    public function __construct(array $params = []) {}

    public function handle(Request $request, callable $next): void
    {
        $user = $request->user();

        if ($user === null || ($user['role'] ?? 'user') !== 'admin') {
            Response::error('Administrator access required.', 403);
        }

        $next($request);
    }
}
