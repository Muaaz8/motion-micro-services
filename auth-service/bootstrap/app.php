<?php

/**
 * bootstrap/app.php — Laravel 11
 *
 * Replace the full contents of your bootstrap/app.php with this.
 * The key addition is the ->withMiddleware() block that registers
 * the custom JWT and internal-service middleware aliases.
 */

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Register custom middleware aliases
        $middleware->alias([
            'jwt.auth'         => \App\Http\Middleware\JwtMiddleware::class,
            'internal.service' => \App\Http\Middleware\InternalServiceMiddleware::class,
        ]);

        // Strip /api prefix so routes are at /api/auth/... as expected
        // (Laravel 11 does this automatically when you use ->withRouting(api:...))
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Return JSON for all API exceptions
        $exceptions->shouldRenderJsonWhen(function ($request) {
            return $request->is('api/*');
        });
    })
    ->create();
