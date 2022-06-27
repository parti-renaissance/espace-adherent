<?php

namespace App\RepublicanSilence\ZoneExtractor;

use App\Entity\Adherent;

class SenatorZoneExtractor implements ZoneExtractorInterface
{
    public function extractZones(Adherent $adherent, ?string $slug): array
    {
        $area = $adherent->getSenatorArea();

        return $area ? [$area->getDepartmentTag()->getZone()] : [];
    }

    public function supports(int $type): bool
    {
        return ZoneExtractorInterface::ADHERENT_TYPE_SENATOR === $type;
    }
}
