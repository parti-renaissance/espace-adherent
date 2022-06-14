<?php

namespace App\RepublicanSilence\ZoneExtractor;

use App\Entity\Adherent;

class ReferentZoneExtractor implements ZoneExtractorInterface
{
    public function extractZones(Adherent $adherent, ?string $slug): array
    {
        $area = $adherent->getManagedArea();

        return $area ? $area->getZones()->toArray() : [];
    }

    public function supports(int $type): bool
    {
        return ZoneExtractorInterface::ADHERENT_TYPE_REFERENT === $type;
    }
}
