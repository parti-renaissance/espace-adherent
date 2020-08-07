<?php

namespace App\Repository\TerritorialCouncil;

use App\Entity\ReferentTag;
use App\Entity\TerritorialCouncil\TerritorialCouncil;
use App\Entity\VotingPlatform\Designation\Designation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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
}
