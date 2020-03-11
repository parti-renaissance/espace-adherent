<?php

namespace AppBundle\RepublicanSilence\TagExtractor;

use AppBundle\Address\Address;
use AppBundle\Entity\Adherent;

class ReferentTagExtractor implements ReferentTagExtractorInterface
{
    public function extractTags(Adherent $adherent, ?string $slug): array
    {
        $area = $adherent->getManagedArea();

        if ($area) {
            $codes = $area->getReferentTagCodes();

            if ($area->hasFranceTag()) {
                $codes[] = Address::FRANCE;
            }
        }

        return $codes ?? [];
    }
}
