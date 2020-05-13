<?php

namespace App\VotingPlatform\Election\Listener;

use App\Repository\VotingPlatform\ElectionRepository;
use App\Security\Voter\VotingPlatformAccessVoter;
use App\VotingPlatform\Election\VoteCommand\VoteCommand;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Workflow\Event\GuardEvent;

class GuardListener implements EventSubscriberInterface
{
    private $isGranted = null;

    private $electionRepository;
    private $authorizationChecker;

    public function __construct(
        ElectionRepository $electionRepository,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->electionRepository = $electionRepository;
        $this->authorizationChecker = $authorizationChecker;
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

        $this->isGranted = $this->authorizationChecker->isGranted(
            VotingPlatformAccessVoter::PERMISSION,
            $this->electionRepository->findByUuid($command->getElectionUuid())
        );
    }
}
