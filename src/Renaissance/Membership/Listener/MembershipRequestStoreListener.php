<?php

namespace App\Renaissance\Membership\Listener;

use App\Membership\MembershipRequest\RenaissanceMembershipRequest;
use App\Renaissance\Membership\MembershipRequestStateEnum;
use App\Renaissance\Membership\MembershipRequestStorage;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\Event;

class MembershipRequestStoreListener implements EventSubscriberInterface
{
    private MembershipRequestStorage $storage;

    public function __construct(MembershipRequestStorage $storage)
    {
        $this->storage = $storage;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'workflow.membership_request.completed' => 'manageCommandStore',
        ];
    }

    public function manageCommandStore(Event $event): void
    {
        $command = $event->getSubject();

        if (!$command instanceof RenaissanceMembershipRequest) {
            return;
        }

        if (MembershipRequestStateEnum::TO_FINISH === $event->getTransition()->getName()) {
            $this->storage->clear();
        } else {
            $this->storage->save($command);
        }
    }
}
