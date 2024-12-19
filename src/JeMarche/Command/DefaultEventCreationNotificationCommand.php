<?php

namespace App\JeMarche\Command;

use App\Entity\Event\DefaultEvent;

class DefaultEventCreationNotificationCommand extends AbstractSendNotificationCommand
{
    public function getClass(): string
    {
        return DefaultEvent::class;
    }
}
