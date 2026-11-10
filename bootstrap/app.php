<?php

use App\Http\Middleware\CapsuleResponseHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(CapsuleResponseHeaders::class);
        $middleware->trimStrings(except: ['components.*.updates.body']);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->dontFlash(['token', 'body']);
        $exceptions->report(function (Throwable $exception): ?bool {
            if (! app()->bound('request') || ! request()->is('c/*', 'capsules/*', 'livewire*/update')) {
                return null;
            }

            Log::error('Capsule request failed.', [
                'route' => request()->route()?->getName(),
                'exception' => $exception::class,
            ]);

            return false;
        });

        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
