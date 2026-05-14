<?php

declare(strict_types=1);

namespace App\Adherent\Notification;

use App\Messenger\Message\UuidDefaultAsyncMessage;
use Symfony\Component\Uid\Uuid;

class NewMembershipNotificationCommand extends UuidDefaultAsyncMessage
{
    public function __construct(
        Uuid $uuid,
        private readonly \DateTimeInterface $from,
        private readonly \DateTimeInterface $to,
    ) {
        parent::__construct($uuid);
    }

    public function getFrom(): \DateTimeInterface
    {
        return $this->from;
    }

    public function getTo(): \DateTimeInterface
    {
        return $this->to;
    }
}
