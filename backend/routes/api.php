<?php

declare(strict_types=1);

use App\Controllers\AdminController;
use App\Controllers\AuthController;
use App\Controllers\ContentController;
use App\Core\Router;
use App\Middleware\AdminMiddleware;
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

    // Google OAuth – full-page redirect flow (state cookie protects the callback).
    $router->get('/auth/google/redirect', [AuthController::class, 'googleRedirect'], [
        RateLimitMiddleware::class . ':google_redirect,20,60',
    ]);
    $router->get('/auth/google/callback', [AuthController::class, 'googleCallback'], [
        RateLimitMiddleware::class . ':google_callback,20,60',
    ]);

    // Auth – requires a valid access token.
    $router->post('/auth/logout', [AuthController::class, 'logout'], [AuthMiddleware::class]);
    $router->get('/auth/me', [AuthController::class, 'me'], [AuthMiddleware::class]);

    // Public site content.
    $router->get('/content/home', [ContentController::class, 'home']);
    $router->get('/collections', [ContentController::class, 'collections']);
    $router->get('/guidelines', [ContentController::class, 'guidelines']);
    $router->get('/mix-match', [ContentController::class, 'mixMatch']);
    $router->get('/news', [ContentController::class, 'news']);

    // Admin content management – every route requires an admin account.
    $admin = [AuthMiddleware::class, AdminMiddleware::class];

    // Collections
    $router->get('/admin/collections', [AdminController::class, 'collectionsIndex'], $admin);
    $router->post('/admin/collections', [AdminController::class, 'collectionStore'], $admin);
    $router->get('/admin/collections/{id}', [AdminController::class, 'collectionShow'], $admin);
    $router->put('/admin/collections/{id}', [AdminController::class, 'collectionUpdate'], $admin);
    $router->delete('/admin/collections/{id}', [AdminController::class, 'collectionDestroy'], $admin);

    // Guidelines
    $router->get('/admin/guidelines', [AdminController::class, 'guidelinesIndex'], $admin);
    $router->post('/admin/guidelines', [AdminController::class, 'guidelineStore'], $admin);
    $router->get('/admin/guidelines/{id}', [AdminController::class, 'guidelineShow'], $admin);
    $router->put('/admin/guidelines/{id}', [AdminController::class, 'guidelineUpdate'], $admin);
    $router->delete('/admin/guidelines/{id}', [AdminController::class, 'guidelineDestroy'], $admin);

    // Looks (Mix & Match)
    $router->get('/admin/looks', [AdminController::class, 'looksIndex'], $admin);
    $router->post('/admin/looks', [AdminController::class, 'lookStore'], $admin);
    $router->get('/admin/looks/{id}', [AdminController::class, 'lookShow'], $admin);
    $router->put('/admin/looks/{id}', [AdminController::class, 'lookUpdate'], $admin);
    $router->delete('/admin/looks/{id}', [AdminController::class, 'lookDestroy'], $admin);

    // News
    $router->get('/admin/news', [AdminController::class, 'newsIndex'], $admin);
    $router->post('/admin/news', [AdminController::class, 'newsStore'], $admin);
    $router->get('/admin/news/{id}', [AdminController::class, 'newsShow'], $admin);
    $router->put('/admin/news/{id}', [AdminController::class, 'newsUpdate'], $admin);
    $router->delete('/admin/news/{id}', [AdminController::class, 'newsDestroy'], $admin);

    // Home – settings + featured, stats and marquee sub-resources.
    $router->get('/admin/home', [AdminController::class, 'homeShow'], $admin);
    $router->put('/admin/home/settings', [AdminController::class, 'homeSettingsUpdate'], $admin);

    $router->post('/admin/home/featured', [AdminController::class, 'homeFeaturedStore'], $admin);
    $router->put('/admin/home/featured/{id}', [AdminController::class, 'homeFeaturedUpdate'], $admin);
    $router->delete('/admin/home/featured/{id}', [AdminController::class, 'homeFeaturedDestroy'], $admin);

    $router->post('/admin/home/stats', [AdminController::class, 'homeStatsStore'], $admin);
    $router->put('/admin/home/stats/{id}', [AdminController::class, 'homeStatsUpdate'], $admin);
    $router->delete('/admin/home/stats/{id}', [AdminController::class, 'homeStatsDestroy'], $admin);

    $router->post('/admin/home/marquee', [AdminController::class, 'homeMarqueeStore'], $admin);
    $router->put('/admin/home/marquee/{id}', [AdminController::class, 'homeMarqueeUpdate'], $admin);
    $router->delete('/admin/home/marquee/{id}', [AdminController::class, 'homeMarqueeDestroy'], $admin);
};
