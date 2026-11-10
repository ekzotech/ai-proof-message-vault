<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CapsuleResponseHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($request->is('c/*', 'capsules/*', 'dashboard', 'profile', 'livewire*/update')) {
            $response->headers->set('Cache-Control', 'private, no-store');
        }

        if ($request->is('c/*')) {
            $directives = 'noindex, nofollow, noarchive';
            if (config('capsules.request_no_ai')) {
                $directives .= ', noai, noimageai';
            }
            $response->headers->set('X-Robots-Tag', $directives);
        }

        return $response;
    }
}
