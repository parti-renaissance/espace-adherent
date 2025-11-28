<?php

declare(strict_types=1);

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
        return null !== $adherent->getCommitteeMembership();
    }

    public function handle(Adherent $adherent): void
    {
        $this->manager->unfollowCommittee($adherent->getCommitteeMembership());
    }
}
