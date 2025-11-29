<?php

declare(strict_types=1);

namespace App\Committee\EventListener;

use App\Committee\Event\ApproveCommitteeEvent;
use App\Mailchimp\Synchronisation\Command\AdherentChangeCommand;
use App\Repository\CommitteeMembershipRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class UpdateMembersWithMailchimpListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly CommitteeMembershipRepository $committeeMembershipRepository,
        private readonly MessageBusInterface $bus,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [ApproveCommitteeEvent::class => 'onCommitteeApprove'];
    }

    public function onCommitteeApprove(ApproveCommitteeEvent $event): void
    {
        if (($committee = $event->getCommittee())->isApproved()) {
            $members = $this->committeeMembershipRepository->findMembers($committee);
            foreach ($members as $member) {
                $this->bus->dispatch(new AdherentChangeCommand($member->getUuid(), $member->getEmailAddress()));
            }
        }
    }
}
