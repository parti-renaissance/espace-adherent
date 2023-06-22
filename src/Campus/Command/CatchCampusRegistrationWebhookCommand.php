<?php

namespace App\Campus\Command;

use App\Messenger\Message\AsynchronousMessageInterface;

class CatchCampusRegistrationWebhookCommand implements AsynchronousMessageInterface
{
    private array $payload;

    public function __construct(array $payload)
    {
        $this->payload = $payload;
    }

    public function getPayload(): array
    {
        return $this->payload;
    }
}
