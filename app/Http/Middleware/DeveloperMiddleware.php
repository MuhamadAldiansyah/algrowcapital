<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class DeveloperMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && Auth::user()->role === 'developer') {
            return $next($request);
        }

        abort(403, 'Akses ditolak. Hanya Developer yang dapat mengelola data tenant.');
    }
}
