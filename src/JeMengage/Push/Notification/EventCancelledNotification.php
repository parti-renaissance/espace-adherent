<?php

declare(strict_types=1);

namespace App\JeMengage\Push\Notification;

use App\Entity\Event\Event;
use App\Firebase\Notification\AbstractMulticastNotification;
use App\JeMengage\Push\NotificationScope;

class EventCancelledNotification extends AbstractMulticastNotification
{
    public static function create(Event $event): self
    {
        return new self(
            static::createTitle($event),
            static::createBody($event),
            NotificationScope::event($event->getId()),
        );
    }

    private static function createTitle(Event $event): string
    {
        return \sprintf('[ANNULATION] %s', $event->getName());
    }

    private static function createBody(Event $event): string
    {
        return \sprintf(
            'L\'événement du %s auquel vous êtes inscrit vient d\'être annulé.',
            static::formatDate($event->getBeginAt(), 'EEEE d MMMM y à HH\'h\'mm'),
        );
    }
}
