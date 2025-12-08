<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureExternalAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->hasValidSession($request) || $this->attachFromBearerToken($request)) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            abort(Response::HTTP_UNAUTHORIZED, 'La sesión ha expirado. Inicia sesión nuevamente.');
        }

        return redirect()->route('login');
    }

    protected function hasValidSession(Request $request): bool
    {
        return $request->hasSession() && $request->session()->has('ext_token');
    }

    protected function attachFromBearerToken(Request $request): bool
    {
        $payload = $this->decodeBearerPayload($request->bearerToken());

        if (! $payload) {
            return false;
        }

        $request->attributes->set('ext_token', $payload['token']);
        $request->attributes->set('ext_user', $payload['user']);

        return true;
    }

    protected function decodeBearerPayload(?string $token): ?array
    {
        if (! $token) {
            return null;
        }

        $segments = explode('.', $token);

        if (count($segments) < 2) {
            return null;
        }

        $payload = $this->decodeJwtSegment($segments[1]);

        if (! is_array($payload)) {
            return null;
        }

        $userId = data_get($payload, 'user_id') ?? data_get($payload, 'sub');
        $email = data_get($payload, 'email');

        if (! $userId || ! $email) {
            return null;
        }

        return [
            'token' => $token,
            'user' => [
                'id' => $userId,
                'name' => data_get($payload, 'displayName') ?? data_get($payload, 'name') ?? $email,
                'email' => $email,
                'roles' => data_get($payload, 'roles', []),
            ],
        ];
    }

    protected function decodeJwtSegment(string $segment): mixed
    {
        $segment = str_replace(['-', '_'], ['+', '/'], $segment);
        $padding = strlen($segment) % 4;

        if ($padding !== 0) {
            $segment .= str_repeat('=', 4 - $padding);
        }

        $decoded = base64_decode($segment, true);

        if ($decoded === false) {
            return null;
        }

        return json_decode($decoded, true);
    }
}
