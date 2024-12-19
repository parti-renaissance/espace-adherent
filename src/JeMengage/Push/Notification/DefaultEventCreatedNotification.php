<?php

namespace App\JeMengage\Push\Notification;

use App\Entity\Event\DefaultEvent;
use App\Firebase\Notification\AbstractMulticastNotification;

class DefaultEventCreatedNotification extends AbstractMulticastNotification
{
    public static function create(DefaultEvent $event): self
    {
        $assemblyZone = $event->getAssemblyZone();

        return new self(
            $assemblyZone ? \sprintf('%s, nouvel événement', $assemblyZone->getName()) : 'Nouvel événement',
            implode(' • ', array_filter([
                $event->getName(),
                self::formatDate($event->getBeginAt(), 'EEEE d MMMM y à HH\'h\'mm'),
                $event->getInlineFormattedAddress(),
            ])),
        );
    }
}
