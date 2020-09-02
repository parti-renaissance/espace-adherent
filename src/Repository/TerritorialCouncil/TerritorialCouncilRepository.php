<?php

namespace App\Repository\TerritorialCouncil;

use App\Entity\Adherent;
use App\Entity\Committee;
use App\Entity\CommitteeMembership;
use App\Entity\ElectedRepresentative\Mandate;
use App\Entity\ElectedRepresentative\Zone;
use App\Entity\ReferentTag;
use App\Entity\TerritorialCouncil\TerritorialCouncil;
use App\Entity\VotingPlatform\Designation\Designation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Symfony\Bridge\Doctrine\RegistryInterface;

class TerritorialCouncilRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, TerritorialCouncil::class);
    }

    public function findOneByReferentTag(ReferentTag $referentTag): ?TerritorialCouncil
    {
        return $this->createQueryBuilder('tc')
            ->innerJoin('tc.referentTags', 'tag')
            ->where('tag.id = :tag')
            ->setParameter('tag', $referentTag)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * @return TerritorialCouncil[]
     */
    public function findAllWithoutStartedElection(Designation $designation, int $offset = 0, int $limit = 200): array
    {
        return $this->createQueryBuilder('tc')
            ->innerJoin('tc.referentTags', 'tag')
            ->leftJoin('tc.currentDesignation', 'd')
            ->where('(tc.currentDesignation IS NULL OR (d.voteEndDate IS NOT NULL AND d.voteEndDate < :date))')
            ->andWhere('tag IN (:tags)')
            ->setParameters([
                'date' => $designation->getCandidacyStartDate(),
                'tags' => $designation->getReferentTags(),
            ])
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findByMandates(array $mandates): array
    {
        return $this->createQueryBuilder('tc')
            ->leftJoin('tc.referentTags', 'tag')
            ->leftJoin(Zone::class, 'zone', Join::WITH, 'tag MEMBER OF zone.referentTags')
            ->leftJoin(Mandate::class, 'mandate', Join::WITH, 'mandate.zone = zone.id')
            ->where('mandate.id IN (:mandates)')
            ->setParameter('mandates', $mandates)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findForSupervisor(Adherent $adherent): array
    {
        return $this->createQueryBuilder('tc')
            ->leftJoin('tc.referentTags', 'tag')
            ->leftJoin(Committee::class, 'committee', Join::WITH, 'tag MEMBER OF committee.referentTags')
            ->leftJoin(CommitteeMembership::class, 'cm', Join::WITH, 'cm.committee = committee')
            ->where('cm.adherent = :adherent')
            ->andWhere('cm.privilege = :privilege')
            ->setParameter('adherent', $adherent)
            ->setParameter('privilege', CommitteeMembership::COMMITTEE_SUPERVISOR)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findByCommittees(array $committees): array
    {
        return $this->createQueryBuilder('tc')
            ->leftJoin('tc.referentTags', 'tag')
            ->leftJoin(Committee::class, 'committee', Join::WITH, 'tag MEMBER OF committee.referentTags')
            ->where('committee IN (:committees)')
            ->setParameter('committees', $committees)
            ->getQuery()
            ->getResult()
        ;
    }
}
