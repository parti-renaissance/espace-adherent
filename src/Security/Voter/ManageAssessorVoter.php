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

    private function isManageable(ManagedArea $managedArea, AssessorRequest $assessorRequest): bool
    {
        if (1 === $managedArea->getCodes() && $managedArea->getCodes() === ['ALL']) {
            return true;
        }

        if ('FR' === $assessorRequest->getAssessorCountry()) {
            if (\in_array($assessorRequest->getAssessorCountry(), $managedArea->getCodes())) {
                return true;
            }

            $dpt = substr($assessorRequest->getAssessorPostalCode(), 0, 2);
            if (\in_array($dpt, [97, 98])) {
                $dpt = substr($assessorRequest->getAssessorPostalCode(), 0, 3);
            }

            if (\in_array($dpt, $managedArea->getCodes())) {
                return true;
            }
        } elseif ('FR' !== $assessorRequest->getAssessorCountry() && \in_array($assessorRequest->getAssessorCountry(), $managedArea->getCodes())) {
            return true;
        }

        return false;
    }
}
