<?php

namespace App\Event;

use App\Entity\Adherent;
use App\Entity\BaseEvent;
use App\Entity\EventRegistration;
use App\Exception\EventRegistrationException;
use App\Repository\EventRegistrationRepository;
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

    public function findRegistration(?string $uuid): ?EventRegistration
    {
        if (!$uuid) {
            return null;
        }

        return $this->repository->findOneByUuid($uuid);
    }

    public function searchRegistration(BaseEvent $event, string $emailAddress, ?Adherent $adherent): ?EventRegistration
    {
        if ($adherent) {
            return $this->searchAdherentRegistration($event, $adherent);
        }

        return $this->searchGuestRegistration($event, $emailAddress);
    }

    public function searchGuestRegistration(BaseEvent $event, string $emailAddress): ?EventRegistration
    {
        return $this->repository->findGuestRegistration($event->getUuid()->toString(), $emailAddress);
    }

    public function searchAdherentRegistration(BaseEvent $event, Adherent $adherent): ?EventRegistration
    {
        return $this->repository->findAdherentRegistration(
            $event->getUuid()->toString(),
            $adherent->getUuid()->toString()
        );
    }

    public function create(EventRegistration $registration, bool $flush = true): void
    {
        $event = $registration->getEvent();
        $event->incrementParticipantsCount();

        $this->manager->persist($registration);

        if ($flush) {
            $this->manager->flush();
        }
    }

    public function remove(EventRegistration $registration, bool $flush = true): void
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
        if (!\in_array($type = strtolower($type), ['upcoming', 'past'], true)) {
            throw new EventRegistrationException(sprintf('Invalid "type" query string parameter. It must be eiter "upcoming" or "past" but "%s" given.', $type));
        }

        if ('past' === $type) {
            return $this->repository->findPastAdherentRegistrations($adherent->getUuid());
        }

        return $this->repository->findUpcomingAdherentRegistrations($adherent->getUuid());
    }
}
