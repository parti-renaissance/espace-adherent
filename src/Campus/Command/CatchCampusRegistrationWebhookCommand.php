<?php

namespace App\Campus\Command;

use App\Messenger\Message\AsynchronousMessageInterface;

class CatchCampusRegistrationWebhookCommand implements AsynchronousMessageInterface
{
    private string $payload;

    public function __construct(string $payload)
    {
        $this->payload = $payload;
    }

    public function getPayload(): string
    {
        return $this->payload;
    }
}
