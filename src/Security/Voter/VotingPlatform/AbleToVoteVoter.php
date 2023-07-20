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

    public function __construct(
        private readonly VoterRepository $voterRepository,
        private readonly VoteRepository $voteRepository
    ) {
    }

    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $subject): bool
    {
        /** @var Election $subject */
        if (!$subject->isVotePeriodActive()) {
            return false;
        }

        if (!$subject->getDesignation()->isLocalPollType() && $adherent->isRenaissanceSympathizer()) {
            return false;
        }

        $adherentIsInVotersList = $this->voterRepository->existsForElection($adherent, $subject->getUuid()->toString());

        if (!$adherentIsInVotersList) {
            return false;
        }

        return !$this->voteRepository->alreadyVoted($adherent, $subject->getCurrentRound());
    }

    protected function supports(string $attribute, $subject): bool
    {
        return self::PERMISSION === $attribute && $subject instanceof Election;
    }
}
