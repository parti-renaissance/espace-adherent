<?php

declare(strict_types=1);

namespace App\ClickUp\Notifier;

use Symfony\Component\Notifier\Message\MessageOptionsInterface;

class ClickUpOptions implements MessageOptionsInterface
{
    public function __construct(private readonly string $channelId)
    {
    }

    public function toArray(): array
    {
        return [];
    }

    public function getRecipientId(): ?string
    {
        return $this->channelId;
    }
}
