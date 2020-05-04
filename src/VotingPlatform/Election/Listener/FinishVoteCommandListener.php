<?php

namespace AppBundle\VotingPlatform\Election\Listener;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\VotingPlatform\Vote;
use AppBundle\Entity\VotingPlatform\Voter;
use AppBundle\Repository\VotingPlatform\VoterRepository;
use AppBundle\VotingPlatform\Election\VoteCommand\VoteCommand;
use AppBundle\VotingPlatform\Election\VoteCommandStateEnum;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Workflow\Event\Event;

class FinishVoteCommandListener implements EventSubscriberInterface
{
    private $entityManager;
    private $security;
    private $voterRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        Security $security,
        VoterRepository $voterRepository
    ) {
        $this->entityManager = $entityManager;
        $this->security = $security;
        $this->voterRepository = $voterRepository;
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

        $voter = $this->getVoter();

        $voter->addVote($this->createVoteObject($command));

        if (!$voter->getId()) {
            $this->entityManager->persist($voter);
        }

        $this->entityManager->flush();
    }

    private function getVoter(): Voter
    {
        /** @var Adherent $adherent */
        $adherent = $this->security->getUser();

        if (!$voter = $this->voterRepository->findForAdherent($adherent)) {
            $voter = new Voter($adherent);
        }

        return $voter;
    }

    private function createVoteObject(VoteCommand $command): Vote
    {
        $vote = new Vote($this->entityManager->merge($command->getElection()));

        foreach ($command->getCandidateGroups() as $group) {
            $vote->addCandidateGroup($this->entityManager->merge($group));
        }

        return $vote;
    }
}
