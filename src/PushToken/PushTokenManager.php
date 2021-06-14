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

    public function findIdentifiersForEventCreation(CommitteeEvent $event): array
    {
        return $this->committeeMembershipRepository->findPushTokenIdentifiers($event->getCommittee());
    }
}
