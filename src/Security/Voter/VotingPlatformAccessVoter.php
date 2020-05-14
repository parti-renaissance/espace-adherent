<?php

namespace App\Security\Voter;

use App\Entity\Adherent;
use App\Entity\VotingPlatform\Election;
use App\Repository\VotingPlatform\VoteRepository;
use App\Repository\VotingPlatform\VotersListRepository;

class VotingPlatformAccessVoter extends AbstractAdherentVoter
{
    public const PERMISSION = 'ABLE_TO_VOTE';

    private $votersListRepository;
    private $voteRepository;

    public function __construct(VotersListRepository $votersListRepository, VoteRepository $voteRepository)
    {
        $this->votersListRepository = $votersListRepository;
        $this->voteRepository = $voteRepository;
    }

    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $subject): bool
    {
        /** @var Election $subject */
        $adherentIsInVotersList = $this->votersListRepository->existsForElection($adherent, $subject->getUuid()->toString());

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
