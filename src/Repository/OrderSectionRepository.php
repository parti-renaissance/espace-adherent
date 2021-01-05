<?php

namespace App\Repository;

use App\Entity\OrderSection;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class OrderSectionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OrderSection::class);
    }

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
