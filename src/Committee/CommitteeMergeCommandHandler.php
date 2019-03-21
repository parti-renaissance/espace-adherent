<?php

namespace AppBundle\Committee;

use AppBundle\Committee\Event\FollowCommitteeEvent;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\Administrator;
use AppBundle\Entity\Committee;
use AppBundle\Entity\CommitteeMembership;
use AppBundle\Entity\Reporting\CommitteeMembershipAction;
use AppBundle\Entity\Reporting\CommitteeMembershipHistory;
use AppBundle\Entity\Reporting\CommitteeMergeHistory;
use AppBundle\Events;
use AppBundle\Membership\UserEvents;
use AppBundle\Repository\CommitteeMembershipRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class CommitteeMergeCommandHandler
{
    private $dispatcher;
    private $em;
    private $committeeMembershipRepository;

    public function __construct(
        EventDispatcherInterface $dispatcher,
        EntityManagerInterface $em,
        CommitteeMembershipRepository $committeeMembershipRepository
    ) {
        $this->dispatcher = $dispatcher;
        $this->em = $em;
        $this->committeeMembershipRepository = $committeeMembershipRepository;
    }

    public function countNewMembers(CommitteeMergeCommand $committeeMergeCommand): int
    {
        $sourceCommittee = $committeeMergeCommand->getSourceCommittee();
        $destinationCommittee = $committeeMergeCommand->getDestinationCommittee();

        return $this
            ->committeeMembershipRepository
            ->findMembersToMerge($sourceCommittee, $destinationCommittee)
            ->count()
        ;
    }

    public function handle(CommitteeMergeCommand $committeeMergeCommand): void
    {
        $sourceCommittee = $committeeMergeCommand->getSourceCommittee();
        $destinationCommittee = $committeeMergeCommand->getDestinationCommittee();
        $administrator = $committeeMergeCommand->getMergedBy();

        $newFollowers = $this->committeeMembershipRepository->findMembersToMerge($sourceCommittee, $destinationCommittee);

        foreach ($newFollowers as $newFollower) {
            $this->em->persist($membership = $newFollower->followCommittee($destinationCommittee));

            $this->em->persist($this->createCommitteeMembershipHistory($membership));
        }

        $this
            ->committeeMembershipRepository
            ->findHostMemberships($sourceCommittee)
            ->map(function (CommitteeMembership $membership) {
                $membership->setPrivilege(CommitteeMembership::COMMITTEE_FOLLOWER);
            })
        ;

        $this
            ->committeeMembershipRepository
            ->findSupervisorMembership($sourceCommittee)
            ->setPrivilege(CommitteeMembership::COMMITTEE_FOLLOWER)
        ;

        $sourceCommittee->refused();

        $this->em->persist($this->createCommitteeMergeHistory($sourceCommittee, $destinationCommittee, $administrator));

        $this->em->flush();

        $newFollowers->map(function (Adherent $adherent) use ($destinationCommittee) {
            $this->dispatcher->dispatch(
                UserEvents::USER_UPDATE_COMMITTEE_PRIVILEGE,
                new FollowCommitteeEvent($adherent, $destinationCommittee)
            );
        });

        $this->dispatchCommitteeUpdate($sourceCommittee);
        $this->dispatchCommitteeUpdate($destinationCommittee);
    }

    private function dispatchCommitteeUpdate(Committee $committee): void
    {
        $this->dispatcher->dispatch(Events::COMMITTEE_UPDATED, new CommitteeEvent($committee));
    }

    private function createCommitteeMembershipHistory(CommitteeMembership $membership): CommitteeMembershipHistory
    {
        return new CommitteeMembershipHistory($membership, CommitteeMembershipAction::JOIN());
    }

    private function createCommitteeMergeHistory(
        Committee $sourceCommittee,
        Committee $destinationCommittee,
        Administrator $administrator
    ): CommitteeMergeHistory {
        return new CommitteeMergeHistory($sourceCommittee, $destinationCommittee, $administrator);
    }
}
