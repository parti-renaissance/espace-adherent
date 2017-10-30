<?php

namespace AppBundle\Repository;

use AppBundle\Collection\AdherentCollection;
use AppBundle\Collection\GroupMembershipCollection;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\GroupMembership;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class GroupMembershipRepository extends EntityRepository
{
    /**
     * Creates the query builder to fetch the membership relationship between
     * an adherent and a group.
     *
     * @param Adherent $adherent
     * @param string   $groupUuid
     *
     * @return QueryBuilder
     */
    private function createMembershipQueryBuilder(Adherent $adherent, string $groupUuid): QueryBuilder
    {
        $groupUuid = Uuid::fromString($groupUuid);

        $qb = $this
            ->createQueryBuilder('g')
            ->where('g.adherent = :adherent')
            ->andWhere('g.groupUuid = :group')
            ->setParameter('adherent', $adherent)
            ->setParameter('group', (string) $groupUuid)
        ;

        return $qb;
    }

    public function findGroupMembershipsForAdherent(Adherent $adherent): GroupMembershipCollection
    {
        $query = $this
            ->createQueryBuilder('g')
            ->where('g.adherent = :adherent')
            ->setParameter('adherent', $adherent)
            ->getQuery()
        ;

        return new GroupMembershipCollection($query->getResult());
    }

    public function findGroupMembership(Adherent $adherent, string $groupUuid): ?GroupMembership
    {
        $query = $this
            ->createMembershipQueryBuilder($adherent, $groupUuid)
            ->getQuery()
        ;

        return $query->getOneOrNullResult();
    }

    /**
     * Returns whether or not the given adherent is already an administrator of at least
     * one group.
     *
     * @param Adherent $adherent
     * @param string   $groupUuid
     *
     * @return bool
     */
    public function administrateGroup(Adherent $adherent, string $groupUuid = null): bool
    {
        $qb = $this->createQueryBuilder('g');

        $qb
            ->select('COUNT(g.uuid)')
            ->where('g.privilege = :privilege')
            ->andWhere('g.adherent = :adherent')
            ->setParameters([
                'adherent' => $adherent,
                'privilege' => GroupMembership::GROUP_ADMINISTRATOR,
            ])
        ;

        if ($groupUuid) {
            $groupUuid = Uuid::fromString($groupUuid);
            $qb
                ->andWhere('gm.groupUuid = :group')
                ->setParameter('group', (string) $groupUuid)
            ;
        }

        return (int) $qb->getQuery()->getSingleScalarResult() >= 1;
    }

    public function countAdministratorMembers(string $groupUuid): int
    {
        $groupUuid = Uuid::fromString($groupUuid);

        return $this->createQueryBuilder('g')
            ->select('COUNT(g.uuid)')
            ->where('g.groupUuid = :group')
            ->andWhere('g.privilege = :privilege')
            ->setParameters([
                'group' => (string) $groupUuid,
                'privilege' => GroupMembership::GROUP_ADMINISTRATOR,
            ])
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    /**
     * Returns the list of all administrator memberships of a group.
     *
     * @param string $groupUuid
     *
     * @return AdherentCollection
     */
    public function findAdministrators(string $groupUuid): AdherentCollection
    {
        return $this->findPriviledgedMembers($groupUuid, [GroupMembership::GROUP_ADMINISTRATOR]);
    }

    /**
     * Returns the list of all priviledged members of a group.
     *
     * @param string $groupUuid  The group UUID
     * @param array  $privileges An array of privilege constants (see {@link : GroupMembership}
     *
     * @return AdherentCollection
     */
    public function findPriviledgedMembers(string $groupUuid, array $privileges): AdherentCollection
    {
        $groupUuid = Uuid::fromString($groupUuid);

        $qb = $this->createQueryBuilder('gm');

        $query = $qb
            ->select('gm', 'adherent')
            ->leftJoin('gm.adherent', 'adherent')
            ->where('gm.groupUuid = :group')
            ->andWhere($qb->expr()->in('gm.privilege', $privileges))
            ->orderBy('gm.joinedAt', 'ASC')
            ->setParameter('group', (string) $groupUuid)
            ->getQuery()
        ;

        return $this->createAdherentCollection($query);
    }

    /**
     * Returns the list of all priviledged memberships of a group.
     *
     * @param string $groupUuid  The group UUID
     * @param array  $privileges An array of privilege constants (see {@link : GroupMembership}
     *
     * @return GroupMembershipCollection
     */
    private function findPriviledgedMemberships(string $groupUuid, array $privileges): GroupMembershipCollection
    {
        $groupUuid = Uuid::fromString($groupUuid);

        $qb = $this->createQueryBuilder('gm');

        $query = $qb
            ->where('gm.groupUuid = :group')
            ->andWhere($qb->expr()->in('gm.privilege', $privileges))
            ->orderBy('gm.joinedAt', 'ASC')
            ->setParameter('group', (string) $groupUuid)
            ->getQuery()
        ;

        return new GroupMembershipCollection($query->getResult());
    }

    /**
     * Returns the list of all members of a group.
     *
     * @param string $groupUuid The group UUID
     *
     * @return AdherentCollection
     */
    public function findMembers(string $groupUuid): AdherentCollection
    {
        return $this->createAdherentCollection($this->createGroupMembershipsQueryBuilder($groupUuid)->getQuery());
    }

    /**
     * @return string[]
     */
    public function findGroupsUuidByAdministratorFirstName(string $firstName): array
    {
        $qb = $this->createQueryBuilder('gm');

        $query = $qb
            ->select('gm.groupUuid')
            ->leftJoin('gm.adherent', 'a')
            ->where('LOWER(a.firstName) LIKE :firstName')
            ->andWhere('gm.privilege = :privilege')
            ->setParameters([
                'firstName' => '%'.strtolower($firstName).'%',
                'privilege' => GroupMembership::GROUP_ADMINISTRATOR,
            ])
            ->getQuery()
        ;

        return array_map(function (UuidInterface $uuid) {
            return $uuid->toString();
        }, array_column($query->getArrayResult(), 'groupUuid'));
    }

    /**
     * @return string[]
     */
    public function findGroupsUuidByAdministratorLastName(string $lastName): array
    {
        $qb = $this->createQueryBuilder('gm');

        $query = $qb
            ->select('gm.groupUuid')
            ->leftJoin('gm.adherent', 'a')
            ->where('LOWER(a.lastName) LIKE :lastName')
            ->andWhere('gm.privilege = :privilege')
            ->setParameters([
                'lastName' => '%'.strtolower($lastName).'%',
                'privilege' => GroupMembership::GROUP_ADMINISTRATOR,
            ])
            ->getQuery()
        ;

        return array_map(function (UuidInterface $uuid) {
            return $uuid->toString();
        }, array_column($query->getArrayResult(), 'groupUuid'));
    }

    /**
     * @return string[]
     */
    public function findGroupsUuidByAdministratorEmailAddress(string $emailAddress): array
    {
        $qb = $this->createQueryBuilder('gm');

        $query = $qb
            ->select('gm.groupUuid')
            ->leftJoin('gm.adherent', 'a')
            ->where('LOWER(a.emailAddress) LIKE :emailAddress')
            ->andWhere('gm.privilege = :privilege')
            ->setParameters([
                'emailAddress' => '%'.strtolower($emailAddress).'%',
                'privilege' => GroupMembership::GROUP_ADMINISTRATOR,
            ])
            ->getQuery()
        ;

        return array_map(function (UuidInterface $uuid) {
            return $uuid->toString();
        }, array_column($query->getArrayResult(), 'groupUuid'));
    }

    /**
     * Creates a QueryBuilder instance to fetch memberships of a group.
     *
     * @param string $groupUuid The group UUID for which the memberships to fetch belong
     * @param string $alias     The custom root alias for the query
     *
     * @return QueryBuilder
     */
    private function createGroupMembershipsQueryBuilder(string $groupUuid, string $alias = 'gm'): QueryBuilder
    {
        $groupUuid = Uuid::fromString($groupUuid);

        $qb = $this->createQueryBuilder($alias);

        $qb
            ->leftJoin($alias.'.adherent', 'a')
            ->where($alias.'.groupUuid = :group')
            ->orderBy('a.firstName', 'ASC')
            ->setParameter('group', (string) $groupUuid)
        ;

        return $qb;
    }

    /**
     * Returns the list of all group memberships of a group.
     *
     * @param string $groupUuid The group UUID
     *
     * @return GroupMembershipCollection
     */
    public function findGroupMemberships(string $groupUuid): GroupMembershipCollection
    {
        $query = $this
            ->createGroupMembershipsQueryBuilder($groupUuid)
            ->addSelect('a')
            ->getQuery()
        ;

        return new GroupMembershipCollection($query->getResult());
    }

    /**
     * Creates an AdherentCollection instance with the results of a Query.
     *
     * The query must return a list of GroupMembership entities.
     *
     * @param Query $query The query to execute
     *
     * @return AdherentCollection
     */
    private function createAdherentCollection(Query $query): AdherentCollection
    {
        return new AdherentCollection(
            array_map(
                function (GroupMembership $membership) {
                    return $membership->getAdherent();
                },
                $query->getResult()
            )
        );
    }
}
