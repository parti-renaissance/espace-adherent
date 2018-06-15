<?php

namespace AppBundle\RepublicanSilence\TagExtractor;

use AppBundle\Entity\Adherent;
use AppBundle\Utils\AreaUtils;

class DistrictReferentTagExtractor implements ReferentTagExtractorInterface
{
    public function extractTags(Adherent $adherent, ?string $slug): array
    {
        return AreaUtils::getCodeFromDistrict($adherent->getManagedDistrict());
    }
}
