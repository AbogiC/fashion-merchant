<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Request;

/**
 * Every middleware implements this contract.
 *
 * A middleware performs work BEFORE the request is handled, then either:
 *   - calls $next($request) to let the chain continue, or
 *   - sends its own response and returns (short-circuit) e.g. on a failed auth check.
 *
 * Middleware that needs configuration receives its parameters in the constructor.
 * The router parses a "Class:param1,param2" route declaration into that array.
 */
interface Middleware
{
    public function __construct(array $params = []);

    public function handle(Request $request, callable $next): void;
}
