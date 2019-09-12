<?php

namespace AppBundle\RepublicanSilence\TagExtractor;

use AppBundle\Entity\Adherent;

class MunicipalChefReferentTagExtractor implements ReferentTagExtractorInterface
{
    public function extractTags(Adherent $adherent, ?string $slug): array
    {
        $area = $adherent->getMunicipalChiefManagedArea();

        return $area ? (array) $area->getDepartmentalCode() : [];
    }
}
