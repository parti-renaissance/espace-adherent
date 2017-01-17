<?php

namespace AppBundle\Repository;

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
     *
     * @return bool
     */
    public function isCommitteeHost(string $adherentUuid): bool
    {
        $adherentUuid = Uuid::fromString($adherentUuid);

        $query = $this
            ->createQueryBuilder('cm')
            ->select('COUNT(cm.uuid)')
            ->where('cm.adherentUuid = :adherent')
            ->andWhere('cm.privilege = :privilege')
            ->setParameter('adherent', (string) $adherentUuid)
            ->setParameter('privilege', CommitteeMembership::COMMITTEE_HOST)
            ->getQuery()
        ;

        return (int) $query->getSingleScalarResult() >= 1;
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
}
