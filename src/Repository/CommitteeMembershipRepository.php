<?php

namespace AppBundle\Repository;

use AppBundle\Collection\AdherentCollection;
use AppBundle\Collection\CommitteeMembershipCollection;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\CommitteeMembership;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class CommitteeMembershipRepository extends EntityRepository
{
    /**
     * Returns whether or not the given adherent is already an host of at least
     * one committee.
     *
     * @param Adherent $adherent
     * @param string   $committeeUuid
     *
     * @return bool
     */
    public function hostCommittee(Adherent $adherent, string $committeeUuid = null): bool
    {
        $qb = $this->createQueryBuilder('cm');

        $qb
            ->select('COUNT(cm.uuid)')
            ->where($qb->expr()->in('cm.privilege', CommitteeMembership::getHostPrivileges()))
            ->andWhere('cm.adherent = :adherent')
            ->setParameter('adherent', $adherent)
        ;

        if ($committeeUuid) {
            $committeeUuid = Uuid::fromString($committeeUuid);
            $qb
                ->andWhere('cm.committeeUuid = :committee')
                ->setParameter('committee', (string) $committeeUuid)
            ;
        }

        return (int) $qb->getQuery()->getSingleScalarResult() >= 1;
    }

    /**
     * Returns whether or not the given adherent is already the supervisor of at
     * least one committee.
     *
     * @param Adherent $adherent
     * @param string   $committeeUuid
     *
     * @return bool
     */
    public function superviseCommittee(Adherent $adherent, string $committeeUuid = null)
    {
        $qb = $this->createQueryBuilder('cm');

        $qb
            ->select('COUNT(cm.uuid)')
            ->where('cm.privilege = :supervisor')
            ->andWhere('cm.adherent = :adherent')
            ->setParameter('adherent', $adherent)
            ->setParameter('supervisor', CommitteeMembership::COMMITTEE_SUPERVISOR)
        ;

        if ($committeeUuid) {
            $committeeUuid = Uuid::fromString($committeeUuid);
            $qb
                ->andWhere('cm.committeeUuid = :committee')
                ->setParameter('committee', (string) $committeeUuid)
            ;
        }

        return (int) $qb->getQuery()->getSingleScalarResult() >= 1;
    }

    public function findMemberships(Adherent $adherent): CommitteeMembershipCollection
    {
        $query = $this
            ->createQueryBuilder('cm')
            ->where('cm.adherent = :adherent')
            ->setParameter('adherent', $adherent)
            ->getQuery()
        ;

        return new CommitteeMembershipCollection($query->getResult());
    }

    public function findMembership(Adherent $adherent, string $committeeUuid): ?CommitteeMembership
    {
        $query = $this
            ->createMembershipQueryBuilder($adherent, $committeeUuid)
            ->getQuery()
        ;

        return $query->getOneOrNullResult();
    }

    /**
     * Creates the query builder to fetch the membership relationship between
     * an adherent and a committee.
     *
     * @param Adherent $adherent
     * @param string   $committeeUuid
     *
     * @return QueryBuilder
     */
    private function createMembershipQueryBuilder(Adherent $adherent, string $committeeUuid): QueryBuilder
    {
        $committeeUuid = Uuid::fromString($committeeUuid);

        $qb = $this
            ->createQueryBuilder('cm')
            ->where('cm.adherent = :adherent')
            ->andWhere('cm.committeeUuid = :committee')
            ->setParameter('adherent', $adherent)
            ->setParameter('committee', (string) $committeeUuid)
        ;

        return $qb;
    }

    /**
     * Returns the number of host members for the given committee.
     *
     * @param string $committeeUuid
     *
     * @return int
     */
    public function countHostMembers(string $committeeUuid): int
    {
        $committeeUuid = Uuid::fromString($committeeUuid);

        $qb = $this->createQueryBuilder('cm');

        $query = $qb
            ->select('COUNT(cm.uuid)')
            ->where('cm.committeeUuid = :committee')
            ->andWhere($qb->expr()->in('cm.privilege', CommitteeMembership::getHostPrivileges()))
            ->setParameter('committee', (string) $committeeUuid)
            ->getQuery()
        ;

        return (int) $query->getSingleScalarResult();
    }

    public function countSupervisorMembers(string $committeeUuid): int
    {
        $committeeUuid = Uuid::fromString($committeeUuid);

        return $this->createQueryBuilder('cm')
            ->select('COUNT(cm.uuid)')
            ->where('cm.committeeUuid = :committee')
            ->andWhere('cm.privilege = :privilege')
            ->setParameter('committee', (string) $committeeUuid)
            ->setParameter('privilege', CommitteeMembership::COMMITTEE_SUPERVISOR)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    /**
     * Returns the list of all hosts memberships of a committee.
     *
     * @param string $committeeUuid
     *
     * @return CommitteeMembershipCollection
     */
    public function findHostMemberships(string $committeeUuid): CommitteeMembershipCollection
    {
        return $this->findPriviledgedMemberships($committeeUuid, [CommitteeMembership::COMMITTEE_HOST]);
    }

    /**
     * Returns the list of all hosts memberships of a committee.
     *
     * @param string $committeeUuid
     *
     * @return AdherentCollection
     */
    public function findHostMembers(string $committeeUuid): AdherentCollection
    {
        return $this->findPriviledgedMembers($committeeUuid, CommitteeMembership::getHostPrivileges());
    }

    /**
     * Finds the list of all committee followers memberships.
     *
     * @param string $committeeUuid The committee UUID
     * @param bool   $includeHosts  Whether or not to include committee hosts as followers
     *
     * @return CommitteeMembershipCollection
     */
    public function findFollowerMemberships(string $committeeUuid, bool $includeHosts = true): CommitteeMembershipCollection
    {
        $privileges = [CommitteeMembership::COMMITTEE_FOLLOWER];
        if ($includeHosts) {
            $privileges = array_merge($privileges, CommitteeMembership::getHostPrivileges());
        }

        return $this->findPriviledgedMemberships($committeeUuid, $privileges);
    }

    /**
     * Finds the list of all committee followers.
     *
     * @param string $committeeUuid The committee UUID
     * @param bool   $includeHosts  Whether or not to include committee hosts as followers
     *
     * @return AdherentCollection
     */
    public function findFollowers(string $committeeUuid, bool $includeHosts = true): AdherentCollection
    {
        $privileges = [CommitteeMembership::COMMITTEE_FOLLOWER];
        if ($includeHosts) {
            $privileges = array_merge($privileges, CommitteeMembership::getHostPrivileges());
        }

        return $this->findPriviledgedMembers($committeeUuid, $privileges);
    }

    /**
     * Returns the list of all priviledged memberships of a committee.
     *
     * @param string $committeeUuid The committee UUID
     * @param array  $privileges    An array of privilege constants (see {@link : CommitteeMembership}
     *
     * @return CommitteeMembershipCollection
     */
    private function findPriviledgedMemberships(string $committeeUuid, array $privileges): CommitteeMembershipCollection
    {
        $committeeUuid = Uuid::fromString($committeeUuid);

        $qb = $this->createQueryBuilder('cm');

        $query = $qb
            ->where('cm.committeeUuid = :committee')
            ->andWhere($qb->expr()->in('cm.privilege', $privileges))
            ->orderBy('cm.joinedAt', 'ASC')
            ->setParameter('committee', (string) $committeeUuid)
            ->getQuery()
        ;

        return new CommitteeMembershipCollection($query->getResult());
    }

    /**
     * Returns the list of all priviledged members of a committee.
     *
     * @param string $committeeUuid The committee UUID
     * @param array  $privileges    An array of privilege constants (see {@link : CommitteeMembership}
     *
     * @return AdherentCollection
     */
    private function findPriviledgedMembers(string $committeeUuid, array $privileges): AdherentCollection
    {
        $committeeUuid = Uuid::fromString($committeeUuid);

        $qb = $this->createQueryBuilder('cm');

        $query = $qb
            ->select('cm', 'adherent')
            ->leftJoin('cm.adherent', 'adherent')
            ->where('cm.committeeUuid = :committee')
            ->andWhere($qb->expr()->in('cm.privilege', $privileges))
            ->orderBy('cm.joinedAt', 'ASC')
            ->setParameter('committee', (string) $committeeUuid)
            ->getQuery()
        ;

        return $this->createAdherentCollection($query);
    }

    /**
     * Returns the list of all members of a committee.
     *
     * @param string $committeeUuid The committee UUID
     *
     * @return AdherentCollection
     */
    public function findMembers(string $committeeUuid): AdherentCollection
    {
        return $this->createAdherentCollection($this->createCommitteeMembershipsQueryBuilder($committeeUuid)->getQuery());
    }

    /**
     * Returns the list of all committee memberships of a committee.
     *
     * @param string $committeeUuid The committee UUID
     *
     * @return CommitteeMembershipCollection
     */
    public function findCommitteeMemberships(string $committeeUuid): CommitteeMembershipCollection
    {
        $query = $this
            ->createCommitteeMembershipsQueryBuilder($committeeUuid)
            ->addSelect('a')
            ->getQuery()
        ;

        return new CommitteeMembershipCollection($query->getResult());
    }

    /**
     * Creates a QueryBuilder instance to fetch memberships of a committee.
     *
     * @param string $committeeUuid The committee UUID for which the memberships to fetch belong
     * @param string $alias         The custom root alias for the query
     *
     * @return QueryBuilder
     */
    private function createCommitteeMembershipsQueryBuilder(string $committeeUuid, string $alias = 'cm'): QueryBuilder
    {
        $committeeUuid = Uuid::fromString($committeeUuid);

        $qb = $this->createQueryBuilder($alias);

        $qb
            ->leftJoin($alias.'.adherent', 'a')
            ->where($alias.'.committeeUuid = :committee')
            ->orderBy('a.firstName', 'ASC')
            ->setParameter('committee', (string) $committeeUuid)
        ;

        return $qb;
    }

    /**
     * Creates an AdherentCollection instance with the results of a Query.
     *
     * The query must return a list of CommitteeMembership entities.
     *
     * @param Query $query The query to execute
     *
     * @return AdherentCollection
     */
    private function createAdherentCollection(Query $query): AdherentCollection
    {
        return new AdherentCollection(
            array_map(
                function (CommitteeMembership $membership) {
                    return $membership->getAdherent();
                },
                $query->getResult()
            )
        );
    }

    /**
     * @return string[]
     */
    public function findCommitteesUuidByHostFirstName(string $firstName): array
    {
        $qb = $this->createQueryBuilder('cm');

        $query = $qb
            ->select('cm.committeeUuid')
            ->leftJoin('cm.adherent', 'a')
            ->where('LOWER(a.firstName) LIKE :firstName')
            ->andWhere($qb->expr()->in('cm.privilege', CommitteeMembership::getHostPrivileges()))
            ->setParameter('firstName', '%'.strtolower($firstName).'%')
            ->getQuery()
        ;

        return array_map(function (UuidInterface $uuid) {
            return $uuid->toString();
        }, array_column($query->getArrayResult(), 'committeeUuid'));
    }

    /**
     * @return string[]
     */
    public function findCommitteesUuidByHostLastName(string $lastName): array
    {
        $qb = $this->createQueryBuilder('cm');

        $query = $qb
            ->select('cm.committeeUuid')
            ->leftJoin('cm.adherent', 'a')
            ->where('LOWER(a.lastName) LIKE :lastName')
            ->andWhere($qb->expr()->in('cm.privilege', CommitteeMembership::getHostPrivileges()))
            ->setParameter('lastName', '%'.strtolower($lastName).'%')
            ->getQuery()
        ;

        return array_map(function (UuidInterface $uuid) {
            return $uuid->toString();
        }, array_column($query->getArrayResult(), 'committeeUuid'));
    }

    /**
     * @return string[]
     */
    public function findCommitteesUuidByHostEmailAddress(string $emailAddress): array
    {
        $qb = $this->createQueryBuilder('cm');

        $query = $qb
            ->select('cm.committeeUuid')
            ->leftJoin('cm.adherent', 'a')
            ->where('LOWER(a.emailAddress) LIKE :emailAddress')
            ->andWhere($qb->expr()->in('cm.privilege', CommitteeMembership::getHostPrivileges()))
            ->setParameter('emailAddress', '%'.strtolower($emailAddress).'%')
            ->getQuery()
        ;

        return array_map(function (UuidInterface $uuid) {
            return $uuid->toString();
        }, array_column($query->getArrayResult(), 'committeeUuid'));
    }
}
