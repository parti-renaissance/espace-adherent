<?php

declare(strict_types=1);

namespace App\JeMengage\Push\Notification;

use App\Entity\Event\Event;
use App\Firebase\Notification\AbstractMulticastNotification;
use App\JeMengage\Push\NotificationScope;
use App\Scope\ScopeEnum;

class EventCreatedNotification extends AbstractMulticastNotification
{
    public static function create(Event $event): self
    {
        return new self(
            self::buildTitle($event),
            implode(' • ', array_filter([
                $event->getName(),
                self::formatDate($event->getBeginAt(), 'EEEE d MMMM y à HH\'h\'mm'),
                $event->getInlineFormattedAddress(),
            ])),
            self::buildScope($event),
        );
    }

    private static function buildTitle(Event $event): string
    {
        if ($committee = $event->getCommittee()) {
            return \sprintf('Nouvel événement dans votre comité %s', $committee->getName());
        }

        if ($agora = $event->agora) {
            return \sprintf('Nouvel événement dans votre Agora %s', $agora->getName());
        }

        if ($event->isNational()) {
            return 'Nouvel événement';
        }

        if (ScopeEnum::MILITANT === $event->getAuthorScope() && !$event->getZones()->isEmpty()) {
            return \sprintf('%s, nouvel événement', $event->getZones()->first()->getName());
        }

        if (!$assemblyZone = $event->getAssemblyZone()) {
            throw new \RuntimeException(\sprintf('Event #%d has no assembly zone — cannot resolve notification title.', $event->getId()));
        }

        return \sprintf('%s, nouvel événement', $assemblyZone->getName());
    }

    private static function buildScope(Event $event): string
    {
        if ($event->isInvitation()) {
            return NotificationScope::event($event->getId());
        }

        if ($committee = $event->getCommittee()) {
            return NotificationScope::committee($committee->getId());
        }

        if ($event->isNational()) {
            return NotificationScope::national();
        }

        if (ScopeEnum::MILITANT === $event->getAuthorScope() && !$event->getZones()->isEmpty()) {
            return NotificationScope::zone($event->getZones()->first()->getCode());
        }

        if (!$assemblyZone = $event->getAssemblyZone()) {
            throw new \RuntimeException(\sprintf('Event #%d has no assembly zone — cannot resolve notification scope.', $event->getId()));
        }

        return NotificationScope::zone($assemblyZone->getCode());
    }
}
