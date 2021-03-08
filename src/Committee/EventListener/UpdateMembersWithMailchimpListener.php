<?php

namespace App\Committee\EventListener;

use App\Committee\CommitteeEvent;
use App\Events;
use App\Mailchimp\Synchronisation\Command\AdherentChangeCommand;
use App\Repository\CommitteeMembershipRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class UpdateMembersWithMailchimpListener implements EventSubscriberInterface
{
    private $committeeMembershipRepository;
    private $bus;

    public function __construct(CommitteeMembershipRepository $committeeMembershipRepository, MessageBusInterface $bus)
    {
        $this->committeeMembershipRepository = $committeeMembershipRepository;
        $this->bus = $bus;
    }

    public static function getSubscribedEvents()
    {
        return [
            Events::COMMITTEE_APPROVED => 'onCommitteeApprove',
        ];
    }

    public function onCommitteeApprove(CommitteeEvent $event): void
    {
        if (($committee = $event->getCommittee())->isApproved()) {
            $members = $this->committeeMembershipRepository->findMembers($committee);
            foreach ($members as $member) {
                $this->bus->dispatch(new AdherentChangeCommand($member->getUuid(), $member->getEmailAddress()));
            }
        }
    }
}
