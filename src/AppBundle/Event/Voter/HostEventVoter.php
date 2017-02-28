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
        if ($event->getOrganizer() && $adherent->getId() === $event->getOrganizer()->getId()) {
            return true;
        }

        if (!$event->getCommittee()) {
            return false;
        }

        return $this->repository->hostCommittee($adherent, (string) $event->getCommittee()->getUuid());
    }
}
