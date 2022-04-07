<?php

namespace App\RepublicanSilence\TagExtractor;

use App\Address\Address;
use App\Entity\Adherent;

class ProcurationManagerTagExtractor implements ReferentTagExtractorInterface
{
    public function extractTags(Adherent $adherent, ?string $slug): array
    {
        $area = $adherent->getProcurationManagedArea();

        if ($area) {
            $codes = $area->getCodes();
            $codes[] = Address::FRANCE;
        }

        return $codes ?? [];
    }
}
