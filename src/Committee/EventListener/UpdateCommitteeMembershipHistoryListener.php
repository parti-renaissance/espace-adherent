<?php

declare(strict_types=1);

namespace App\Committee\EventListener;

use App\Committee\Event\CommitteeMembershipEventInterface;
use App\Committee\Event\FollowCommitteeEvent;
use App\Entity\Reporting\CommitteeMembershipAction;
use App\Entity\Reporting\CommitteeMembershipHistory;
use App\Membership\UserEvents;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UpdateCommitteeMembershipHistoryListener implements EventSubscriberInterface
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [UserEvents::USER_UPDATE_COMMITTEE_PRIVILEGE => 'onUserUpdateCommitteePrivilege'];
    }

    public function onUserUpdateCommitteePrivilege(CommitteeMembershipEventInterface $event): void
    {
        $this->entityManager->persist(new CommitteeMembershipHistory(
            $event->getCommitteeMembership(),
            $event instanceof FollowCommitteeEvent ? CommitteeMembershipAction::JOIN() : CommitteeMembershipAction::LEAVE(),
        ));

        $this->entityManager->flush();
    }
}
