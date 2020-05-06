<?php

namespace App\Security\Voter;

use App\Entity\Adherent;
use App\Entity\AssessorRequest;
use App\Entity\ManagedArea;

class ManageAssessorVoter extends AbstractAdherentVoter
{
    private const MANAGE = 'MANAGE';

    protected function supports($attribute, $subject)
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

    private function isManageable(ManagedArea $managedArea, AssessorRequest $assessorRequest)
    {
        return \in_array(
            'FR' === $assessorRequest->getAssessorCountry()
                ? substr($assessorRequest->getAssessorPostalCode(), 0, 2)
                : $assessorRequest->getAssessorCountry(),
            $managedArea->getCodes()
        );
    }
}
