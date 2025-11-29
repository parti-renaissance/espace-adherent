<?php

declare(strict_types=1);

namespace App\Security\Voter\Committee;

use App\Entity\Adherent;
use App\Entity\Committee;
use App\Security\Voter\AbstractAdherentVoter;
use App\VotingPlatform\Security\ElectionAuthorisationChecker;

class CommitteeElectionVoter extends AbstractAdherentVoter
{
    public const PERMISSION_ABLE_TO_CANDIDATE = 'ABLE_TO_CANDIDATE_IN_COMMITTEE';
    public const PERMISSION_ABLE_TO_VOTE = 'ABLE_TO_VOTE_IN_COMMITTEE';
    public const PERMISSION_IS_VOTER = 'IS_VOTER_IN_COMMITTEE';

    private $authorisationChecker;

    public function __construct(ElectionAuthorisationChecker $authorisationChecker)
    {
        $this->authorisationChecker = $authorisationChecker;
    }

    /**
     * @param Committee $subject
     */
    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $subject): bool
    {
        if (self::PERMISSION_ABLE_TO_CANDIDATE === $attribute) {
            return $this->authorisationChecker->canCandidateOnCommittee($subject, $adherent);
        }

        if (self::PERMISSION_ABLE_TO_VOTE === $attribute) {
            return $this->authorisationChecker->canVoteOnCommittee($subject, $adherent);
        }

        if (self::PERMISSION_IS_VOTER === $attribute) {
            return $this->authorisationChecker->isVoterOnCommittee($subject, $adherent);
        }

        return false;
    }

    protected function supports(string $attribute, $subject): bool
    {
        return \in_array($attribute, [self::PERMISSION_ABLE_TO_CANDIDATE, self::PERMISSION_ABLE_TO_VOTE, self::PERMISSION_IS_VOTER], true) && $subject instanceof Committee;
    }
}
