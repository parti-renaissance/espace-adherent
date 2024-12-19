<?php

namespace App\JeMengage\Push\Notification;

use App\Entity\Event\BaseEvent;
use App\Firebase\Notification\AbstractMulticastNotification;

class EventReminderNotification extends AbstractMulticastNotification
{
    public static function create(BaseEvent $event): self
    {
        return new self(
            'Votre événement commence bientôt',
            implode(' • ', array_filter([
                $event->getName(),
                self::formatDate($event->getBeginAt(), 'EEEE d MMMM y à HH\'h\'mm'),
                $event->getInlineFormattedAddress(),
            ])),
        );
    }
}
