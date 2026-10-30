<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Utils\Jwt;

/**
 * Protects routes by requiring a valid JWT access token.
 *
 * The token is read from the httpOnly cookie set at login, with a fallback to
 * an Authorization: Bearer header for API clients that prefer that.
 * On success the decoded claims are attached to the request for handlers.
 */
class AuthMiddleware implements Middleware
{
    public function __construct(array $params = []) {}

    public function handle(Request $request, callable $next): void
    {
        $token = $request->cookie('access_token');

        if ($token === null) {
            $auth = $request->header('Authorization') ?? '';
            if (stripos($auth, 'Bearer ') === 0) {
                $token = substr($auth, 7);
            }
        }

        $claims = Jwt::verify($token);
        if ($claims === null) {
            Response::error('Unauthenticated.', 401);
        }

        $request->setUser($claims);
        $next($request);
    }
}
