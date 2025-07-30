<?php

namespace App\Repository;

use App\Entity\AppAlert;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class AppAlertRepository extends ServiceEntityRepository
{
    use UuidEntityRepositoryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AppAlert::class);
    }

    /**
     * @return AppAlert[]
     */
    public function findAllActive(): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.beginAt <= :now')
            ->andWhere('a.endAt >= :now')
            ->andWhere('a.isActive = true')
            ->setParameter('now', new \DateTime())
            ->orderBy('a.beginAt', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }
}
