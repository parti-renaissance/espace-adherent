<?php

namespace App\Referent;

use App\Entity\Adherent;
use App\Entity\ApplicationRequest\ApplicationRequest;
use App\Entity\ReferentTaggableEntity;
use App\Intl\FranceCitiesBundle;
use App\Repository\ReferentTagRepository;
use App\Utils\AreaUtils;

class ReferentTagManager
{
    private $referentTagRepository;

    public function __construct(ReferentTagRepository $referentTagRepository)
    {
        $this->referentTagRepository = $referentTagRepository;
    }

    public function assignReferentLocalTags(ReferentTaggableEntity $entity): void
    {
        $entity->clearReferentTags();

        if (empty($codes = ManagedAreaUtils::getLocalCodes($entity))) {
            return;
        }

        foreach ($this->referentTagRepository->findByCodes($codes) as $referentTag) {
            $entity->addReferentTag($referentTag);
        }
    }

    public function assignApplicationRequestReferentTags(ApplicationRequest $applicationRequest): void
    {
        $applicationRequest->clearReferentTags();

        $codes = [];
        foreach ($applicationRequest->getFavoriteCities() as $inseeCode) {
            $localCode = [ManagedAreaUtils::getCodeFromPostalCode($inseeCode)];

            $relatedCodes = ManagedAreaUtils::getRelatedCodes($localCode[0]);

            if (\in_array(AreaUtils::PREFIX_POSTALCODE_PARIS_DISTRICTS, $relatedCodes, true)) {
                $localCode[] = FranceCitiesBundle::SPECIAL_CITY_DISTRICTS[FranceCitiesBundle::CUSTOM_CITY_CODE_PARIS][$inseeCode];
            }

            $codes = array_merge($codes, $localCode, $relatedCodes);
        }

        foreach ($this->referentTagRepository->findByCodes(array_unique($codes)) as $referentTag) {
            $applicationRequest->addReferentTag($referentTag);
        }
    }

    public function isUpdateNeeded(Adherent $adherent): bool
    {
        $currentTags = array_values($adherent->getReferentTagsCodes());
        sort($currentTags);

        $newTags = ManagedAreaUtils::getLocalCodes($adherent);
        $newTags = array_values($newTags);
        sort($newTags);

        return $currentTags !== $newTags;
    }
}
