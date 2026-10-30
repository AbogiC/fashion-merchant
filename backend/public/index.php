<?php

declare(strict_types=1);

/**
 * Front controller – the only file that should be web-accessible.
 * Point your web server / PHP built-in server document root at this directory.
 *
 *   php -S localhost:8000 -t backend/public        (dev)
 */

define('BASE_PATH', dirname(__DIR__));

// Bootstrap the autoloader and environment.
require BASE_PATH . '/core/Autoloader.php';
\App\Core\Autoloader::register();
\App\Core\Env::load(BASE_PATH . '/.env');

use App\Core\Request;
use App\Core\Router;
use App\Middleware\CorsMiddleware;
use App\Middleware\EnsureAjaxMiddleware;
use App\Middleware\SecurityHeadersMiddleware;

// Error handling: never expose raw errors to the client in production.
if (\App\Core\Env::get('APP_ENV', 'development') === 'production') {
    error_reporting(E_ALL);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
    ini_set('error_log', BASE_PATH . '/storage/logs/php-error.log');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
}

$router = new Router();

// Global middleware – applied to every request, in order.
$router->addGlobal(CorsMiddleware::class);
$router->addGlobal(SecurityHeadersMiddleware::class);
$router->addGlobal(EnsureAjaxMiddleware::class);

// Register the routes.
(require BASE_PATH . '/routes/api.php')($router);

$router->dispatch(new Request());
