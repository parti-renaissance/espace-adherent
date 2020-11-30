<?php

namespace App\Repository;

use App\Entity\HomeBlock;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class HomeBlockRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, HomeBlock::class);
    }

    /**
     * @return HomeBlock[]
     */
    public function findHomeBlocks()
    {
        return $this->createQueryBuilder('h')
            ->select('h', 'm')
            ->leftJoin('h.media', 'm')
            ->orderBy('h.position', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }
}
