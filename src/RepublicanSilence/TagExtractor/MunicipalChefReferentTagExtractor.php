<?php

namespace App\RepublicanSilence\TagExtractor;

use App\Address\Address;
use App\Entity\Adherent;

class MunicipalChefReferentTagExtractor implements ReferentTagExtractorInterface
{
    public function extractTags(Adherent $adherent, ?string $slug): array
    {
        $area = $adherent->getMunicipalChiefManagedArea();

        return $area ? [$area->getDepartmentalCode(), Address::FRANCE] : [];
    }
}
