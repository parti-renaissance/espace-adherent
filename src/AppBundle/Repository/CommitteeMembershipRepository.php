<?php

namespace AppBundle\Repository;

use AppBundle\Collection\CommitteeMembershipCollection;
use AppBundle\Entity\CommitteeMembership;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Ramsey\Uuid\Uuid;

class CommitteeMembershipRepository extends EntityRepository
{
    /**
     * Returns whether or not the given adherent is already an host of at least
     * one committee.
     *
     * @param string $adherentUuid
     * @param string $committeeUuid
     *
     * @return bool
     */
    public function hostCommittee(string $adherentUuid, string $committeeUuid = null): bool
    {
        $adherentUuid = Uuid::fromString($adherentUuid);

        $qb = $this
            ->createQueryBuilder('cm')
            ->select('COUNT(cm.uuid)')
            ->where('cm.privilege = :privilege')
            ->andWhere('cm.adherentUuid = :adherent')
            ->setParameter('adherent', (string) $adherentUuid)
            ->setParameter('privilege', CommitteeMembership::COMMITTEE_HOST)
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
     * Returns whether or not an adherent is already member of a committee.
     *
     * @param string $adherentUuid
     * @param string $committeeUuid
     *
     * @return bool
     */
    public function isMemberOf(string $adherentUuid, string $committeeUuid): bool
    {
        $query = $this
            ->createMembershipQueryBuilder($adherentUuid, $committeeUuid)
            ->select('COUNT(cm.uuid)')
            ->getQuery()
        ;

        return 1 === (int) $query->getSingleScalarResult();
    }

    /**
     * Finds all the memberships for an adherent.
     *
     * @param string $adherentUuid
     *
     * @return CommitteeMembershipCollection
     */
    public function findMemberships(string $adherentUuid): CommitteeMembershipCollection
    {
        $adherentUuid = Uuid::fromString($adherentUuid);

        $query = $this
            ->createQueryBuilder('cm')
            ->where('cm.adherentUuid = :adherent')
            ->setParameter('adherent', (string) $adherentUuid)
            ->getQuery()
        ;

        return new CommitteeMembershipCollection($query->getResult());
    }

    /**
     * Finds the membership relationship between an adherent and a committee.
     *
     * @param string $adherentUuid
     * @param string $committeeUuid
     *
     * @return CommitteeMembership|null
     */
    public function findMembership(string $adherentUuid, string $committeeUuid)
    {
        $query = $this
            ->createMembershipQueryBuilder($adherentUuid, $committeeUuid)
            ->getQuery()
        ;

        return $query->getOneOrNullResult();
    }

    /**
     * Creates the query builder to fetch the membership relationship between
     * an adherent and a committee.
     *
     * @param string $adherentUuid
     * @param string $committeeUuid
     *
     * @return QueryBuilder
     */
    private function createMembershipQueryBuilder(string $adherentUuid, string $committeeUuid): QueryBuilder
    {
        $adherentUuid = Uuid::fromString($adherentUuid);
        $committeeUuid = Uuid::fromString($committeeUuid);

        $qb = $this
            ->createQueryBuilder('cm')
            ->where('cm.adherentUuid = :adherent')
            ->andWhere('cm.committeeUuid = :committee')
            ->setParameter('adherent', (string) $adherentUuid)
            ->setParameter('committee', (string) $committeeUuid)
        ;

        return $qb;
    }

    /**
     * Returns the number of members (hosts and followers) for the given committee.
     *
     * @param string $committeeUuid
     *
     * @return int
     */
    public function countMembers(string $committeeUuid): int
    {
        $committeeUuid = Uuid::fromString($committeeUuid);

        $query = $this
            ->createQueryBuilder('cm')
            ->select('COUNT(cm.uuid)')
            ->where('cm.committeeUuid = :committee')
            ->setParameter('committee', (string) $committeeUuid)
            ->getQuery()
        ;

        return (int) $query->getSingleScalarResult();
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

        $query = $this
            ->createQueryBuilder('cm')
            ->select('COUNT(cm.uuid)')
            ->where('cm.committeeUuid = :committee')
            ->andWhere('cm.privilege = :privilege')
            ->setParameter('committee', (string) $committeeUuid)
            ->setParameter('privilege', CommitteeMembership::COMMITTEE_HOST)
            ->getQuery()
        ;

        return (int) $query->getSingleScalarResult();
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
            $privileges[] = CommitteeMembership::COMMITTEE_HOST;
        }

        return $this->findPriviledgedMemberships($committeeUuid, $privileges);
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
}
