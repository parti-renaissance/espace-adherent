<?php

namespace App\Security\Voter\Committee;

use App\Entity\Adherent;
use App\Entity\Committee;
use App\Security\Voter\AbstractAdherentVoter;
use App\VotingPlatform\Security\LockPeriodManager;

class ChangeCommitteeVoteVoter extends AbstractAdherentVoter
{
    public const PERMISSION = 'ABLE_TO_CHANGE_COMMITTEE_VOTE';

    private $lockPeriodManager;

    public function __construct(LockPeriodManager $lockPeriodManager)
    {
        $this->lockPeriodManager = $lockPeriodManager;
    }

    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $subject): bool
    {
        if (($subject instanceof Committee) && null === $adherent->getMembershipFor($subject)) {
            return false;
        }

        if ($adherent->isSupervisor()) {
            return false;
        }

        if ($adherent->getMemberships()->getCommitteeCandidacyMembership()) {
            return false;
        }

        if ($this->lockPeriodManager->isLocked($adherent)) {
            return false;
        }

        return true;
    }

    protected function supports($attribute, $subject)
    {
        return self::PERMISSION === $attribute;
    }
}
