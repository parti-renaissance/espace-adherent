<?php

namespace App\NationalEvent;

use App\Entity\NationalEvent\EventInscription;
use App\Entity\NationalEvent\NationalEvent;
use App\Event\Request\EventInscriptionRequest;
use App\Mailchimp\Synchronisation\Command\NationalEventInscriptionChangeCommand;
use App\NationalEvent\Event\NewNationalEventInscriptionEvent;
use App\NationalEvent\Event\UpdateNationalEventInscriptionEvent;
use App\Repository\AdherentRepository;
use App\Repository\NationalEvent\EventInscriptionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class EventInscriptionManager
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly EventInscriptionRepository $eventInscriptionRepository,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly AdherentRepository $adherentRepository,
        private readonly Notifier $notifier,
    ) {
    }

    public function saveInscription(NationalEvent $nationalEvent, EventInscriptionRequest $inscriptionRequest, ?EventInscription $existingInscription = null): EventInscription
    {
        $eventInscription = $existingInscription ?? new EventInscription($nationalEvent);
        $eventInscription->updateFromRequest($inscriptionRequest);

        if ($adherent = $this->adherentRepository->findOneByEmail($eventInscription->addressEmail)) {
            $eventInscription->adherent = $adherent;
        }

        if (
            $eventInscription->referrerCode
            && !$eventInscription->referrer
            && $referrer = $this->adherentRepository->findByPublicId($eventInscription->referrerCode, true)
        ) {
            $eventInscription->referrer = $referrer;
        }

        $this->entityManager->persist($eventInscription);
        $this->entityManager->flush();

        if ($existingInscription) {
            $this->eventDispatcher->dispatch(new UpdateNationalEventInscriptionEvent($eventInscription));
        } else {
            $this->eventDispatcher->dispatch(new NewNationalEventInscriptionEvent($eventInscription));
        }

        if ($nationalEvent->isCampus() && $eventInscription->isDuplicate() && $eventInscription->originalInscription) {
            $this->notifier->sendDuplicateNotification($eventInscription->originalInscription);
        }

        $this->eventDispatcher->dispatch(new NationalEventInscriptionChangeCommand($eventInscription->getUuid(), $existingInscription?->addressEmail));

        return $eventInscription;
    }

    public function countReservedPlaces(NationalEvent $event): array
    {
        return $this->eventInscriptionRepository->countPlacesByTransport($event->getId(), array_column($event->transportConfiguration['transports'] ?? [], 'id'));
    }
}
