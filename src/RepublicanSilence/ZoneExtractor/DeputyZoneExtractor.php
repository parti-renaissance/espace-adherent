<?php

namespace App\RepublicanSilence\ZoneExtractor;

use App\Entity\Adherent;
use App\Repository\Geo\ZoneRepository;

class DeputyZoneExtractor implements ZoneExtractorInterface
{
    private ZoneRepository $zoneRepository;

    public function __construct(ZoneRepository $zoneRepository)
    {
        $this->zoneRepository = $zoneRepository;
    }

    public function extractZones(Adherent $adherent, ?string $slug): array
    {
        $district = $adherent->getManagedDistrict();

        if (null === $district) {
            return [];
        }

        return $this->zoneRepository->findBy(['geoData' => $district->getGeoData()]);
    }

    public function supports(int $type): bool
    {
        return ZoneExtractorInterface::ADHERENT_TYPE_DEPUTY === $type;
    }
}
