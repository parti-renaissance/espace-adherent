<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Election;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

class ElectionRepository extends EntityRepository
{
    public function createAllComingNextByRoundDateQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('e')
            ->leftJoin('e.rounds', 'r')
            ->where('r.date > :now')
            ->setParameter('now', new \DateTime())
            ->orderBy('r.date', 'ASC')
        ;
    }

    public function findComingNextElection(): ?Election
    {
        $elections = $this->createAllComingNextByRoundDateQueryBuilder()
            ->setMaxResults(1)
            ->getQuery()
            ->getResult()
        ;

        return $elections[0] ?? null;
    }

    /**
     * @return Election[]
     */
    public function findAllComingNextByRoundDate(): array
    {
        return $this->createAllComingNextByRoundDateQueryBuilder()
            ->getQuery()
            ->getResult()
        ;
    }
}
