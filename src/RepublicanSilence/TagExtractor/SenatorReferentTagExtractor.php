<?php

namespace AppBundle\RepublicanSilence\TagExtractor;

use AppBundle\Address\Address;
use AppBundle\Entity\Adherent;

class SenatorReferentTagExtractor implements ReferentTagExtractorInterface
{
    public function extractTags(Adherent $adherent, ?string $slug): array
    {
        $area = $adherent->getSenatorArea();

        return $area ? [$area->getDepartmentTag()->getCode(), Address::FRANCE] : [];
    }
}
