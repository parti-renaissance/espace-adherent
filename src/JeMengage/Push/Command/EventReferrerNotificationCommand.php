<?php

namespace App\JeMengage\Push\Command;

use App\Entity\Event\EventRegistration;

class EventReferrerNotificationCommand extends AbstractSendNotificationCommand
{
    public function getClass(): string
    {
        return EventRegistration::class;
    }
}
