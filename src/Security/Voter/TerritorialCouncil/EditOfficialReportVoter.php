<?php

namespace App\Security\Voter\TerritorialCouncil;

use App\Entity\Adherent;
use App\Entity\TerritorialCouncil\OfficialReport;
use App\Security\Voter\AbstractAdherentVoter;

class EditOfficialReportVoter extends AbstractAdherentVoter
{
    public const PERMISSION = 'CAN_EDIT_OFFICIAL_REPORT';

    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $subject): bool
    {
        $isGranted = false;

        /** @var OfficialReport $subject */
        if ($adherent->isReferent()) {
            $isGranted = !empty(array_intersect($subject->getPoliticalCommittee()->getTerritorialCouncil()->getReferentTagsCodes(), $adherent->getManagedAreaTagCodes()));
        }

        return $isGranted;
    }

    protected function supports($attribute, $subject)
    {
        return self::PERMISSION === $attribute && $subject instanceof OfficialReport;
    }
}
