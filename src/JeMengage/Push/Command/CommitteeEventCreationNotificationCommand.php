<?php

namespace App\JeMengage\Push\Command;

use App\Entity\Event\CommitteeEvent;
use App\Entity\NotificationObjectInterface;

class CommitteeEventCreationNotificationCommand extends AbstractSendNotificationCommand
{
    public function getClass(): string
    {
        return CommitteeEvent::class;
    }

    public function updateFromObject(NotificationObjectInterface $object): void
    {
    }
}
