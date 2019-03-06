<?php

namespace AppBundle\Membership;

use AppBundle\Entity\Adherent;
use AppBundle\Repository\EventRegistrationRepository;
use AppBundle\Repository\EventRepository;

class MemberActivityTracker
{
    private $eventRegistrationRepository;
    private $eventRepository;

    public function __construct(
        EventRegistrationRepository $eventRegistrationRepository,
        EventRepository $eventRepository
    ) {
        $this->eventRegistrationRepository = $eventRegistrationRepository;
        $this->eventRepository = $eventRepository;
    }

    public function getRecentActivitiesForAdherent(Adherent $adherent): MemberActivityCollection
    {
        return new MemberActivityCollection(
            $adherent,
            $this->eventRegistrationRepository->findPastAdherentRegistrations($adherent->getUuid()->toString()),
            $this->eventRepository->findEventsByOrganizer($adherent)
        );
    }
}
