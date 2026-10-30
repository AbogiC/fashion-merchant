<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;

/**
 * Simple, file-based sliding-window rate limiter.
 *
 * Usage on a route: "RateLimitMiddleware:login,5,60"
 *   action = login   (bucket key)
 *   max    = 5        (max requests)
 *   decay  = 60       (seconds window)
 *
 * Keeps the bucket per client IP and per action, stored in storage/ratelimit.
 * File-based so the limiter works even before the database is available.
 */
class RateLimitMiddleware implements Middleware
{
    private string $action;
    private int $max;
    private int $decay;

    public function __construct(array $params = [])
    {
        $this->action = $params[0] ?? 'global';
        $this->max = isset($params[1]) ? (int) $params[1] : 5;
        $this->decay = isset($params[2]) ? (int) $params[2] : 60;
    }

    public function handle(Request $request, callable $next): void
    {
        $dir = dirname(__DIR__, 2) . '/storage/ratelimit';
        if (!is_dir($dir)) {
            @mkdir($dir, 0700, true);
        }

        $key = md5($this->action . '|' . $request->ip());
        $file = $dir . '/' . $key . '.json';
        $now = time();

        $hits = [];
        if (is_file($file)) {
            $data = json_decode((string) file_get_contents($file), true);
            if (is_array($data)) {
                // Keep only timestamps that are still within the window.
                $hits = array_values(array_filter(
                    $data,
                    fn($t) => is_int($t) && $t > $now - $this->decay,
                ));
            }
        }

        if (count($hits) >= $this->max) {
            $retry = max(1, $this->decay - ($now - (int) $hits[0]));
            header('Retry-After: ' . $retry);
            Response::error('Too many requests. Please slow down and try again shortly.', 429);
        }

        $hits[] = $now;
        file_put_contents($file, json_encode($hits), LOCK_EX);

        $next($request);
    }
}
