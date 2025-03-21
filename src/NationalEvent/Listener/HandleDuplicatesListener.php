<?php

namespace App\NationalEvent\Listener;

use App\NationalEvent\Event\NewNationalEventInscriptionEvent;
use App\NationalEvent\InscriptionStatusEnum;
use App\Repository\NationalEvent\EventInscriptionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class HandleDuplicatesListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly EventInscriptionRepository $eventInscriptionRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [NewNationalEventInscriptionEvent::class => 'handleDuplicates'];
    }

    public function handleDuplicates(NewNationalEventInscriptionEvent $event): void
    {
        $newEventInscription = $event->eventInscription;

        $oldEventInscription = $this->eventInscriptionRepository->findDuplicate($newEventInscription);

        if (!$oldEventInscription) {
            return;
        }

        $newEventInscription->status = InscriptionStatusEnum::DUPLICATE;

        $oldEventInscription->update($newEventInscription);

        $this->entityManager->flush();
    }
}
