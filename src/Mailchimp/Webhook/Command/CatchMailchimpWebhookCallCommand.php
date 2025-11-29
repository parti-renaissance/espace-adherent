<?php

declare(strict_types=1);

namespace App\Mailchimp\Webhook\Command;

use App\Messenger\Message\AsynchronousMessageInterface;
use Symfony\Component\Serializer\Attribute\Groups;

class CatchMailchimpWebhookCallCommand implements AsynchronousMessageInterface
{
    private array $payload;

    public function __construct(array $payload)
    {
        $this->payload = $payload;
    }

    #[Groups(['command_read'])]
    public function getPayload(): array
    {
        return $this->payload;
    }

    public function getType(): ?string
    {
        return $this->payload['type'] ?? null;
    }

    public function getListId(): ?string
    {
        return $this->getData()['list_id'] ?? null;
    }

    public function getData(): array
    {
        return $this->payload['data'] ?? [];
    }
}
