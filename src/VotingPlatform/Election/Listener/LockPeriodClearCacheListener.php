<?php

namespace App\VotingPlatform\Election\Listener;

use App\VotingPlatform\Election\VoteCommand\VoteCommand;
use App\VotingPlatform\Election\VoteCommandStateEnum;
use App\VotingPlatform\Security\LockPeriodManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Workflow\Event\Event;

class LockPeriodClearCacheListener implements EventSubscriberInterface
{
    private $security;
    private $lockPeriodManager;

    public function __construct(LockPeriodManager $lockPeriodManager, Security $security)
    {
        $this->lockPeriodManager = $lockPeriodManager;
        $this->security = $security;
    }

    public static function getSubscribedEvents()
    {
        return [
            sprintf('workflow.voting_process.completed.%s', VoteCommandStateEnum::TO_FINISH) => ['clearCache', -1],
        ];
    }

    public function clearCache(Event $event): void
    {
        /** @var VoteCommand $command */
        $command = $event->getSubject();

        if (!$command instanceof VoteCommand) {
            return;
        }

        $this->lockPeriodManager->clearForAdherent($this->security->getUser());
    }
}
