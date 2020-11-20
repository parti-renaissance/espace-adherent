<?php

namespace App\Referent;

use App\Entity\Adherent;
use App\Entity\ZoneableEntity;
use App\Repository\Geo\ZoneRepository;

class ReferentZoneManager
{
    private $zoneRepository;

    public function __construct(ZoneRepository $zoneRepository)
    {
        $this->zoneRepository = $zoneRepository;
    }

    public function assignZone(ZoneableEntity $entity): void
    {
        $entity->clearZones();

        if (empty($code = ManagedAreaUtils::getZone($entity))) {
            return;
        }

        if ($zone = $this->zoneRepository->findOneByCode($code)) {
            $entity->addZone($zone);
        }
    }

    public function isUpdateNeeded(Adherent $adherent): bool
    {
        $currentZones = array_values($adherent->getZonesCodes());
        sort($currentZones);

        $newZone = ManagedAreaUtils::getZone($adherent);

        $newZones = $newZone ? [$newZone] : [];

        return $currentZones !== $newZones;
    }
}
