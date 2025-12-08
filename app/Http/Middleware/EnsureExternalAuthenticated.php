<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureExternalAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        if (
            $this->hasValidSession($request)
            || $this->attachFromTrustedHeaders($request)
            || $this->attachFromBearerToken($request)
        ) {
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

    protected function attachFromTrustedHeaders(Request $request): bool
    {
        $payload = $this->extractTrustedPayload($request);

        if (! $payload) {
            return false;
        }

        $request->attributes->set('ext_user', [
            'id' => (int) $payload['id'],
            'name' => $payload['name'] ?? $payload['email'],
            'email' => $payload['email'],
            'roles' => $payload['roles'] ?? [],
        ]);

        if (! empty($payload['token'])) {
            $request->attributes->set('ext_token', $payload['token']);
        }

        return true;
    }

    protected function extractTrustedPayload(Request $request): ?array
    {
        $raw = $request->header('X-External-Agent');

        if ($raw) {
            $decoded = json_decode($raw, true);

            if (
                is_array($decoded)
                && isset($decoded['id'])
                && isset($decoded['email'])
            ) {
                return [
                    'id' => $decoded['id'],
                    'email' => $decoded['email'],
                    'name' => $decoded['name'] ?? $decoded['email'],
                    'roles' => $decoded['roles'] ?? [],
                    'token' => $decoded['token'] ?? null,
                ];
            }
        }

        $id = $request->header('X-External-Agent-Id') ?? $request->query('agent_id');
        $email = $request->header('X-External-Agent-Email') ?? $request->query('agent_email');

        if (! $id || ! $email) {
            return null;
        }

        return [
            'id' => $id,
            'email' => $email,
            'name' => $request->header('X-External-Agent-Name') ?? $request->query('agent_name') ?? $email,
            'roles' => $this->parseRoles(
                $request->header('X-External-Agent-Roles') ?? $request->query('agent_roles')
            ),
            'token' => $request->header('X-External-Agent-Token') ?? $request->query('agent_token'),
        ];
    }

    protected function parseRoles(null|string|array $roles): array
    {
        if (is_array($roles)) {
            return $roles;
        }

        if (! $roles) {
            return [];
        }

        $decoded = json_decode($roles, true);

        if (is_array($decoded)) {
            return $decoded;
        }

        return array_values(array_filter(array_map('trim', explode(',', $roles))));
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
