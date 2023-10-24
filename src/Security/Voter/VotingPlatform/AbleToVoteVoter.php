<?php

namespace App\Security\Voter\VotingPlatform;

use App\Entity\Adherent;
use App\Entity\VotingPlatform\Election;
use App\Repository\VotingPlatform\VoteRepository;
use App\Repository\VotingPlatform\VoterRepository;
use App\Security\Voter\AbstractAdherentVoter;

class AbleToVoteVoter extends AbstractAdherentVoter
{
    public const PERMISSION = 'ABLE_TO_VOTE';
    public const PERMISSION_RESULTS = 'ABLE_TO_SEE_RESULTS';

    public function __construct(
        private readonly VoterRepository $voterRepository,
        private readonly VoteRepository $voteRepository
    ) {
    }

    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $subject): bool
    {
        /** @var Election $subject */
        if (self::PERMISSION === $attribute) {
            if (!$subject->isVotePeriodActive()) {
                return false;
            }

            if ($this->voteRepository->alreadyVoted($adherent, $subject->getCurrentRound())) {
                return false;
            }
        }

        $designation = $subject->getDesignation();

        if (!$designation->isLocalPollType() && $adherent->isRenaissanceSympathizer()) {
            return false;
        }

        if ($designation->isConsultationType() && !$adherent->hasActiveMembership()) {
            return false;
        }

        $adherentIsInVotersList = $this->voterRepository->existsForElection($adherent, $subject->getUuid()->toString());

        if (!$adherentIsInVotersList) {
            // Allow to vote adherent who are not on the list for CONSULTATION election
            if ($designation->isConsultationType()) {
                return true;
            }

            if ($designation->isTerritorialAssemblyType() && $adherent->findActifLocalMandates()) {
                return true;
            }

            return false;
        }

        return true;
    }

    protected function supports(string $attribute, $subject): bool
    {
        return (self::PERMISSION === $attribute || self::PERMISSION_RESULTS === $attribute) && $subject instanceof Election;
    }
}
