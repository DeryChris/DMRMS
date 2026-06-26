<?php

use Illuminate\Auth\Middleware\RedirectIfAuthenticated;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'subscription' => \App\Http\Middleware\SubscriptionMiddleware::class,
            'ai.rate.limit' => \App\Http\Middleware\AiRateLimitMiddleware::class,
            'applicant.access' => \App\Http\Middleware\CheckApplicantAccess::class,
        ]);

        RedirectIfAuthenticated::redirectUsing(function (Request $request) {
            $user = $request->user('applicant') ?? $request->user();

            if ($user && method_exists($user, 'isAdmin') && $user->isAdmin()) {
                return route('admin.dashboard');
            }

            return route('applicant.dashboard');
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
