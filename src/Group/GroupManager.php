<?php

namespace AppBundle\Group;

use AppBundle\Collection\GroupMembershipCollection;
use AppBundle\Entity\Adherent;
use AppBundle\Collection\AdherentCollection;
use AppBundle\Entity\Group;
use AppBundle\Entity\GroupFeedItem;
use AppBundle\Entity\GroupMembership;
use AppBundle\Repository\AdherentRepository;
use AppBundle\Repository\GroupFeedItemRepository;
use AppBundle\Repository\GroupMembershipRepository;
use AppBundle\Repository\GroupRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager;

class GroupManager
{
    private $registry;

    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
    }

    public function isPromotableAdministrator(Adherent $adherent, Group $group): bool
    {
        if (!$membership = $this->getGroupMembershipRepository()->findGroupMembership($adherent, $group->getUuid())) {
            return false;
        }

        return $membership->isFollower();
    }

    public function isDemotableAdministrator(Adherent $adherent, Group $group): bool
    {
        if (!$membership = $this->getGroupMembershipRepository()->findGroupMembership($adherent, $group->getUuid())) {
            return false;
        }

        return $membership->isAdministrator();
    }

    public function isGroupAdministrator(Adherent $adherent): bool
    {
        // Optimization to prevent a SQL query if the current adherent already
        // has a loaded list of related group memberships entities.
        if ($adherent->isAdministrator()) {
            return true;
        }

        return $this->getGroupMembershipRepository()->administrateGroup($adherent);
    }

    public function administrateGroup(Adherent $adherent, Group $group): bool
    {
        // Optimization to prevent a SQL query if the current adherent already
        // has a loaded list of related group memberships entities.
        if ($adherent->isAdministratorOf($group)) {
            return true;
        }

        return $this->getGroupMembershipRepository()->administrateGroup($adherent, $group->getUuid());
    }

    public function getAdherentGroups(Adherent $adherent): array
    {
        return $this->doGetAdherentGroups($adherent);
    }

    public function getAdherentGroupsAdministrator(Adherent $adherent): array
    {
        return $this->doGetAdherentGroups($adherent, true);
    }

    private function doGetAdherentGroups(Adherent $adherent, $onlyAdministrator = false): array
    {
        // Prevent SQL query if the adherent doesn't follow any groups yet.
        if (!count($memberships = $adherent->getGroupMemberships())) {
            return [];
        }

        if (true === $onlyAdministrator) {
            $memberships = $memberships->getGroupAdministratorMemberships();
        }

        $groups = $this
            ->getGroupRepository()
            ->findGroups($memberships->getGroupUuids(), GroupRepository::INCLUDE_UNAPPROVED)
            ->filter(function (Group $group) use ($adherent) {
                // Any approved group is kept.
                if ($group->isApproved()) {
                    return $group;
                }

                // However, an unapproved group is kept only if it was created by the adherent.
                if ($group->isCreatedBy($adherent->getUuid())) {
                    return $group;
                }
            });

        return $groups->toArray();
    }

    public function countGroupAdministrators(Group $group): int
    {
        return $this->getGroupMembershipRepository()->countAdministratorMembers($group->getUuid());
    }

    public function getGroupAdministrators(Group $group): AdherentCollection
    {
        return $this->getGroupMembershipRepository()->findAdministrators($group->getUuid());
    }

    public function getGroupCreator(Group $group): Adherent
    {
        return $this->getAdherentRepository()->findOneByUuid($group->getCreatedBy());
    }

    public function getGroupMembers(Group $group): AdherentCollection
    {
        return $this->getGroupMembershipRepository()->findMembers($group->getUuid());
    }

    public function getGroupFollowers(Group $group): AdherentCollection
    {
        return $this->getGroupMembershipRepository()->findPriviledgedMembers($group->getUuid(), [GroupMembership::GROUP_FOLLOWER]);
    }

    public function getGroupMemberships(Group $group): GroupMembershipCollection
    {
        return $this->getGroupMembershipRepository()->findGroupMemberships($group->getUuid());
    }

    public function getGroupMembership(Adherent $adherent, Group $group): ?GroupMembership
    {
        // Optimization to prevent a SQL query if the current adherent already
        // has a loaded list of related group memberships entities.
        if ($membership = $adherent->getGroupMembershipFor($group)) {
            return $membership;
        }

        return $this->getGroupMembershipRepository()->findGroupMembership($adherent, $group->getUuid());
    }

    /**
     * Promotes an adherent to be an administrator of a group.
     *
     * @param Adherent $adherent
     * @param Group    $group
     * @param bool     $flush
     */
    public function promote(Adherent $adherent, Group $group, bool $flush = true): void
    {
        $membership = $this->getGroupMembershipRepository()->findGroupMembership($adherent, $group->getUuid());
        $membership->promote();

        if ($flush) {
            $this->getManager()->flush();
        }
    }

    /**
     * Make an adherent to be a member of a group.
     *
     * @param Adherent $adherent
     * @param Group    $group
     * @param bool     $flush
     */
    public function demote(Adherent $adherent, Group $group, bool $flush = true): void
    {
        $membership = $this->getGroupMembershipRepository()->findGroupMembership($adherent, $group->getUuid());
        $membership->demote();

        if ($flush) {
            $this->getManager()->flush();
        }
    }

    /**
     * Approves one group.
     *
     * @param Group $group
     * @param bool  $flush
     */
    public function approveGroup(Group $group, bool $flush = true): void
    {
        $group->approved();

        if ($flush) {
            $this->getManager()->flush();
        }
    }

    /**
     * Refuses one group.
     *
     * @param Group $group
     * @param bool  $flush
     */
    public function refuseGroup(Group $group, bool $flush = true): void
    {
        $group->refused();

        if ($flush) {
            $this->getManager()->flush();
        }
    }

    public function isFollowingGroup(Adherent $adherent, Group $group): bool
    {
        return $this->getGroupMembership($adherent, $group) instanceof GroupMembership;
    }

    /**
     * Makes an adherent follow multiple groups at once.
     *
     * @param Adherent $adherent The follower
     * @param string[] $groups   An array of group UUIDs
     */
    public function followGroups(Adherent $adherent, array $groups): void
    {
        if (empty($groups)) {
            return;
        }

        foreach ($this->getGroupRepository()->findByUuid($groups) as $group) {
            if (!$this->isFollowingGroup($adherent, $group)) {
                $this->followGroup($adherent, $group, false);
            }
        }

        $this->getManager()->flush();
    }

    /**
     * Makes an adherent follow one group.
     *
     * @param Adherent $adherent The follower
     * @param Group    $group    The group to follow
     * @param bool     $flush    Whether or not to flush the transaction
     */
    public function followGroup(Adherent $adherent, Group $group, $flush = true): void
    {
        $manager = $this->getManager();
        $manager->persist($adherent->followGroup($group));

        if ($flush) {
            $manager->flush();
        }
    }

    /**
     * Makes an adherent unfollow one group.
     *
     * @param Adherent $adherent The follower
     * @param Group    $group    The group to follow
     * @param bool     $flush    Whether or not to flush the transaction
     */
    public function unfollowGroup(Adherent $adherent, Group $group, bool $flush = true): void
    {
        $membership = $this->getGroupMembershipRepository()->findGroupMembership($adherent, $group->getUuid());

        if ($membership) {
            $this->doUnfollowGroup($membership, $group, $flush);
        }
    }

    private function doUnfollowGroup(GroupMembership $membership, Group $group, bool $flush = true): void
    {
        $manager = $this->getManager();

        $manager->remove($membership);
        $group->decrementMembersCount();

        if ($flush) {
            $manager->flush();
        }
    }

    private function getManager(): ObjectManager
    {
        return $this->registry->getManager();
    }

    private function getGroupRepository(): GroupRepository
    {
        return $this->registry->getRepository(Group::class);
    }

    private function getGroupFeedItemRepository(): GroupFeedItemRepository
    {
        return $this->registry->getRepository(GroupFeedItem::class);
    }

    private function getGroupMembershipRepository(): GroupMembershipRepository
    {
        return $this->registry->getRepository(GroupMembership::class);
    }

    private function getAdherentRepository(): AdherentRepository
    {
        return $this->registry->getRepository(Adherent::class);
    }

    public function countApprovedGroups(): int
    {
        return $this->getGroupRepository()->countApprovedGroups();
    }

    public function changePrivilege(Adherent $adherent, Group $group, string $privilege): void
    {
        GroupMembership::checkPrivilege($privilege);

        if (!$groupMembership = $this->getGroupMembership($adherent, $group)) {
            return;
        }

        $groupMembership->setPrivilege($privilege);

        $this->getManager()->flush();
    }
}
