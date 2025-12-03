<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Client\Response;

class HelpdeskClientException extends Exception
{
    public static function fromResponse(Response $response): self
    {
        $message = $response->json('message') ?? 'La API externa rechazó la solicitud.';

        return new self($message, $response->status());
    }
}
