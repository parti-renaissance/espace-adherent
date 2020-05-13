<?php

namespace App\Membership;

use App\Entity\Adherent;
use App\Repository\EventRegistrationRepository;
use App\Repository\EventRepository;

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
