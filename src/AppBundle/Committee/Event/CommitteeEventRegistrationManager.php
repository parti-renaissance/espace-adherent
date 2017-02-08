<?php

namespace AppBundle\Committee\Event;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\CommitteeEvent;
use AppBundle\Entity\CommitteeEventRegistration;
use AppBundle\Repository\CommitteeEventRegistrationRepository;
use Doctrine\Common\Persistence\ObjectManager;

class CommitteeEventRegistrationManager
{
    private $manager;
    private $repository;

    public function __construct(ObjectManager $manager, CommitteeEventRegistrationRepository $repository)
    {
        $this->manager = $manager;
        $this->repository = $repository;
    }

    public function findRegistration(string $uuid): ?CommitteeEventRegistration
    {
        return $this->repository->findOneByUuid($uuid);
    }

    public function searchRegistration(
        CommitteeEvent $event,
        string $emailAddress,
        Adherent $adherent = null
    ): ?CommitteeEventRegistration {
        $eventUuid = (string) $event->getUuid();

        if (!$adherent) {
            return $this->repository->findGuestRegistration($eventUuid, $emailAddress);
        }

        return $this->repository->findAdherentRegistration($eventUuid, (string) $adherent->getUuid());
    }

    public function create(CommitteeEventRegistration $registration, bool $flush = true)
    {
        $event = $registration->getEvent();
        $event->incrementParticipantsCount();

        $this->manager->persist($registration);

        if ($flush) {
            $this->manager->flush();
        }
    }

    public function remove(CommitteeEventRegistration $registration, bool $flush = true)
    {
        $event = $registration->getEvent();
        $event->decrementParticipantsCount();

        $this->manager->remove($registration);

        if ($flush) {
            $this->manager->flush();
        }
    }
}
