<?php

namespace AppBundle\Adherent\Unregistration\Handlers;

use AppBundle\CitizenProject\CitizenProjectManager;
use AppBundle\Entity\Adherent;

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
