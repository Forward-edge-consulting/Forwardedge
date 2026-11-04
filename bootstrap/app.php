<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Log;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Allow frontend forms to read the CSRF token cookie.
        $middleware->encryptCookies(except: ['XSRF-TOKEN']);

        // Safe environment check at bootstrap time (no container usage)
        $appEnv = $_ENV['APP_ENV'] ?? getenv('APP_ENV') ?: 'production';

        // Only enable the logger in local (or comment this if you want it always)
        if ($appEnv === 'local') {
            // Put CSRF logger BEFORE VerifyCsrfToken in the 'web' stack
            $middleware->prependToGroup('web', \App\Http\Middleware\LogCsrfFailures::class);
        }

        $middleware->appendToGroup('web', \App\Http\Middleware\ApplySeoDefaults::class);

        // Existing aliases
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
        ]);

        $middleware->redirectGuestsTo(fn () => route('admin.login'));
        $middleware->redirectUsersTo('/');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Gracefully handle expired/invalid CSRF tokens so users see a clear message instead of a blank/419 page.
        $exceptions->render(function (TokenMismatchException $e, $request) {
            Log::warning('CSRF token mismatch', [
                'url' => $request->fullUrl(),
                'route' => optional($request->route())->getName(),
                'session_exists' => $request->hasSession() && $request->session()->isStarted(),
                'user_id' => optional($request->user())->id,
            ]);

            return back()
                ->withInput()
                ->with('error', 'Your session expired—please retry the form.');
        });
    })
    ->create();
