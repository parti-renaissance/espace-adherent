<?php

namespace App\NationalEvent;

use App\Entity\NationalEvent\EventInscription;
use App\Entity\NationalEvent\NationalEvent;
use App\Event\Request\EventInscriptionRequest;
use Doctrine\ORM\EntityManagerInterface;

class EventInscriptionHandler
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function handle(NationalEvent $nationalEvent, EventInscriptionRequest $inscriptionRequest): void
    {
        $eventInscription = new EventInscription($nationalEvent);
        $eventInscription->updateFromRequest($inscriptionRequest);

        $this->entityManager->persist($eventInscription);

        $this->entityManager->flush();
    }
}
