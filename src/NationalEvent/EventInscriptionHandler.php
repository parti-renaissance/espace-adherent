<?php

namespace App\NationalEvent;

use App\Entity\NationalEvent\EventInscription;
use App\Entity\NationalEvent\NationalEvent;
use App\Event\Request\EventInscriptionRequest;
use App\NationalEvent\Event\NewNationalEventInscriptionEvent;
use App\NationalEvent\Event\UpdateNationalEventInscriptionEvent;
use App\Repository\AdherentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class EventInscriptionHandler
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly AdherentRepository $adherentRepository,
        private readonly Notifier $notifier,
    ) {
    }

    public function handle(NationalEvent $nationalEvent, EventInscriptionRequest $inscriptionRequest, ?EventInscription $existingInscription = null): EventInscription
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

        return $eventInscription;
    }
}
