<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureExternalAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->hasValidSession($request) || $this->attachFromHeader($request)) {
            return $next($request);
        }

        abort(Response::HTTP_UNAUTHORIZED, 'Falta el encabezado X-External-Agent con los datos del agente.');
    }

    protected function hasValidSession(Request $request): bool
    {
        return $request->hasSession() && $request->session()->has('ext_user');
    }

    protected function attachFromHeader(Request $request): bool
    {
        $raw = $request->header('X-External-Agent');

        if (! $raw) {
            return false;
        }

        $decoded = json_decode($raw, true);

        if (! is_array($decoded) || empty($decoded['id']) || empty($decoded['email'])) {
            abort(Response::HTTP_UNAUTHORIZED, 'Formato inválido para X-External-Agent.');
        }

        $request->attributes->set('ext_user', [
            'id' => (int) $decoded['id'],
            'name' => $decoded['name'] ?? $decoded['email'],
            'email' => $decoded['email'],
            'roles' => $decoded['roles'] ?? [],
        ]);

        if (! empty($decoded['token'])) {
            $request->attributes->set('ext_token', $decoded['token']);
        }

        return true;
    }
}
