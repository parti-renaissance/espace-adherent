<?php

namespace AppBundle\RepublicanSilence\TagExtractor;

use AppBundle\Entity\Adherent;

class ReferentTagExtractor implements ReferentTagExtractorInterface
{
    public function extractTags(Adherent $adherent, ?string $slug): array
    {
        $area = $adherent->getManagedArea();

        return $area ? $area->getReferentTagCodes() : [];
    }
}
