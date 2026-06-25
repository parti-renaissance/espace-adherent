<?php

declare(strict_types=1);

namespace App\Action\Command;

use App\Messenger\Message\AbstractUuidMessage;
use App\Messenger\Message\AsynchronousMessageInterface;
use Symfony\Component\Uid\Uuid;

class SendActionParticipantsNotificationCommand extends AbstractUuidMessage implements AsynchronousMessageInterface
{
    public function __construct(Uuid $uuid, private readonly bool $cancelled = false)
    {
        parent::__construct($uuid);
    }

    public function isCancelled(): bool
    {
        return $this->cancelled;
    }
}
