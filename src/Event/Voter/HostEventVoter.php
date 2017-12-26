<?php

namespace AppBundle\Event\Voter;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\Event;
use AppBundle\Event\EventPermissions;
use AppBundle\Repository\CommitteeMembershipRepository;

class HostEventVoter extends AbstractEventVoter
{
    private $repository;

    public function __construct(CommitteeMembershipRepository $repository)
    {
        $this->repository = $repository;
    }

    protected function supports($attribute, $event)
    {
        return EventPermissions::HOST === $attribute && $event instanceof Event;
    }

    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, Event $event): bool
    {
        if ($event->getOrganizer() && $adherent->equals($event->getOrganizer())) {
            return true;
        }

        if (!$committee = $event->getCommittee()) {
            return false;
        }

        // Optimization to prevent a SQL query if the current adherent already
        // has a loaded list of related committee memberships entities.
        if ($membership = $adherent->getMembershipFor($committee)) {
            return $membership->canHostCommittee();
        }

        return $this->repository->hostCommittee($adherent, $committee->getUuid());
    }
}
