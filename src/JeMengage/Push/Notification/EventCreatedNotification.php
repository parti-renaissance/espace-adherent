<?php

declare(strict_types=1);

namespace App\JeMengage\Push\Notification;

use App\Entity\Event\Event;
use App\Firebase\Notification\AbstractMulticastNotification;
use App\JeMengage\Push\NotificationScope;

class EventCreatedNotification extends AbstractMulticastNotification
{
    public static function create(Event $event): self
    {
        if ($event->getCommittee()) {
            $titre = \sprintf('Nouvel événement dans votre comité %s', $event->getCommittee()->getName());
            $scope = NotificationScope::committee($event->getCommittee()->getId());
        } elseif ($event->isNational()) {
            $titre = 'Nouvel événement';
            $scope = NotificationScope::national();
        } else {
            $assemblyZone = $event->getAssemblyZone();

            if (!$assemblyZone) {
                throw new \RuntimeException(\sprintf('Event #%d has no assembly zone — cannot resolve notification scope.', $event->getId()));
            }

            $titre = \sprintf('%s, nouvel événement', $assemblyZone->getName());
            $scope = NotificationScope::zone($assemblyZone->getCode());
        }

        return new self(
            $titre,
            implode(' • ', array_filter([
                $event->getName(),
                self::formatDate($event->getBeginAt(), 'EEEE d MMMM y à HH\'h\'mm'),
                $event->getInlineFormattedAddress(),
            ])),
            $scope,
        );
    }
}
