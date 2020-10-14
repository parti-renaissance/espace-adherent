<?php

namespace App\Security\Voter\TerritorialCouncil;

use App\Entity\Adherent;
use App\Entity\TerritorialCouncil\OfficialReport;
use App\Security\Voter\AbstractAdherentVoter;

class DownloadOfficialReportVoter extends AbstractAdherentVoter
{
    public const PERMISSION = 'CAN_DOWNLOAD_OFFICIAL_REPORT';

    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $subject): bool
    {
        $isGranted = false;

        /** @var OfficialReport $subject */
        if ($adherent->isReferent()) {
            $isGranted = !empty(array_intersect($subject->getPoliticalCommittee()->getTerritorialCouncil()->getReferentTagsCodes(), $adherent->getManagedAreaTagCodes()));
        }

        if (!$isGranted && $adherent->hasPoliticalCommitteeMembership()) {
            $isGranted = $adherent->getPoliticalCommitteeMembership()->getPoliticalCommittee() === $subject->getPoliticalCommittee();
        }

        return $isGranted;
    }

    protected function supports($attribute, $subject)
    {
        return self::PERMISSION === $attribute && $subject instanceof OfficialReport;
    }
}
