<?php

namespace AppBundle\Committee;

use AppBundle\Entity\Committee;
use AppBundle\Entity\CommitteeMembership;
use AppBundle\Entity\Reporting\CommitteeMembershipAction;
use AppBundle\Entity\Reporting\CommitteeMembershipHistory;
use AppBundle\Entity\Reporting\CommitteeMergeHistory;
use AppBundle\Events;
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

        $sourceHostsMemberships = $this->committeeMembershipRepository->findHostMemberships($sourceCommittee);
        $sourceSupervisorMembership = $this->committeeMembershipRepository->findSupervisorMembership($sourceCommittee);
        $newFollowers = $this->committeeMembershipRepository->findMembersToMerge($sourceCommittee, $destinationCommittee);

        $this->em->beginTransaction();

        try {
            foreach ($newFollowers as $newFollower) {
                $this->em->persist($membership = $newFollower->followCommittee($destinationCommittee));

                $this->em->persist($this->createCommitteeMembershipHistory($membership));
            }

            foreach ($sourceHostsMemberships as $sourceHostMembership) {
                $sourceHostMembership->setPrivilege(CommitteeMembership::COMMITTEE_FOLLOWER);
            }

            $sourceSupervisorMembership->setPrivilege(CommitteeMembership::COMMITTEE_FOLLOWER);

            $sourceCommittee->refused();

            $this->em->persist($this->createCommitteeMergeHistory($sourceCommittee, $destinationCommittee));

            $this->em->flush();

            $this->em->commit();
        } catch (\Exception $exception) {
            $this->em->rollback();

            throw $exception;
        }

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
        Committee $destinationCommittee
    ): CommitteeMergeHistory {
        return new CommitteeMergeHistory($sourceCommittee, $destinationCommittee);
    }
}
