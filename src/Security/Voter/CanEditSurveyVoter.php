<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\Adherent;
use App\Entity\Jecoute\LocalSurvey;

class CanEditSurveyVoter extends AbstractAdherentVoter
{
    public const PERMISSION = 'CAN_EDIT_SURVEY';

    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $subject): bool
    {
        /** @var LocalSurvey $subject */
        if ($subject->getCreator() && $subject->getCreator()->equals($adherent)) {
            return true;
        }

        $surveyZone = $subject->getZone();

        if ($adherent->isJecouteManager()) {
            $managedZone = $adherent->getJecouteManagedArea()->getZone();
        }

        if ($adherent->isLeaderRegionalCandidate() || $adherent->isHeadedRegionalCandidate()) {
            $managedZone = $adherent->getCandidateManagedArea()->getZone();
        }

        if (isset($managedZone)) {
            return $surveyZone === $managedZone
                || (!$subject->hasBlockedChanges() && $managedZone->hasChild($surveyZone));
        }

        return false;
    }

    protected function supports(string $attribute, $subject): bool
    {
        return self::PERMISSION === $attribute && $subject instanceof LocalSurvey;
    }
}
