<?php

namespace App\VotingPlatform\Election\Listener;

use App\Entity\Adherent;
use App\Entity\VotingPlatform\Election;
use App\Entity\VotingPlatform\Vote;
use App\Entity\VotingPlatform\VoteChoice;
use App\Entity\VotingPlatform\Voter;
use App\Entity\VotingPlatform\VoteResult;
use App\Repository\VotingPlatform\CandidateGroupRepository;
use App\Repository\VotingPlatform\ElectionRepository;
use App\Repository\VotingPlatform\VoterRepository;
use App\VotingPlatform\Election\VoteCommand\VoteCommand;
use App\VotingPlatform\Election\VoteCommandStateEnum;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Workflow\Event\Event;

class FinishVoteCommandListener implements EventSubscriberInterface
{
    private $entityManager;
    private $security;
    private $voterRepository;
    private $electionRepository;
    private $candidateGroupRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        Security $security,
        VoterRepository $voterRepository,
        ElectionRepository $electionRepository,
        CandidateGroupRepository $candidateGroupRepository
    ) {
        $this->entityManager = $entityManager;
        $this->security = $security;
        $this->voterRepository = $voterRepository;
        $this->electionRepository = $electionRepository;
        $this->candidateGroupRepository = $candidateGroupRepository;
    }

    public static function getSubscribedEvents()
    {
        return [
            sprintf('workflow.voting_process.completed.%s', VoteCommandStateEnum::TO_FINISH) => ['persistVote'],
        ];
    }

    public function persistVote(Event $event): void
    {
        $command = $event->getSubject();

        if (!$command instanceof VoteCommand) {
            return;
        }

        $election = $this->electionRepository->findByUuid($command->getElectionUuid());

        if (!$election instanceof Election) {
            return;
        }

        // 1. create vote history for the current voter
        $vote = $this->generateVote($election);

        // 2. generate a unique key to save the vote result with
        $voterKey = VoteResult::generateVoterKey();

        // 3. create vote result with unique key
        $voteResult = $this->createVoteResult($election, $command, $voterKey);

        $this->entityManager->persist($vote);
        $this->entityManager->persist($voteResult);

        $this->entityManager->flush();
    }

    private function generateVote(Election $election): Vote
    {
        /** @var Adherent $adherent */
        $adherent = $this->security->getUser();

        if (!$voter = $this->voterRepository->findForAdherent($adherent)) {
            $voter = new Voter($adherent);
        }

        return new Vote($voter, $election);
    }

    private function createVoteResult(Election $election, VoteCommand $command, string $voterKey): VoteResult
    {
        $voteResult = new VoteResult($election, $voterKey);

        foreach ($command->getCandidateGroups() as $choice) {
            $voteChoice = new VoteChoice();

            if (VoteChoice::BLANK_VOTE_VALUE == $choice) {
                $voteChoice->setIsBlank(true);
            } else {
                if (!$group = $this->candidateGroupRepository->findOneByUuid($choice)) {
                    throw new \RuntimeException(sprintf('Candidate group not found with uuid "%s"', $choice));
                }

                $voteChoice->setCandidateGroup($group);
            }

            $voteResult->addVoteChoice($voteChoice);
        }

        return $voteResult;
    }
}
