<?php

namespace AppBundle\Event;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\Event;
use AppBundle\Entity\EventRegistration;
use AppBundle\Exception\EventRegistrationException;
use AppBundle\Repository\EventRegistrationRepository;
use Doctrine\Common\Persistence\ObjectManager;

class EventRegistrationManager
{
    private $manager;
    private $repository;

    public function __construct(ObjectManager $manager, EventRegistrationRepository $repository)
    {
        $this->manager = $manager;
        $this->repository = $repository;
    }

    public function findRegistration(string $uuid): ?EventRegistration
    {
        return $this->repository->findOneByUuid($uuid);
    }

    public function findRegistrations(Event $event): array
    {
        return $this->repository->findByEvent($event);
    }

    public function searchRegistration(
        Event $event,
        string $emailAddress,
        Adherent $adherent = null
    ): ?EventRegistration {
        $eventUuid = (string) $event->getUuid();

        if (!$adherent) {
            return $this->repository->findGuestRegistration($eventUuid, $emailAddress);
        }

        return $this->repository->findAdherentRegistration($eventUuid, (string) $adherent->getUuid());
    }

    public function create(EventRegistration $registration, bool $flush = true)
    {
        $event = $registration->getEvent();
        $event->incrementParticipantsCount();

        $this->manager->persist($registration);

        if ($flush) {
            $this->manager->flush();
        }
    }

    public function remove(EventRegistration $registration, bool $flush = true)
    {
        $event = $registration->getEvent();
        $event->decrementParticipantsCount();

        $this->manager->remove($registration);

        if ($flush) {
            $this->manager->flush();
        }
    }

    /**
     * @throws EventRegistrationException
     */
    public function getAdherentRegistrations(Adherent $adherent, string $type = 'upcoming'): array
    {
        if (!in_array($type = strtolower($type), ['upcoming', 'past'])) {
            throw new EventRegistrationException(sprintf('Invalid "type" query string parameter. It must be eiter "upcoming" or "past" but "%s" given.', $type));
        }

        if ('past' === $type) {
            return $this->repository->findPastAdherentRegistrations($adherent->getUuid());
        }

        return $this->repository->findUpcomingAdherentRegistrations($adherent->getUuid());
    }
}
