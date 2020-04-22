<?php

namespace AppBundle\Committee;

use AppBundle\Collection\CommitteeMembershipCollection;
use AppBundle\Committee\Event\FollowCommitteeEvent;
use AppBundle\Committee\Event\UnfollowCommitteeEvent;
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

        $mergedMemberships = [];
        foreach ($newFollowers as $newFollower) {
            $this->em->persist($membership = $newFollower->followCommittee($destinationCommittee));

            $this->em->persist($this->createCommitteeMembershipHistory($membership, CommitteeMembershipAction::JOIN()));

            $mergedMemberships[] = $membership;
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

        $this->em->persist($this->createCommitteeMergeHistory(
            $sourceCommittee,
            $destinationCommittee,
            $mergedMemberships,
            $administrator
        ));

        $this->em->flush();

        $newFollowers->map(function (Adherent $adherent) use ($destinationCommittee) {
            $this->dispatcher->dispatch(
                UserEvents::USER_UPDATE_COMMITTEE_PRIVILEGE,
                new FollowCommitteeEvent($adherent, $destinationCommittee)
            );
        });

        $this->dispatchCommitteeUpdate($sourceCommittee);
        $this->dispatchCommitteeUpdate($destinationCommittee);

        $votingMembership = $this->committeeMembershipRepository->findVotingMemberships($sourceCommittee);
        $adherents = array_map(static function (CommitteeMembership $membership) {
            $membership->removeCandidacy();
            $membership->disableVote();

            return $membership->getAdherent();
        }, $votingMembership);

        $this->em->flush();

        $this->committeeMembershipRepository->enableVoteStatusForAdherents($destinationCommittee, $adherents);
    }

    public function revert(CommitteeMergeHistory $committeeMergeHistory, Administrator $administrator): void
    {
        $sourceCommittee = $committeeMergeHistory->getSourceCommittee();
        $destinationCommittee = $committeeMergeHistory->getDestinationCommittee();
        $mergedMemberships = $committeeMergeHistory->getMergedMemberships();

        $sourceCommittee->approved();

        $this->revertDestinationCommitteeMemberships($destinationCommittee, $mergedMemberships);
        $this->revertSourceCommitteeMemberships($sourceCommittee);
        $this->revertSourceCommitteeVotingMemberships($sourceCommittee, $mergedMemberships);

        $committeeMergeHistory->revert($administrator);

        $this->em->flush();

        $this->dispatchCommitteeUpdate($sourceCommittee);
        $this->dispatchCommitteeUpdate($destinationCommittee);

        foreach ($mergedMemberships as $membership) {
            $this->dispatcher->dispatch(
                UserEvents::USER_UPDATE_COMMITTEE_PRIVILEGE,
                new UnfollowCommitteeEvent($membership->getAdherent(), $destinationCommittee)
            );
        }
    }

    private function revertDestinationCommitteeMemberships(
        Committee $committee,
        CommitteeMembershipCollection $memberships
    ): void {
        foreach ($memberships as $membership) {
            $committee->decrementMembersCount();

            $this->em->remove($membership);

            $this->em->persist($this->createCommitteeMembershipHistory($membership, CommitteeMembershipAction::LEAVE()));
        }
    }

    private function revertSourceCommitteeMemberships(Committee $committee): void
    {
        /** @var CommitteeMembership[]|CommitteeMembershipCollection $memberships */
        $memberships = $this->committeeMembershipRepository->findCommitteeMemberships($committee);

        foreach ($memberships as $membership) {
            $adherent = $membership->getAdherent();

            if ($committee->getCreatedBy() === $adherent->getUuidAsString()) {
                $membership->setPrivilege(CommitteeMembership::COMMITTEE_SUPERVISOR);
            }
        }
    }

    private function revertSourceCommitteeVotingMemberships(
        Committee $committee,
        CommitteeMembershipCollection $memberships
    ): void {
        $votingMemberships = $memberships->filter(static function (CommitteeMembership $membership) {
            return $membership->isVotingCommittee();
        });

        $votingMergedAdherents = array_map(static function (CommitteeMembership $membership) {
            return $membership->getAdherent();
        }, $votingMemberships->toArray());

        $this->committeeMembershipRepository->enableVoteStatusForAdherents($committee, $votingMergedAdherents);
    }

    private function dispatchCommitteeUpdate(Committee $committee): void
    {
        $this->dispatcher->dispatch(Events::COMMITTEE_UPDATED, new CommitteeEvent($committee));
    }

    private function createCommitteeMembershipHistory(
        CommitteeMembership $membership,
        CommitteeMembershipAction $action
    ): CommitteeMembershipHistory {
        return new CommitteeMembershipHistory($membership, $action);
    }

    private function createCommitteeMergeHistory(
        Committee $sourceCommittee,
        Committee $destinationCommittee,
        array $mergedMemberships,
        Administrator $administrator
    ): CommitteeMergeHistory {
        return new CommitteeMergeHistory($sourceCommittee, $destinationCommittee, $mergedMemberships, $administrator);
    }
}
