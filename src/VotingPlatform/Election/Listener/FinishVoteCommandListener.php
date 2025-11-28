<?php

declare(strict_types=1);

namespace App\VotingPlatform\Election\Listener;

use App\Entity\Adherent;
use App\Entity\VotingPlatform\Election;
use App\Entity\VotingPlatform\ElectionRound;
use App\Entity\VotingPlatform\Vote;
use App\Entity\VotingPlatform\VoteChoice;
use App\Entity\VotingPlatform\Voter;
use App\Entity\VotingPlatform\VoteResult;
use App\Repository\VotingPlatform\CandidateGroupRepository;
use App\Repository\VotingPlatform\ElectionPoolRepository;
use App\Repository\VotingPlatform\ElectionRepository;
use App\Repository\VotingPlatform\VoterRepository;
use App\VotingPlatform\Election\Event\NewVote;
use App\VotingPlatform\Election\VoteCommand\VoteCommand;
use App\VotingPlatform\Election\VoteCommandStateEnum;
use App\VotingPlatform\Election\VoteCommandStorage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\Event;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class FinishVoteCommandListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly Security $security,
        private readonly VoterRepository $voterRepository,
        private readonly ElectionRepository $electionRepository,
        private readonly CandidateGroupRepository $candidateGroupRepository,
        private readonly VoteCommandStorage $storage,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly ElectionPoolRepository $poolRepository,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            \sprintf('workflow.voting_process.completed.%s', VoteCommandStateEnum::TO_FINISH) => ['persistVote'],
        ];
    }

    public function persistVote(Event $event): void
    {
        /** @var VoteCommand $command */
        $command = $event->getSubject();

        if (!$command instanceof VoteCommand) {
            return;
        }

        $election = $this->electionRepository->findOneByUuid($command->getElectionUuid());

        if (!$election instanceof Election) {
            return;
        }

        $electionRound = $election->getCurrentRound();

        /** @var Adherent $adherent */
        $adherent = $this->security->getUser();

        // 0. generate voter profil
        $voter = $this->getVoter($adherent, $election);

        // 1. create vote history for the current voter
        $vote = new Vote($voter, $electionRound);

        // 2. generate a unique key to save the vote result with
        $voterKey = VoteResult::generateVoterKey();

        // 3. create vote result with unique key
        $voteResult = $this->createVoteResult($electionRound, $command, $voterKey, $adherent->getAssemblyZone()?->getCode());

        // 4. delete voters from other voters lists for the same designation
        $voter = $vote->getVoter();
        foreach ($voter->getVotersListsForDesignation($election->getDesignation()) as $list) {
            if ($list->getId() === $election->getVotersList()->getId()) {
                continue;
            }

            $list->removeVoter($voter);
        }
        $voter->setIsGhost(false);

        $this->entityManager->persist($vote);
        $this->entityManager->persist($voteResult);

        $this->entityManager->flush();

        $this->saveVoterKeyInSession($voterKey);

        $this->eventDispatcher->dispatch(new NewVote($election, $voter, $voterKey));
    }

    private function getVoter(Adherent $adherent, Election $election): Voter
    {
        if (!$voter = $this->voterRepository->findForAdherent($adherent)) {
            $voter = new Voter($adherent);
        }

        if ($voterList = $election->getVotersList()) {
            $voterList->addVoter($voter);
        }

        return $voter;
    }

    private function createVoteResult(ElectionRound $electionRound, VoteCommand $command, string $voterKey, ?string $zoneCode): VoteResult
    {
        $voteResult = new VoteResult($electionRound, $voterKey, $zoneCode);

        foreach ($command->getChoicesByPools() as $poolId => $choice) {
            if (!$pool = $this->poolRepository->findForResult($poolId)) {
                continue;
            }

            if ($command->isMajorityVote()) {
                foreach ($choice as $candidateGroupUuid => $mention) {
                    if (!$group = $this->candidateGroupRepository->findOneByUuid($candidateGroupUuid)) {
                        throw new \RuntimeException(\sprintf('Candidate group not found with uuid "%s"', $choice));
                    }

                    $voteChoice = new VoteChoice($pool);
                    $voteChoice->setCandidateGroup($group);
                    $voteChoice->setMention($mention);

                    $voteResult->addVoteChoice($voteChoice);
                }
            } else {
                $voteChoice = new VoteChoice($pool);

                if (VoteChoice::BLANK_VOTE_VALUE == $choice) {
                    $voteChoice->setIsBlank(true);
                } else {
                    if (!$group = $this->candidateGroupRepository->findOneByUuid($choice)) {
                        throw new \RuntimeException(\sprintf('Candidate group not found with uuid "%s"', $choice));
                    }

                    $voteChoice->setCandidateGroup($group);
                }

                $voteResult->addVoteChoice($voteChoice);
            }
        }

        return $voteResult;
    }

    private function saveVoterKeyInSession(string $voterKey): void
    {
        $this->storage->saveLastVoterKey($voterKey);
    }
}
