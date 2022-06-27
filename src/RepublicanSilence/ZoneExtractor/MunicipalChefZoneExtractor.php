<?php

namespace App\RepublicanSilence\ZoneExtractor;

use App\Entity\Adherent;
use App\Repository\Geo\ZoneRepository;

class MunicipalChefZoneExtractor implements ZoneExtractorInterface
{
    private ZoneRepository $zoneRepository;

    public function __construct(ZoneRepository $zoneRepository)
    {
        $this->zoneRepository = $zoneRepository;
    }

    public function extractZones(Adherent $adherent, ?string $slug): array
    {
        $area = $adherent->getMunicipalChiefManagedArea();

        return $area ? [$this->zoneRepository->findByInseeCode($area->getInseeCode())] : [];
    }

    public function supports(int $type): bool
    {
        return ZoneExtractorInterface::ADHERENT_TYPE_MUNICIPAL_CHIEF === $type;
    }
}
