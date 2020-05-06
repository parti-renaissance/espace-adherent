<?php

namespace App\RepublicanSilence\TagExtractor;

use App\Address\Address;
use App\Entity\Adherent;

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
