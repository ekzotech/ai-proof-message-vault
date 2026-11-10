<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureDuskIsolation
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless(app()->environment('testing') && config('dusk.isolated'), 404);

        return $next($request);
    }
}
