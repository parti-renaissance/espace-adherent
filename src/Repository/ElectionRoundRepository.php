<?php

namespace AppBundle\Repository;

use AppBundle\Procuration\ElectionContext;
use AppBundle\Procuration\Exception\InvalidProcurationFlowException;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

class ElectionRoundRepository extends EntityRepository
{
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
