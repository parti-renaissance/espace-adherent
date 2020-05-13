<?php

namespace App\Adherent\Unregistration\Handlers;

use App\CitizenProject\CitizenProjectManager;
use App\Entity\Adherent;

class UnfollowCitizenProjectHandler implements UnregistrationAdherentHandlerInterface
{
    private $manager;

    public function __construct(CitizenProjectManager $manager)
    {
        $this->manager = $manager;
    }

    public function supports(Adherent $adherent): bool
    {
        return !$adherent->getCitizenProjectMemberships()->isEmpty();
    }

    public function handle(Adherent $adherent): void
    {
        foreach ($adherent->getCitizenProjectMemberships() as $membership) {
            $this->manager->unfollowCitizenProject($adherent, $membership->getCitizenProject());
        }
    }
}
