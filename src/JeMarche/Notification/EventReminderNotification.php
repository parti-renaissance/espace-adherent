<?php

namespace App\JeMarche\Notification;

use App\Entity\Event\BaseEvent;
use App\Firebase\Notification\AbstractMulticastNotification;

class EventReminderNotification extends AbstractMulticastNotification
{
    public static function create(array $tokens, BaseEvent $event): self
    {
        $body = sprintf('%s • %s',
            $event->getName(),
            self::formatDate($event->getBeginAt(), 'EEEE d MMMM y à HH\'h\'mm')
        );

        if (!empty($event->getAddress())) {
            $body .= sprintf(' • %s', $event->getInlineFormattedAddress());
        }

        $notification = new self(
            'Votre événement commence bientôt',
            $body,
            $tokens
        );

        $notification->setDeepLinkFromObject($event);

        return $notification;
    }
}
