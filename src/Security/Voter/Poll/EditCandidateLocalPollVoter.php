<?php

declare(strict_types=1);

namespace App\Security\Voter\Poll;

use App\Entity\Adherent;
use App\Entity\Poll\LocalPoll;
use App\Security\Voter\AbstractAdherentVoter;

class EditCandidateLocalPollVoter extends AbstractAdherentVoter
{
    public const PERMISSION = 'CAN_EDIT_CANDIDATE_LOCAL_POLL';

    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $subject): bool
    {
        $managedZone = null;

        if ($adherent->isCandidate()) {
            $managedZone = $adherent->getCandidateManagedArea()->getZone();
        }

        if (!$managedZone) {
            return false;
        }

        return \in_array($managedZone, $subject->getZone()->getWithParents());
    }

    protected function supports(string $attribute, $subject): bool
    {
        return self::PERMISSION === $attribute && $subject instanceof LocalPoll;
    }
}
