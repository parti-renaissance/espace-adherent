<?php

namespace App\JeMarche\Notification;

use App\Entity\Event\BaseEvent;
use App\Firebase\Notification\AbstractMulticastNotification;

class EventReminderNotification extends AbstractMulticastNotification
{
    public static function create(array $tokens, BaseEvent $event): self
    {
        return new self(
            'Votre événement commence bientôt',
            sprintf('%s • %s • %s',
                $event->getName(),
                self::formatDate($event->getBeginAt(), 'EEEE d MMMM y à HH\'h\'mm'),
                $event->getInlineFormattedAddress()
            ),
            $tokens
        );
    }
}
