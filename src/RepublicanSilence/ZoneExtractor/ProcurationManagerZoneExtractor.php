<?php

namespace App\RepublicanSilence\ZoneExtractor;

use App\Entity\Adherent;
use App\Repository\Geo\ZoneRepository;

class ProcurationManagerZoneExtractor implements ZoneExtractorInterface
{
    private ZoneRepository $zoneRepository;

    public function __construct(ZoneRepository $zoneRepository)
    {
        $this->zoneRepository = $zoneRepository;
    }

    public function extractZones(Adherent $adherent, ?string $slug): array
    {
        $area = $adherent->getProcurationManagedArea();

        if ($area) {
            $zones = [];
            foreach ($area->getCodes() as $code) {
                $zone = $this->zoneRepository->findOneBy(['code' => $code]);
                if (!$zone) {
                    $zone = $this->zoneRepository->findOneByPostalCode($code);
                }

                if ($zone) {
                    $zones[] = $zone;
                }
            }
        }

        return $zones ?? [];
    }

    public function supports(int $type): bool
    {
        return ZoneExtractorInterface::ADHERENT_TYPE_PROCURATION_MANAGER === $type;
    }
}
