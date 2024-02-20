<?php

namespace App\NationalEvent;

use App\CaptainVerify\Storage;
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
        private readonly Storage $storage
    ) {
    }

    public function handle(NationalEvent $nationalEvent, EventInscriptionRequest $inscriptionRequest): void
    {
        $eventInscription = new EventInscription($nationalEvent);
        $eventInscription->updateFromRequest($inscriptionRequest);

        if ($emailCheckResponse = $this->storage->get($eventInscription->addressEmail)) {
            $eventInscription->emailCheck = $emailCheckResponse;
        }

        $this->entityManager->persist($eventInscription);

        $this->entityManager->flush();

        $this->eventDispatcher->dispatch(new NewNationalEventInscriptionEvent($eventInscription));
    }
}
