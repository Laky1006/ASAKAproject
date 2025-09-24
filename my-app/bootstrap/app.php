<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

// Create & configure the Laravel application (Laravel 11 style)
return Application::configure(basePath: dirname(__DIR__))

    // ---------- ROUTING REGISTRATION ----------
    // Point Laravel to your route files. You included web + console; that's fine.
    // (Optional) If you have API routes, add: api: __DIR__.'/../routes/api.php',
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        // api: __DIR__.'/../routes/api.php',   // ← uncomment if you have routes/api.php
        commands: __DIR__.'/../routes/console.php',
        health: '/up', // /up endpoint used by health checks
    )

    // ---------- MIDDLEWARE CONFIG ----------
    ->withMiddleware(function (Middleware $middleware) {

        // Add/append web middleware that should always run on "web" routes.
        // You’re appending Inertia’s middleware and HTTP/2 preload link headers.
        // Note: The default web stack (session, CSRF, etc.) is already included by Laravel.
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,                // Inertia shared props, versioning, etc.
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class, // Sends Link: rel=preload headers for assets
        ]);

        // Define short aliases for route middleware.
        // This lets you use ->middleware('admin') in routes/web.php.
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            // Add more custom aliases here if needed:
            // 'moderator' => \App\Http\Middleware\ModeratorMiddleware::class,
        ]);
    })

    // ---------- EXCEPTION HANDLING ----------
    ->withExceptions(function (Exceptions $exceptions) {
        // Register custom exception reporting/handling here if needed.
        // e.g., $exceptions->report(function (Throwable $e) { ... });
        // or    $exceptions->render(function (Throwable $e, $request) { ... });
    })

    // Build the app instance
    ->create();
