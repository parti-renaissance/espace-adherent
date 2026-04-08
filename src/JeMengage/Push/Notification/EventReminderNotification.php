<?php

declare(strict_types=1);

namespace App\JeMengage\Push\Notification;

use App\Entity\Event\Event;
use App\Firebase\Notification\AbstractMulticastNotification;
use App\JeMengage\Push\NotificationScope;

class EventReminderNotification extends AbstractMulticastNotification
{
    public static function create(Event $event): self
    {
        return new self(
            'Votre événement commence bientôt',
            implode(' • ', array_filter([
                $event->getName(),
                self::formatDate($event->getBeginAt(), 'EEEE d MMMM y à HH\'h\'mm'),
                $event->getInlineFormattedAddress(),
            ])),
            NotificationScope::event($event->getId()),
        );
    }
}
