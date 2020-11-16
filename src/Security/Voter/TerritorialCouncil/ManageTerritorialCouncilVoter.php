<?php

namespace App\Security\Voter\TerritorialCouncil;

use App\Entity\Adherent;
use App\Entity\ReferentTag;
use App\Entity\TerritorialCouncil\TerritorialCouncil;
use App\Security\Voter\AbstractAdherentVoter;

class ManageTerritorialCouncilVoter extends AbstractAdherentVoter
{
    public const PERMISSION = 'CAN_MANAGE_TERRITORIAL_COUNCIL';

    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $subject): bool
    {
        $isGranted = false;

        /** @var TerritorialCouncil $subject */
        if ($adherent->isReferent()) {
            $isGranted = !empty(array_intersect($subject->getReferentTags()->toArray(), $this->getFilteredReferentTags($adherent->getManagedArea()->getTags()->toArray())));
        }

        return $isGranted;
    }

    protected function supports($attribute, $subject)
    {
        return self::PERMISSION === $attribute && $subject instanceof TerritorialCouncil;
    }

    private function getFilteredReferentTags(array $referentTags): array
    {
        return array_filter($referentTags, function (ReferentTag $referentTag) {
            return !$referentTag->isCountryTag();
        });
    }
}
