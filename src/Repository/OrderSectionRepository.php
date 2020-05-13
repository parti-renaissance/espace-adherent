<?php

namespace App\Repository;

use App\Entity\OrderSection;
use Doctrine\ORM\EntityRepository;

class OrderSectionRepository extends EntityRepository
{
    /**
     * @return OrderSection[]
     */
    public function findAllOrderedByPosition(): array
    {
        return $this
            ->createQueryBuilder('s')
            ->select('s', 'a')
            ->leftJoin('s.articles', 'a')
            ->orderBy('s.position', 'ASC')
            ->getQuery()
            ->getResult()
            ;
    }
}
