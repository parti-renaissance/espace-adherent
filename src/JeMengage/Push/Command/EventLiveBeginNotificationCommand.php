<?php

namespace App\JeMengage\Push\Command;

use App\Entity\Event\Event;

class EventLiveBeginNotificationCommand extends AbstractSendNotificationCommand
{
    public function getClass(): string
    {
        return Event::class;
    }
}
