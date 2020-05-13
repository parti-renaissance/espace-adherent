<?php

namespace App\Repository;

use App\Entity\ElectionRound;
use App\Procuration\ElectionContext;
use App\Procuration\Exception\InvalidProcurationFlowException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;

class ElectionRoundRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ElectionRound::class);
    }

    public function createQueryBuilderFromElectionContext(ElectionContext $context): QueryBuilder
    {
        if (!$ids = $context->getCachedIds()) {
            throw new InvalidProcurationFlowException('The context has no ids.');
        }

        return $this->createQueryBuilder('r')
            ->leftJoin('r.election', 'e')
            ->where('e.id IN (:context)')
            ->setParameter('context', $ids)
            ->andWhere('r.date > :now')
            ->setParameter('now', new \DateTime())
            ->orderBy('r.date', 'ASC')
        ;
    }

    public function getAllRoundsAsChoices(): array
    {
        $results = $this->createQueryBuilder('r', 'r.id')
            ->select('r.id, r.label')
            ->orderBy('r.date', 'ASC')
            ->getQuery()
            ->getArrayResult()
        ;

        return $results;
    }

    public function getUpcomingElectionRounds(): array
    {
        $results = $this->createQueryBuilder('r', 'r.id')
            ->select('r.id, r.label')
            ->andWhere('r.date >= CURRENT_DATE()')
            ->orderBy('r.date', 'ASC')
            ->getQuery()
            ->getArrayResult()
        ;

        return $results;
    }
}
