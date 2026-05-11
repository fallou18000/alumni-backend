<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Broadcast;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Broadcast::routes([
                'middleware' => ['auth:sanctum'],
            ]);
        }
    )
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->withMiddleware(function (Middleware $middleware): void {

        // ✅ CORS obligatoire
        $middleware->append(\Illuminate\Http\Middleware\HandleCors::class);

        // ❌ IMPORTANT : NE PAS ACTIVER Sanctum SPA middleware
        // ❌ NE PAS METTRE EnsureFrontendRequestsAreStateful
    })
 ->withMiddleware(function ($middleware) {
    $middleware->append(\App\Http\Middleware\UpdateLastSeen::class);
})
    ->create();