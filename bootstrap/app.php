<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias(['role' => \App\Http\Middleware\EnsureRole::class]);
        // Railway (and most hosts) sit behind a proxy that terminates HTTPS
        $middleware->trustProxies(at: '*');
        // Staff go to the dashboard after login, guests to the login page
        $middleware->redirectGuestsTo('/login');
        $middleware->redirectUsersTo(fn ($request) => $request->user()?->isStaff() ? '/dashboard' : '/');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
