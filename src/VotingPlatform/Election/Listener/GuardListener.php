<?php

namespace App\VotingPlatform\Election\Listener;

use App\Repository\VotingPlatform\ElectionRepository;
use App\Security\Voter\VotingPlatformAccessVoter;
use App\VotingPlatform\Election\VoteCommand\VoteCommand;
use App\VotingPlatform\Election\VoteCommandStateEnum;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Workflow\Event\GuardEvent;

class GuardListener implements EventSubscriberInterface
{
    private $isGranted = null;
    private $isGrantedForInspector = null;

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
            if (false === $this->isGranted && true === $this->isGrantedForInspector) {
                $event->setBlocked(!$this->isGrantedForInspector);

                return;
            }

            $event->setBlocked(!$this->isGranted);

            return;
        }

        /** @var VoteCommand $command */
        $command = $event->getSubject();

        $this->isGranted = $this->authorizationChecker->isGranted(
            VotingPlatformAccessVoter::PERMISSION,
            $this->electionRepository->findOneByUuid($command->getElectionUuid())
        );

        if (false === $this->isGranted && $this->authorizationChecker->isGranted('ROLE_VOTE_INSPECTOR')) {
            $this->isGrantedForInspector = VoteCommandStateEnum::TO_FINISH !== $event->getTransition()->getName();
            $event->setBlocked(!$this->isGrantedForInspector);

            return;
        }

        $event->setBlocked(!$this->isGranted);
    }
}
