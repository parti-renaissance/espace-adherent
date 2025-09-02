<?php

namespace App\NationalEvent\Listener;

use App\NationalEvent\Event\NationalEventInscriptionEventInterface;
use App\NationalEvent\Event\NewNationalEventInscriptionEvent;
use App\NationalEvent\Event\UpdateNationalEventInscriptionEvent;
use App\Repository\Geo\ZoneRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class BindZoneListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ZoneRepository $zoneRepository,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            NewNationalEventInscriptionEvent::class => ['bindZone'],
            UpdateNationalEventInscriptionEvent::class => ['bindZone'],
        ];
    }

    public function bindZone(NationalEventInscriptionEventInterface $event): void
    {
        $inscription = $event->getEventInscription();

        if (empty($inscription->postalCode)) {
            return;
        }

        if ($zone = $this->zoneRepository->findOneByPostalCode($inscription->postalCode)) {
            $inscription->setZones([$zone]);
        } elseif ($zone = $this->zoneRepository->findOneDepartmentByPostalCode($inscription->postalCode)) {
            $inscription->setZones([$zone]);
        } else {
            $inscription->setZones([]);
        }

        $this->entityManager->flush();
    }
}
