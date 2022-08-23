<?php

namespace App\Renaissance\Membership\Listener;

use App\Renaissance\Membership\MembershipRequestCommand;
use App\Renaissance\Membership\MembershipRequestCommandStateEnum;
use App\Renaissance\Membership\MembershipRequestCommandStorage;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\Event;

class MembershipRequestCommandStoreListener implements EventSubscriberInterface
{
    private MembershipRequestCommandStorage $storage;

    public function __construct(MembershipRequestCommandStorage $storage)
    {
        $this->storage = $storage;
    }

    public static function getSubscribedEvents()
    {
        return [
            'workflow.membership_request.completed' => 'manageCommandStore',
        ];
    }

    public function manageCommandStore(Event $event): void
    {
        $command = $event->getSubject();

        if (!$command instanceof MembershipRequestCommand) {
            return;
        }

        if (MembershipRequestCommandStateEnum::TO_FINISH === $event->getTransition()->getName()) {
            $this->storage->clear();
        } else {
            $this->storage->save($command);
        }
    }
}
