<?php

namespace App\PushToken;

use App\Entity\Committee;
use App\Entity\Event\BaseEvent;
use App\Repository\CommitteeMembershipRepository;
use App\Repository\EventRegistrationRepository;

class PushTokenManager
{
    private $committeeMembershipRepository;
    private $eventRegistrationRepository;

    public function __construct(
        CommitteeMembershipRepository $committeeMembershipRepository,
        EventRegistrationRepository $eventRegistrationRepository,
    ) {
        $this->committeeMembershipRepository = $committeeMembershipRepository;
        $this->eventRegistrationRepository = $eventRegistrationRepository;
    }

    public function findIdentifiersForCommittee(Committee $committee): array
    {
        return $this->committeeMembershipRepository->findPushTokenIdentifiers($committee);
    }

    public function findIdentifiersForEvent(BaseEvent $event): array
    {
        return $this->eventRegistrationRepository->findPushTokenIdentifiers($event);
    }
}
