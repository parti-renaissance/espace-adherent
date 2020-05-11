<?php

namespace AppBundle\VotingPlatform\Election\Listener;

use AppBundle\Entity\Adherent;
use AppBundle\Repository\VotingPlatform\VoteRepository;
use AppBundle\Repository\VotingPlatform\VotersListRepository;
use AppBundle\VotingPlatform\Election\VoteCommand\VoteCommand;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Workflow\Event\GuardEvent;

class GuardListener implements EventSubscriberInterface
{
    private $voteRepository;
    private $security;

    private $isGranted = null;
    private $votersListRepository;

    public function __construct(
        VoteRepository $voteRepository,
        VotersListRepository $votersListRepository,
        Security $security
    ) {
        $this->voteRepository = $voteRepository;
        $this->votersListRepository = $votersListRepository;
        $this->security = $security;
    }

    public static function getSubscribedEvents()
    {
        return [
            'workflow.voting_process.guard' => ['guardStart'],
        ];
    }

    public function guardStart(GuardEvent $event): void
    {
        if (null !== $this->isGranted) {
            $event->setBlocked(!$this->isGranted);

            return;
        }

        /** @var VoteCommand $command */
        $command = $event->getSubject();

        /** @var Adherent $adherent */
        $adherent = $this->security->getUser();

        $adherentIsInVotersList = $this->votersListRepository->existsForElection($adherent, $command->getElectionUuid());

        if (!$adherentIsInVotersList) {
            $this->isGranted = false;
            $event->setBlocked(true);
        }

        $alreadyVoted = $this->voteRepository->alreadyVoted($adherent, $command->getElectionUuid());

        if ($alreadyVoted) {
            $this->isGranted = false;
            $event->setBlocked(true);
        }

        $this->isGranted = true === $adherentIsInVotersList && false === $alreadyVoted;
    }
}
