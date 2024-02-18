<?php

namespace App\NationalEvent;

use App\Entity\NationalEvent\EventInscription;
use App\Entity\NationalEvent\NationalEvent;
use App\Event\Request\EventInscriptionRequest;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class EventInscriptionHandler
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function handle(NationalEvent $nationalEvent, EventInscriptionRequest $inscriptionRequest): void
    {
        $eventInscription = new EventInscription($nationalEvent);
        $eventInscription->updateFromRequest($inscriptionRequest);

        $this->entityManager->persist($eventInscription);

        $this->entityManager->flush();

        $this->eventDispatcher->dispatch(new NewNationalEventInscriptionEvent($eventInscription));
    }
}
