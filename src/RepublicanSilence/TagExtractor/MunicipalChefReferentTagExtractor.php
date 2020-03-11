<?php

namespace AppBundle\RepublicanSilence\TagExtractor;

use AppBundle\Address\Address;
use AppBundle\Entity\Adherent;

class MunicipalChefReferentTagExtractor implements ReferentTagExtractorInterface
{
    public function extractTags(Adherent $adherent, ?string $slug): array
    {
        $area = $adherent->getMunicipalChiefManagedArea();

        return $area ? [$area->getDepartmentalCode(), Address::FRANCE] : [];
    }
}
