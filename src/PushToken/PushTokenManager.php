<?php

namespace App\PushToken;

use App\Committee\CommitteeEvent;
use App\Entity\Event\BaseEvent;
use App\Entity\Event\DefaultEvent;
use App\Entity\Geo\Zone;
use App\Repository\CommitteeMembershipRepository;
use App\Repository\PushTokenRepository;

class PushTokenManager
{
    private $pushTokenRepository;
    private $committeeMembershipRepository;

    public function __construct(
        PushTokenRepository $pushTokenRepository,
        CommitteeMembershipRepository $committeeMembershipRepository
    ) {
        $this->pushTokenRepository = $pushTokenRepository;
        $this->committeeMembershipRepository = $committeeMembershipRepository;
    }

    public function findIdentifiersForEventCreation(BaseEvent $event): array
    {
        if ($event instanceof CommitteeEvent) {
            return $this->committeeMembershipRepository->findPushTokenIdentifiers($event->getCommittee());
        }

        if ($event instanceof DefaultEvent) {
            if (!$zone = $this->findZoneToNotifyForEventCreation($event)) {
                return [];
            }

            return $this->pushTokenRepository->findIdentifiersForZones([$zone]);
        }

        throw new \InvalidArgumentException(sprintf('Event type "%s" is not handled for notifications', $event->getType()));
    }

    private function findZoneToNotifyForEventCreation(DefaultEvent $event): ?Zone
    {
        $boroughs = $event->getZonesOfType(Zone::BOROUGH);
        if (!empty($boroughs)) {
            return $boroughs[0];
        }

        $departments = $event->getParentZonesOfType(Zone::DEPARTMENT);
        if (!empty($departments)) {
            return $departments[0];
        }

        return null;
    }
}
