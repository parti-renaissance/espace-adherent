<?php

namespace App\NationalEvent\Command;

use App\Messenger\Message\UuidDefaultAsyncMessage;
use App\NationalEvent\WebhookActionEnum;
use Ramsey\Uuid\UuidInterface;

class SendWebhookCommand extends UuidDefaultAsyncMessage
{
    public function __construct(
        UuidInterface $uuid,
        public readonly WebhookActionEnum $action,
    ) {
        parent::__construct($uuid);
    }

    public function isPostCreate(): bool
    {
        return WebhookActionEnum::POST_CREATE === $this->action;
    }

    public function isPostUpdate(): bool
    {
        return WebhookActionEnum::POST_UPDATE === $this->action;
    }
}
