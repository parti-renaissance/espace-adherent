<?php

namespace App\JeMarche\Command;

use App\Entity\Event\BaseEvent;

class EventReminderNotificationCommand extends AbstractSendNotificationCommand
{
    public function getClass(): string
    {
        return BaseEvent::class;
    }
}
