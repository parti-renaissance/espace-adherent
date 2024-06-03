<?php

namespace App\JeMarche\Notification;

use App\Entity\Event\DefaultEvent;
use App\Entity\Geo\Zone;
use App\Firebase\Notification\AbstractTopicNotification;

class DefaultEventCreatedNotification extends AbstractTopicNotification
{
    public static function create(string $topic, DefaultEvent $event, Zone $zone): self
    {
        $notification = new self(
            sprintf('%s, nouvel événement', $zone->getName()),
            sprintf('%s • %s • %s',
                $event->getName(),
                self::formatDate($event->getBeginAt(), 'EEEE d MMMM y à HH\'h\'mm'),
                $event->getInlineFormattedAddress()
            ),
            $topic
        );

        $notification->setDeepLinkFromObject($event);

        return $notification;
    }
}
