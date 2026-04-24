<?php

declare(strict_types=1);

namespace App\Committee\EventListener;

use App\Committee\Event\CommitteeMembershipEventInterface;
use App\Committee\Event\FollowCommitteeEvent;
use App\Event\Command\InviteAdherentForAllFutureInvitationEventsCommand;
use App\Event\Command\RemoveAdherentForAllFutureInvitationEventsCommand;
use App\Membership\UserEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class UpdateCommitteeMemberEventInvitationsListener implements EventSubscriberInterface
{
    public function __construct(private readonly MessageBusInterface $messageBus)
    {
    }

    public function onCommitteeMembershipChange(CommitteeMembershipEventInterface $event): void
    {
        $membership = $event->getCommitteeMembership();
        $command = $event instanceof FollowCommitteeEvent
            ? new InviteAdherentForAllFutureInvitationEventsCommand($membership->getAdherentUuid(), committeeId: $membership->getCommittee()->getId())
            : new RemoveAdherentForAllFutureInvitationEventsCommand($membership->getAdherentUuid(), committeeId: $membership->getCommittee()->getId());

        $this->messageBus->dispatch($command);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            UserEvents::USER_UPDATE_COMMITTEE_PRIVILEGE => 'onCommitteeMembershipChange',
        ];
    }
}
