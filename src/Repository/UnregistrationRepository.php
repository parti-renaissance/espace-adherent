<?php

namespace App\Repository;

use App\Entity\Unregistration;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class UnregistrationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Unregistration::class);
    }

    public function countForExport(): int
    {
        return (int) $this->createQueryBuilder('i')
            ->select('COUNT(i)')
            ->orderBy('i.id', 'ASC')
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    public function findPaginatedForExport(int $page, int $perPage)
    {
        return $this->createQueryBuilder('i')
            ->select('i')
            ->orderBy('i.id', 'ASC')
            ->getQuery()
            ->setFirstResult(($page - 1) * $perPage)
            ->setMaxResults($perPage)
            ->getResult()
        ;
    }
}
