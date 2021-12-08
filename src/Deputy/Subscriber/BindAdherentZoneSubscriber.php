<?php

namespace App\Deputy\Subscriber;

use App\Entity\Geo\Zone;
use App\Membership\AdherentEvents;
use App\Membership\Event\AdherentEvent;
use App\Repository\Geo\ZoneRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class BindAdherentZoneSubscriber implements EventSubscriberInterface
{
    private const TYPES = [
        Zone::CANTON,
        Zone::DISTRICT,
        Zone::FOREIGN_DISTRICT,
    ];

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var ZoneRepository
     */
    private $repository;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->repository = $em->getRepository(Zone::class);
    }

    public function updateZones(AdherentEvent $event): void
    {
        $adherent = $event->getAdherent();
        if (!$adherent->isGeocoded()) {
            return;
        }

        $latitude = $adherent->getLatitude();
        $longitude = $adherent->getLongitude();
        $toAdd = $this->repository->findByCoordinatesAndTypes($latitude, $longitude, self::TYPES);
        if (empty($toAdd)) {
            return;
        }

        $toRemove = $adherent->getZones()->filter(
            static function (Zone $zone): bool {
                return \in_array($zone->getType(), self::TYPES, true);
            }
        );

        foreach ($toRemove as $zone) {
            $adherent->removeZone($zone);
        }

        foreach ($toAdd as $zone) {
            $adherent->addZone($zone);
        }

        $this->em->flush();
    }

    public static function getSubscribedEvents(): array
    {
        return [
            AdherentEvents::REGISTRATION_COMPLETED => ['updateZones', -257],
            AdherentEvents::PROFILE_UPDATED => ['updateZones', -257],
        ];
    }
}
