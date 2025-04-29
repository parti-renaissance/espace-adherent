<?php

namespace App\Mailer\Command;

use App\Messenger\Message\AbstractUuidMessage;
use Ramsey\Uuid\UuidInterface;

abstract class AbstractSendMessageCommand extends AbstractUuidMessage implements SendMessageCommandInterface
{
    public function __construct(UuidInterface $uuid, private readonly bool $resend = false)
    {
        parent::__construct($uuid);
    }

    public function isResend(): bool
    {
        return $this->resend;
    }
}
