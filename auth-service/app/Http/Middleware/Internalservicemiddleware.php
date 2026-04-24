<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * InternalServiceMiddleware
 *
 * Guards endpoints that should only be callable by trusted microservices,
 * not by end users. Calling services must include the shared secret
 * in the X-Service-Secret header.
 *
 * Set INTERNAL_SERVICE_SECRET in .env — use a long random string,
 * e.g. `php artisan key:generate --show` and use that value.
 */
class InternalServiceMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $incomingSecret = $request->header('X-Service-Secret');
        $expectedSecret = config('app.internal_secret');

        if (! $incomingSecret || ! hash_equals((string) $expectedSecret, (string) $incomingSecret)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        return $next($request);
    }
}
