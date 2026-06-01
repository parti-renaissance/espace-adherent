<?php

declare(strict_types=1);

namespace App\SocialNetwork\Webhook\Command;

use App\Messenger\Message\AsynchronousMessageInterface;

class SocialNetworkFeedWebhookCommand implements AsynchronousMessageInterface
{
    public function __construct(private array $payload)
    {
    }

    public function getPayload(): array
    {
        return $this->payload;
    }
}
