<?php

use App\Http\Middleware\CheckActiveAccount;
use App\Http\Middleware\CheckRole;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Append the inactive-account check to every authenticated web request.
        // Placed in the 'web' group so it runs after session/auth are booted.
        $middleware->appendToGroup('web', CheckActiveAccount::class);

        // Named alias used in route definitions:
        //   check.role:admin
        //   check.role:moderator,admin
        //   check.role:author,moderator,admin
        $middleware->alias([
            'check.role' => CheckRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
