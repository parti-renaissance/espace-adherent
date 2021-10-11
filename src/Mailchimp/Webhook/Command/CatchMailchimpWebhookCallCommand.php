<?php

namespace App\Mailchimp\Webhook\Command;

use App\Messenger\Message\AsynchronousMessageInterface;

class CatchMailchimpWebhookCallCommand implements AsynchronousMessageInterface
{
    private array $payload;

    public function __construct(array $data)
    {
        $this->payload = $data;
    }

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
        if (!empty($this->getData()['list_id'])) {
            return $this->getData()['list_id'];
        }

        return null;
    }

    public function getData(): array
    {
        return $this->payload['data'] ?? [];
    }
}
