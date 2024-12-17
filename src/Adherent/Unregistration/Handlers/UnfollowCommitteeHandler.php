<?php

namespace App\Adherent\Unregistration\Handlers;

use App\Committee\CommitteeMembershipManager;
use App\Entity\Adherent;

class UnfollowCommitteeHandler implements UnregistrationAdherentHandlerInterface
{
    public function __construct(private readonly CommitteeMembershipManager $manager)
    {
    }

    public function supports(Adherent $adherent): bool
    {
        return !$adherent->getCommitteeMembership();
    }

    public function handle(Adherent $adherent): void
    {
        $this->manager->unfollowCommittee($adherent->getCommitteeMembership());
    }
}
