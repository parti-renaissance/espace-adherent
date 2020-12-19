<?php

namespace App\Security\Voter\Committee;

use App\Entity\Adherent;
use App\Entity\Committee;
use App\Security\Voter\AbstractAdherentVoter;
use App\VotingPlatform\Security\ElectionAuthorisationChecker;

class CommitteeCandidacyVoter extends AbstractAdherentVoter
{
    public const PERMISSION = 'ABLE_TO_CANDIDATE';

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
        return $this->authorisationChecker->canCandidateOnCommittee($subject, $adherent);
    }

    protected function supports($attribute, $subject)
    {
        return self::PERMISSION === $attribute && $subject instanceof Committee;
    }
}
