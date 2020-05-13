<?php

namespace App\RepublicanSilence\TagExtractor;

use App\Entity\Adherent;
use App\Utils\AreaUtils;

class DistrictReferentTagExtractor implements ReferentTagExtractorInterface
{
    public function extractTags(Adherent $adherent, ?string $slug): array
    {
        return AreaUtils::getCodeFromDistrict($adherent->getManagedDistrict());
    }
}
