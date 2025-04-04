<?php

namespace App\JeMengage\Push\Command;

use App\Entity\NationalEvent\NationalEvent;
use Ramsey\Uuid\UuidInterface;

class NationalEventTicketAvailableNotificationCommand extends AbstractSendNotificationCommand
{
    public function __construct(UuidInterface $uuid, public readonly string $destinationType)
    {
        parent::__construct($uuid);
    }

    public function getClass(): string
    {
        return NationalEvent::class;
    }
}
