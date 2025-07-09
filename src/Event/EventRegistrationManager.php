<?php

namespace App\Event;

use App\Entity\Adherent;
use App\Entity\Event\Event;
use App\Entity\Event\EventRegistration;
use App\Exception\EventRegistrationException;
use App\Repository\EventRegistrationRepository;
use Doctrine\ORM\EntityManagerInterface;

class EventRegistrationManager
{
    public function __construct(
        private readonly EntityManagerInterface $manager,
        private readonly EventRegistrationRepository $repository,
    ) {
    }

    public function findRegistration(?string $uuid): ?EventRegistration
    {
        if (!$uuid) {
            return null;
        }

        return $this->repository->findOneByUuid($uuid);
    }

    public function searchRegistration(Event $event, string $emailAddress, ?Adherent $adherent): ?EventRegistration
    {
        if ($adherent) {
            return $this->searchAdherentRegistration($event, $adherent);
        }

        return $this->searchGuestRegistration($event, $emailAddress);
    }

    public function searchGuestRegistration(Event $event, string $emailAddress): ?EventRegistration
    {
        return $this->repository->findGuestRegistration($event->getUuid()->toString(), $emailAddress);
    }

    public function searchAdherentRegistration(Event $event, Adherent $adherent): ?EventRegistration
    {
        return $this->repository->findAdherentRegistration(
            $event->getUuid()->toString(),
            $adherent->getUuid()->toString()
        );
    }

    public function create(EventRegistration $registration): void
    {
        $event = $registration->getEvent();

        if ($registration->isConfirmed()) {
            $adherent = $registration->getAdherent();

            $event->updateMembersCount(true, $adherent);
        }

        $this->manager->persist($registration);

        $this->manager->flush();
    }

    public function remove(EventRegistration $registration): void
    {
        $event = $registration->getEvent();

        if ($registration->isConfirmed()) {
            $adherent = $registration->getAdherent();

            $event->updateMembersCount(false, $adherent);
        }

        $this->manager->remove($registration);
        $this->manager->flush();
    }

    /**
     * @throws EventRegistrationException
     */
    public function getAdherentRegistrations(Adherent $adherent, string $type = 'upcoming'): array
    {
        if (!\in_array($type = strtolower($type), ['upcoming', 'past'], true)) {
            throw new EventRegistrationException(\sprintf('Invalid "type" query string parameter. It must be eiter "upcoming" or "past" but "%s" given.', $type));
        }

        if ('past' === $type) {
            return $this->repository->findPastAdherentRegistrations($adherent->getUuid());
        }

        return $this->repository->findUpcomingAdherentRegistrations($adherent->getUuid());
    }
}
