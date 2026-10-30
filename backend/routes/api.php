<?php

declare(strict_types=1);

use App\Controllers\AuthController;
use App\Core\Router;
use App\Middleware\AuthMiddleware;
use App\Middleware\RateLimitMiddleware;

/**
 * API route definitions.
 * Returns a closure so the bootstrapper (public/index.php) can register them
 * onto the Router instance after the global middleware is attached.
 *
 * Middleware specs use the "Class:arg1,arg2" notation.
 */
return function (Router $router): void {
    // Auth – public, but throttled to stop brute-force / signup abuse.
    $router->post('/auth/register', [AuthController::class, 'register'], [
        RateLimitMiddleware::class . ':register,5,60',
    ]);
    $router->post('/auth/login', [AuthController::class, 'login'], [
        RateLimitMiddleware::class . ':login,5,60',
    ]);
    $router->post('/auth/refresh', [AuthController::class, 'refresh'], [
        RateLimitMiddleware::class . ':refresh,10,60',
    ]);

    // Auth – requires a valid access token.
    $router->post('/auth/logout', [AuthController::class, 'logout'], [AuthMiddleware::class]);
    $router->get('/auth/me', [AuthController::class, 'me'], [AuthMiddleware::class]);
};
