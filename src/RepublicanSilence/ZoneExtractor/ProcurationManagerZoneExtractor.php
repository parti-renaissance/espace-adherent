<?php

namespace App\RepublicanSilence\ZoneExtractor;

use App\Entity\Adherent;
use App\Scope\ScopeEnum;

class ProcurationManagerZoneExtractor implements ZoneExtractorInterface
{
    public function extractZones(Adherent $adherent, ?string $slug): array
    {
        if ($role = $adherent->findZoneBasedRole(ScopeEnum::PROCURATIONS_MANAGER)) {
            return $role->getZones()->toArray();
        }

        return [];
    }

    public function supports(int $type): bool
    {
        return ZoneExtractorInterface::ADHERENT_TYPE_PROCURATION_MANAGER === $type;
    }
}
