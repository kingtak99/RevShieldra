<?php

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
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [App\Http\Middleware\SetLocale::class]);
        $middleware->api(prepend: [
            \Illuminate\Http\Middleware\HandleCors::class,
        ]);
        // Exclude webhook routes from CSRF verification
        $middleware->validateCsrfTokens(except: [
            'api/lemonsqueezy/webhook',
            'webhooks/lemon-squeezy',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
