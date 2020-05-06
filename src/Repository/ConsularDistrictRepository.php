<?php

namespace App\Repository;

use App\Entity\ConsularDistrict;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class ConsularDistrictRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ConsularDistrict::class);
    }

    public function findByCode(string $code): ?ConsularDistrict
    {
        return $this->createQueryBuilder('cd')
            ->where('cd.code = :code')
            ->setParameter('code', $code)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
