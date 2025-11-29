<?php

declare(strict_types=1);

namespace App\JeMengage\Push\Notification;

use App\Entity\Event\Event;
use App\Firebase\Notification\AbstractMulticastNotification;

class EventCreatedNotification extends AbstractMulticastNotification
{
    public static function create(Event $event): self
    {
        if ($event->getCommittee()) {
            $titre = \sprintf('Nouvel événement dans votre comité %s', $event->getCommittee()->getName());
        } else {
            $assemblyZone = $event->getAssemblyZone();
            $titre = $assemblyZone ? \sprintf('%s, nouvel événement', $assemblyZone->getName()) : 'Nouvel événement';
        }

        return new self(
            $titre,
            implode(' • ', array_filter([
                $event->getName(),
                self::formatDate($event->getBeginAt(), 'EEEE d MMMM y à HH\'h\'mm'),
                $event->getInlineFormattedAddress(),
            ])),
        );
    }
}
