<?php

declare(strict_types=1);

namespace App\JeMengage\Push\Command;

use App\Entity\NationalEvent\NationalEvent;
use Symfony\Component\Uid\Uuid;

class NationalEventTicketAvailableNotificationCommand extends AbstractSendNotificationCommand
{
    public function __construct(Uuid $uuid, public readonly string $destinationType)
    {
        parent::__construct($uuid);
    }

    public function getClass(): string
    {
        return NationalEvent::class;
    }
}
