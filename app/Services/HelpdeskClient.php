<?php

namespace App\Services;

use App\Exceptions\HelpdeskClientException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class HelpdeskClient
{
    public function __construct(
        protected ?string $token = null,
        protected ?string $baseUrl = null,
        protected ?int $timeout = null,
        protected ?string $deviceName = null,
        protected ?bool $rememberMe = null,
    ) {
        $this->baseUrl = $baseUrl ?? (string) config('services.helpdesk.base_url');
        $this->timeout = $timeout ?? (int) config('services.helpdesk.timeout', 15);
        $this->deviceName = $deviceName ?? (string) config('services.helpdesk.device_name', config('app.name'));
        $this->rememberMe = $rememberMe ?? (bool) config('services.helpdesk.remember_me', true);
    }

    public function setToken(?string $token): void
    {
        $this->token = $token;
    }

    /**
     * Authenticate an agent against the external API.
     */
    public function login(string $email, string $password): array
    {
        $response = $this->client()->post('auth/login', [
            'email' => $email,
            'password' => $password,
            'deviceName' => $this->deviceName,
            'rememberMe' => $this->rememberMe,
        ]);

        $this->throwIfFailed($response);

        $body = $response->json();

        $token = data_get($body, 'data.accessToken')
            ?? data_get($body, 'data.token')
            ?? data_get($body, 'data.access_token')
            ?? data_get($body, 'accessToken')
            ?? data_get($body, 'token')
            ?? data_get($body, 'access_token');

        $user = data_get($body, 'data.user')
            ?? data_get($body, 'user')
            ?? $body['data'] ?? null;

        return [
            'token' => $token,
            'user' => $user,
        ];
    }

    public function tickets(array $query = []): array
    {
        $response = $this->client()->get('tickets', $query);
        $this->throwIfFailed($response);

        return $response->json('data', []);
    }

    public function ticket(int|string $ticketId): array
    {
        $response = $this->client()->get("tickets/{$ticketId}");
        $this->throwIfFailed($response);

        return $response->json('data', []);
    }

    public function ticketResponses(int|string $ticketId): array
    {
        $response = $this->client()->get("tickets/{$ticketId}/responses");
        $this->throwIfFailed($response);

        return $response->json('data', []);
    }

    public function storeTicketResponse(int|string $ticketId, array $payload): array
    {
        $response = $this->client()->post("tickets/{$ticketId}/responses", $payload);
        $this->throwIfFailed($response);

        return $response->json('data', []);
    }

    protected function client(): PendingRequest
    {
        $client = Http::timeout($this->timeout)
            ->baseUrl($this->baseUrl)
            ->acceptJson();

        if ($this->token) {
            $client = $client->withToken($this->token);
        }

        return $client;
    }

    protected function throwIfFailed(Response $response): void
    {
        if ($response->failed()) {
            throw HelpdeskClientException::fromResponse($response);
        }
    }
}
