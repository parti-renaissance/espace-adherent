<?php

namespace App\JeMarche\Notification;

use App\Entity\Event\DefaultEvent;
use App\Firebase\Notification\AbstractMulticastNotification;

class DefaultEventCreatedNotification extends AbstractMulticastNotification
{
    public static function create(DefaultEvent $event): self
    {
        $notification = new self(
            '%s, nouvel événement',
            \sprintf('%s • %s • %s',
                $event->getName(),
                self::formatDate($event->getBeginAt(), 'EEEE d MMMM y à HH\'h\'mm'),
                $event->getInlineFormattedAddress()
            ),
        );

        $notification->setDeepLinkFromObject($event);

        return $notification;
    }
}
