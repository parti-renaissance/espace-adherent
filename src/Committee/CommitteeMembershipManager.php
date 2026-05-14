<?php

declare(strict_types=1);

namespace App\Committee;

use App\Committee\Event\FollowCommitteeEvent;
use App\Committee\Event\UnfollowCommitteeEvent;
use App\Entity\Adherent;
use App\Entity\Committee;
use App\Entity\CommitteeMembership;
use App\Entity\Reporting\CommitteeMembershipAction;
use App\Membership\UserEvents;
use App\Repository\CommitteeMembershipRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class CommitteeMembershipManager
{
    public function __construct(
        private readonly CommitteeMembershipRepository $committeeMembershipRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly EventDispatcherInterface $dispatcher,
    ) {
    }

    public function followCommittee(
        Adherent $adherent,
        Committee $committee,
        CommitteeMembershipTriggerEnum $trigger,
    ): void {
        if ($membership = $adherent->getCommitteeMembership()) {
            if ($membership->getCommittee()->getId() === $committee->getId()) {
                return;
            }

            $this->unfollowCommittee($membership);
        }

        if (!$this->committeeMembershipRepository->insertIfNotExists($adherent, $committee, $trigger)) {
            return;
        }

        $membership = $this->committeeMembershipRepository->findOneBy([
            'adherent' => $adherent,
            'committee' => $committee,
        ]);
        $adherent->setCommitteeMembership($membership);

        $committee->updateMembersCount(
            true,
            $adherent->isRenaissanceSympathizer(),
            $adherent->isRenaissanceAdherent(),
            $adherent->hasActiveMembership()
        );

        $this->entityManager->flush();

        $this->dispatcher->dispatch(new FollowCommitteeEvent($membership), UserEvents::USER_UPDATE_COMMITTEE_PRIVILEGE);
    }

    public function followCommitteesBulk(
        Committee $committee,
        array $adherents,
        CommitteeMembershipTriggerEnum $trigger,
    ): FollowCommitteesBulkResult {
        if (!$adherents) {
            return new FollowCommitteesBulkResult(0, [], []);
        }

        $committeeId = $committee->getId();

        /** @var array<int, Adherent> $adherentsById */
        $adherentsById = [];
        foreach ($adherents as $adherent) {
            $adherentsById[$adherent->getId()] = $adherent;
        }

        $existingByAdherent = $this->committeeMembershipRepository->findExistingMembershipsByAdherentIds(
            array_keys($adherentsById),
        );

        $oldMembershipIds = [];
        $leaveHistoryRows = [];
        $newMembershipRows = [];
        $joinHistoryRows = [];
        $removedMemberships = [];
        $newMemberships = [];

        $now = new \DateTime()->format('Y-m-d H:i:s');

        foreach ($adherentsById as $adherentId => $adherent) {
            $existing = $existingByAdherent[$adherentId] ?? null;

            if ($existing && $existing['committee_id'] === $committeeId) {
                continue;
            }

            if ($existing) {
                $oldMembershipIds[] = $existing['id'];
                $leaveHistoryRows[] = [
                    'committee_id' => $existing['committee_id'],
                    'adherent_uuid' => $adherent->getUuid()->toRfc4122(),
                    'action' => CommitteeMembershipAction::LEAVE,
                    'privilege' => $existing['privilege'],
                    'date' => $now,
                ];
                $removedMemberships[] = [
                    'uuid' => $adherent->getUuid(),
                    'committeeId' => $existing['committee_id'],
                ];
            }

            $newMembership = CommitteeMembership::createFollower($committee, $adherent, $trigger);
            $newMembershipRows[] = [
                'uuid' => $newMembership->getUuid()->toRfc4122(),
                'adherent_id' => $adherentId,
                'committee_id' => $committeeId,
                'privilege' => CommitteeMembership::COMMITTEE_FOLLOWER,
                'joined_at' => $newMembership->getJoinedAt()->format('Y-m-d H:i:s'),
                '`trigger`' => $trigger->value,
            ];
            $joinHistoryRows[] = [
                'committee_id' => $committeeId,
                'adherent_uuid' => $adherent->getUuid()->toRfc4122(),
                'action' => CommitteeMembershipAction::JOIN,
                'privilege' => CommitteeMembership::COMMITTEE_FOLLOWER,
                'date' => $now,
            ];
            $newMemberships[] = [
                'uuid' => $adherent->getUuid(),
                'committeeId' => $committeeId,
            ];
        }

        $newMembershipCount = $this->committeeMembershipRepository->bulkApplyMembershipChanges(
            $oldMembershipIds,
            $leaveHistoryRows,
            $newMembershipRows,
            $joinHistoryRows,
        );

        return new FollowCommitteesBulkResult($newMembershipCount, $newMemberships, $removedMemberships);
    }

    /**
     * @return CommitteeMembership[]
     */
    public function getCommitteeMemberships(Committee $committee): array
    {
        return $this->committeeMembershipRepository->findCommitteeMemberships($committee);
    }

    public function unfollowCommittee(CommitteeMembership $membership): void
    {
        $adherent = $membership->getAdherent();
        $adherent->setCommitteeMembership(null);
        $this->entityManager->remove($membership);

        $membership->getCommittee()->updateMembersCount(
            false,
            $adherent->isRenaissanceSympathizer(),
            $adherent->isRenaissanceAdherent(),
            $adherent->hasActiveMembership()
        );

        $this->entityManager->flush();

        $this->dispatcher->dispatch(new UnfollowCommitteeEvent($membership), UserEvents::USER_UPDATE_COMMITTEE_PRIVILEGE);
    }
}
