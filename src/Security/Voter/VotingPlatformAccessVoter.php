<?php

namespace App\Security\Voter;

use App\Entity\Adherent;
use App\Entity\VotingPlatform\Election;
use App\Repository\VotingPlatform\VoteRepository;
use App\Repository\VotingPlatform\VoterRepository;

class VotingPlatformAccessVoter extends AbstractAdherentVoter
{
    public const PERMISSION = 'ABLE_TO_VOTE';

    private $voterRepository;
    private $voteRepository;

    public function __construct(VoterRepository $voterRepository, VoteRepository $voteRepository)
    {
        $this->voterRepository = $voterRepository;
        $this->voteRepository = $voteRepository;
    }

    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $subject): bool
    {
        /** @var Election $subject */
        if (!$subject->isVotePeriodActive()) {
            return false;
        }

        $adherentIsInVotersList = $this->voterRepository->existsForElection($adherent, $subject->getUuid()->toString());

        if (!$adherentIsInVotersList) {
            return false;
        }

        $alreadyVoted = $this->voteRepository->alreadyVoted($adherent, $subject->getUuid()->toString());

        if ($alreadyVoted) {
            return false;
        }

        return true;
    }

    protected function supports($attribute, $subject)
    {
        return self::PERMISSION === $attribute && $subject instanceof Election;
    }
}
