<?php

namespace App\RepublicanSilence\ZoneExtractor;

use App\Entity\Adherent;

class DeputyZoneExtractor implements ZoneExtractorInterface
{
    public function extractZones(Adherent $adherent, ?string $slug): array
    {
        if ($zone = $adherent->getDeputyZone()) {
            return [$zone];
        }

        return [];
    }

    public function supports(int $type): bool
    {
        return ZoneExtractorInterface::ADHERENT_TYPE_DEPUTY === $type;
    }
}
