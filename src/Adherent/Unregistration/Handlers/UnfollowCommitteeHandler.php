<?php

namespace App\Adherent\Unregistration\Handlers;

use App\Committee\CommitteeManager;
use App\Entity\Adherent;

class UnfollowCommitteeHandler implements UnregistrationAdherentHandlerInterface
{
    private $manager;

    public function __construct(CommitteeManager $manager)
    {
        $this->manager = $manager;
    }

    public function supports(Adherent $adherent): bool
    {
        return !$adherent->getMemberships()->isEmpty();
    }

    public function handle(Adherent $adherent): void
    {
        foreach ($adherent->getMemberships() as $membership) {
            $this->manager->unfollowCommittee($adherent, $membership->getCommittee());
        }
    }
}
