<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Election;
use Doctrine\ORM\EntityRepository;

class ElectionRepository extends EntityRepository
{
    public function findComingNextElection(): ?Election
    {
        $elections = $this->createQueryBuilder('e')
            ->leftJoin('e.rounds', 'r')
            ->where('r.date > :now')
            ->setParameter('now', new \DateTime())
            ->orderBy('r.date', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getResult()
        ;

        return $elections[0] ?? null;
    }
}
