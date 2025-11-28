<?php

declare(strict_types=1);

namespace App\Referent;

use App\Entity\Adherent;
use App\Entity\ZoneableEntityInterface;
use App\Repository\Geo\ZoneRepository;
use App\Utils\AreaUtils;

class ReferentZoneManager
{
    private $zoneRepository;

    public function __construct(ZoneRepository $zoneRepository)
    {
        $this->zoneRepository = $zoneRepository;
    }

    public function assignZone(ZoneableEntityInterface $entity): void
    {
        $entity->clearZones();

        if (empty($code = AreaUtils::getZone($entity))) {
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

        $newZone = AreaUtils::getZone($adherent);

        $newZones = $newZone ? [$newZone] : [];

        return $currentZones !== $newZones;
    }
}
