<?php

namespace App\Mailer\Command;

use App\Messenger\Message\AbstractUuidMessage;
use Ramsey\Uuid\UuidInterface;

abstract class AbstractSendMessageCommand extends AbstractUuidMessage implements SendMessageCommandInterface
{
    public bool $resend;

    public function __construct(UuidInterface $uuid, bool $resend = false)
    {
        parent::__construct($uuid);

        $this->resend = $resend;
    }
}
