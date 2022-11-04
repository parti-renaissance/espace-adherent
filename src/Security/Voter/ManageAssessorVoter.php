<?php

namespace App\Security\Voter;

use App\Address\Address;
use App\Entity\Adherent;
use App\Entity\AssessorRequest;
use App\Entity\Election\VotePlace;
use App\Entity\ManagedArea;

class ManageAssessorVoter extends AbstractAdherentVoter
{
    private const MANAGE = 'MANAGE_ASSESSOR';

    protected function supports(string $attribute, $subject): bool
    {
        return self::MANAGE === $attribute && $subject instanceof AssessorRequest;
    }

    /**
     * @param AssessorRequest $subject
     */
    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $subject): bool
    {
        return $adherent->isAssessorManager() && $this->isManageable($adherent->getAssessorManagedArea(), $subject);
    }

    private function isManageable(ManagedArea $managedArea, AssessorRequest $assessorRequest): bool
    {
        if (\in_array('ALL', $managedCodes = $managedArea->getCodes(), true)) {
            return true;
        }

        if (\in_array($assessorRequest->getAssessorCountry(), $managedCodes)) {
            return true;
        }

        if (Address::FRANCE === $assessorRequest->getAssessorCountry()) {
            $dpt = substr($assessorRequest->getAssessorPostalCode(), 0, 2);
            if (\in_array($dpt, [97, 98])) {
                $dpt = substr($assessorRequest->getAssessorPostalCode(), 0, 3);
            }

            if (\in_array($dpt, $managedCodes)) {
                return true;
            }
        }

        if (str_contains(implode(',', $managedCodes), 'CIRCO_')) {
            $votePlaceCodes = array_filter($assessorRequest->getVotePlaceWishes()->map(function (VotePlace $votePlace) {
                return $votePlace->zone ? 'CIRCO_'.$votePlace->zone->getCode() : null;
            })->toArray());

            return !empty(array_intersect($votePlaceCodes, $managedCodes));
        }

        return false;
    }
}
