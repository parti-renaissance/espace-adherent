<?php

namespace AppBundle\VotingPlatform\Election\Listener;

use AppBundle\VotingPlatform\Election\VoteCommand\VoteCommand;
use AppBundle\VotingPlatform\Election\VoteCommandStateEnum;
use AppBundle\VotingPlatform\Election\VoteCommandStorage;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\Event;

class ManageVoteCommandStoreListener implements EventSubscriberInterface
{
    /**
     * @var VoteCommandStorage
     */
    private $storage;

    public function __construct(VoteCommandStorage $storage)
    {
        $this->storage = $storage;
    }

    public static function getSubscribedEvents()
    {
        return [
            'workflow.voting_process.completed' => ['manageStore'],
        ];
    }

    public function manageStore(Event $event): void
    {
        $command = $event->getSubject();

        if (!$command instanceof VoteCommand) {
            return;
        }

        if (VoteCommandStateEnum::TO_FINISH === $event->getTransition()->getName()) {
            $this->storage->clear();
        } else {
            $this->storage->save($command);
        }
    }
}
