<?php

namespace App\Security\Voter\Committee;

use App\Entity\Adherent;
use App\Entity\Committee;
use App\VotingPlatform\Security\LockPeriodManager;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ChangeCommitteeVoteVoter extends Voter
{
    public const PERMISSION = 'ABLE_TO_CHANGE_COMMITTEE_VOTE';
    public const COMMITTEE_IS_NOT_LOCKED = 'COMMITTEE_IS_NOT_LOCKED';

    private $lockPeriodManager;

    public function __construct(LockPeriodManager $lockPeriodManager)
    {
        $this->lockPeriodManager = $lockPeriodManager;
    }

    protected function voteOnAttribute($attribute, $committee, TokenInterface $token)
    {
        $adherent = $token->getUser();

        if (!$adherent instanceof Adherent) {
            return false;
        }

        $isPreviousAdmin = $this->isPreviousAdmin($token);

        // Check if the committee is not in lock period
        if (self::COMMITTEE_IS_NOT_LOCKED === $attribute) {
            if (null === $adherent->getMembershipFor($committee)) {
                return false;
            }

            if ($this->lockPeriodManager->isCommitteeLocked($committee, $isPreviousAdmin)) {
                return false;
            }

            return true;
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

        if (
            ($voteMembership = $adherent->getMemberships()->getVotingCommitteeMembership())
            && $this->lockPeriodManager->isCommitteeLocked($voteMembership->getCommittee(), $isPreviousAdmin)
        ) {
            return false;
        }

        return true;
    }

    protected function supports($attribute, $subject)
    {
        return self::PERMISSION === $attribute
            || (self::COMMITTEE_IS_NOT_LOCKED === $attribute && $subject instanceof Committee)
        ;
    }

    private function isPreviousAdmin(TokenInterface $token)
    {
        foreach ($token->getRoles() as $role) {
            if ('ROLE_PREVIOUS_ADMIN' === $role->getRole()) {
                return true;
            }
        }

        return false;
    }
}
