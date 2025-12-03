<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfExternalAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->session()->has('ext_token')) {
            return redirect()->route('tickets.index');
        }

        return $next($request);
    }
}
