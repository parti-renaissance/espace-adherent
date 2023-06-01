<?php

namespace App\Deputy\Subscriber;

use App\Entity\Adherent;
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
        Zone::VOTE_PLACE,
    ];

    private EntityManagerInterface $em;
    private ZoneRepository $repository;

    public function __construct(EntityManagerInterface $em, ZoneRepository $zoneRepository)
    {
        $this->em = $em;
        $this->repository = $zoneRepository;
    }

    public function updateZones(AdherentEvent $event): void
    {
        $adherent = $event->getAdherent();
        $toAdd = [];

        if ($adherent->isForeignResident()) {
            $toAdd = $this->repository->findParent(Zone::FOREIGN_DISTRICT, $adherent->getCountry(), Zone::COUNTRY);
        } elseif ($adherent->isGeocoded()) {
            $latitude = $adherent->getLatitude();
            $longitude = $adherent->getLongitude();

            $toAdd = $this->repository->findByCoordinatesAndTypes($latitude, $longitude, self::TYPES);
        }

        $toAdd = $this->cleanFrenchZonesToAdd($adherent, $toAdd);

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

    /** @param Zone[] $toAdd */
    private function cleanFrenchZonesToAdd(Adherent $adherent, array $toAdd): array
    {
        if ($adherent->isForeignResident() || !($postalCode = $adherent->getPostalCode()) || 5 !== mb_strlen($postalCode)) {
            return $toAdd;
        }

        $toClean = $toKeep = [];

        foreach ($toAdd as $zone) {
            if (\in_array($zone->getType(), [Zone::DISTRICT, Zone::CANTON])) {
                $toClean[] = $zone;
            } else {
                $toKeep[] = $zone;
            }
        }

        foreach ($toClean as $key => $zone) {
            /** @var Zone $dptZone */
            $dptZone = current($zone->getParentsOfType(Zone::DEPARTMENT));

            if ($dptZone && !str_starts_with($postalCode, $dptZone->getCode())) {
                unset($toClean[$key]);
            }
        }

        return array_merge($toKeep, $toClean);
    }
}
