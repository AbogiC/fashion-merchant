<?php

declare(strict_types=1);

namespace App\Core;

use App\Middleware\Middleware;

/**
 * A tiny dependency-free router.
 *
 * Supports GET/POST/PUT/DELETE, `{param}` path placeholders, per-route
 * middleware and a set of global middleware that runs for every request.
 *
 * Middleware declarations are strings in the form "Class" or "Class:arg1,arg2".
 */
class Router
{
    private array $routes = [];
    private array $globalMiddleware = [];

    public function addGlobal(string $middleware): self
    {
        $this->globalMiddleware[] = $middleware;
        return $this;
    }

    public function get(string $path, array $handler, array $middleware = []): self
    {
        return $this->add('GET', $path, $handler, $middleware);
    }

    public function post(string $path, array $handler, array $middleware = []): self
    {
        return $this->add('POST', $path, $handler, $middleware);
    }

    public function put(string $path, array $handler, array $middleware = []): self
    {
        return $this->add('PUT', $path, $handler, $middleware);
    }

    public function delete(string $path, array $handler, array $middleware = []): self
    {
        return $this->add('DELETE', $path, $handler, $middleware);
    }

    private function add(string $method, string $path, array $handler, array $middleware): self
    {
        $this->routes[] = [
            'method'     => $method,
            'path'       => '/' . trim($path, '/'),
            'handler'    => $handler,
            'middleware' => $middleware,
        ];
        return $this;
    }

    /** Match the incoming request and run the middleware pipeline. */
    public function dispatch(Request $request): void
    {
        $method = $request->method();
        $path = $this->normalize($request->path());

        $pathMatched = false;

        foreach ($this->routes as $route) {
            if (!preg_match($this->compile($route['path']), $path, $matches)) {
                continue;
            }

            if ($route['method'] !== $method) {
                $pathMatched = true;
                continue;
            }

            // Keep only named capture groups as route params.
            $params = array_filter($matches, fn($k) => !is_int($k), ARRAY_FILTER_USE_KEY);
            $request->setParams($params);

            $this->run($route, $request);
            return;
        }

        Response::error(
            $pathMatched ? 'Method not allowed' : 'Not found',
            $pathMatched ? 405 : 404,
        );
    }

    private function normalize(string $path): string
    {
        $prefix = rtrim(Env::get('API_PREFIX', '/api'), '/');
        if ($prefix !== '' && str_starts_with($path, $prefix)) {
            $path = substr($path, strlen($prefix));
        }
        return '/' . trim($path, '/');
    }

    /** Convert {param} placeholders into named regex capture groups. */
    private function compile(string $path): string
    {
        $pattern = preg_replace('/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/', '(?P<$1>[^/]+)', $path);
        return '#^' . $pattern . '$#';
    }

    /** Build and execute the middleware -> handler pipeline. */
    private function run(array $route, Request $request): void
    {
        $stack = array_merge($this->globalMiddleware, $route['middleware']);

        $handler = function (Request $req) use ($route): void {
            [$class, $method] = $route['handler'];
            $controller = new $class();
            $controller->$method($req);
        };

        // Fold the middleware around the handler: first item runs first.
        $pipeline = array_reduce(
            array_reverse($stack),
            function (callable $next, string $spec) {
                return function (Request $req) use ($spec, $next): void {
                    $this->resolveMiddleware($spec)->handle($req, $next);
                };
            },
            $handler,
        );

        $pipeline($request);
    }

    private function resolveMiddleware(string $spec): Middleware
    {
        $parts = explode(':', $spec, 2);
        $class = $parts[0];
        $params = [];
        if (isset($parts[1]) && $parts[1] !== '') {
            $params = array_map('trim', explode(',', $parts[1]));
        }
        return new $class($params);
    }
}
