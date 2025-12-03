<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureExternalAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->session()->has('ext_token')) {
            if ($request->expectsJson()) {
                abort(Response::HTTP_UNAUTHORIZED, 'La sesión ha expirado. Inicia sesión nuevamente.');
            }

            return redirect()->route('login');
        }

        return $next($request);
    }
}
