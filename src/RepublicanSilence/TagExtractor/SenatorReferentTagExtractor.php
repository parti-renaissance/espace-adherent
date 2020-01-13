<?php

namespace AppBundle\RepublicanSilence\TagExtractor;

use AppBundle\Entity\Adherent;

class SenatorReferentTagExtractor implements ReferentTagExtractorInterface
{
    public function extractTags(Adherent $adherent, ?string $slug): array
    {
        $area = $adherent->getSenatorArea();

        return $area ? (array) $area->getDepartmentTag()->getCode() : [];
    }
}
