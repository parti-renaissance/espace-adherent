<?php

namespace AppBundle\Membership;

use AppBundle\Entity\Adherent;
use AppBundle\Repository\EventRegistrationRepository;

class MemberActivityTracker
{
    private $eventRegistrationRepository;

    public function __construct(EventRegistrationRepository $eventRegistrationRepository)
    {
        $this->eventRegistrationRepository = $eventRegistrationRepository;
    }

    public function getRecentActivitiesForAdherent(Adherent $adherent): MemberActivityCollection
    {
        return new MemberActivityCollection(
            $adherent,
            $this->eventRegistrationRepository->findPastAdherentRegistrations($adherent->getUuid()->toString())
        );
    }
}
