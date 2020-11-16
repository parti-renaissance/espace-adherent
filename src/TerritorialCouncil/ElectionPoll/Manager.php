<?php

namespace App\TerritorialCouncil\ElectionPoll;

use App\Entity\TerritorialCouncil\ElectionPoll\Poll;
use App\Entity\TerritorialCouncil\ElectionPoll\PollChoice;
use App\Entity\TerritorialCouncil\ElectionPoll\Vote;
use App\Entity\TerritorialCouncil\TerritorialCouncilMembership;
use App\Repository\TerritorialCouncil\ElectionPoll\PollChoiceRepository;
use App\Repository\TerritorialCouncil\ElectionPoll\VoteRepository;
use Doctrine\ORM\EntityManagerInterface;

class Manager
{
    private $entityManager;
    private $voteRepository;
    private $pollChoiceRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        VoteRepository $voteRepository,
        PollChoiceRepository $pollChoiceRepository
    ) {
        $this->entityManager = $entityManager;
        $this->voteRepository = $voteRepository;
        $this->pollChoiceRepository = $pollChoiceRepository;
    }

    public function vote(PollChoice $choice, TerritorialCouncilMembership $membership): void
    {
        $this->entityManager->persist(new Vote($choice, $membership));
        $this->entityManager->flush();
    }

    public function hasVoted(Poll $electionPoll, TerritorialCouncilMembership $membership): bool
    {
        return $this->voteRepository->hasVoted($electionPoll, $membership);
    }

    public function findChoice(string $choiceUuid): ?PollChoice
    {
        return $this->pollChoiceRepository->findOneByUuid($choiceUuid);
    }

    public function findVote(Poll $poll, TerritorialCouncilMembership $membership): ?Vote
    {
        return $this->voteRepository->findOneForMembership($poll, $membership);
    }
}
