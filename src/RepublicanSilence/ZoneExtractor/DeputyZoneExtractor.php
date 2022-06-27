<?php

namespace App\RepublicanSilence\ZoneExtractor;

use App\Entity\Adherent;

class DeputyZoneExtractor implements ZoneExtractorInterface
{
    public function extractZones(Adherent $adherent, ?string $slug): array
    {
        $district = $adherent->getManagedDistrict();

        if (null === $district) {
            return [];
        }

        return [$district->getReferentTag()->getZone()];
    }

    public function supports(int $type): bool
    {
        return ZoneExtractorInterface::ADHERENT_TYPE_DEPUTY === $type;
    }
}
