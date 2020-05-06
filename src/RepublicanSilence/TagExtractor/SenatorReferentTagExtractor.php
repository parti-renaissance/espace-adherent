<?php

namespace App\RepublicanSilence\TagExtractor;

use App\Address\Address;
use App\Entity\Adherent;

class SenatorReferentTagExtractor implements ReferentTagExtractorInterface
{
    public function extractTags(Adherent $adherent, ?string $slug): array
    {
        $area = $adherent->getSenatorArea();

        return $area ? [$area->getDepartmentTag()->getCode(), Address::FRANCE] : [];
    }
}
