<?php

namespace App\Repository\Geo;

use App\Entity\Geo\City;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

final class CityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, City::class);
    }

    /**
     * @return City[]
     */
    public function findAllGroupedByCode(): array
    {
        return $this->createQueryBuilder('c', 'c.code')->getQuery()->getResult();
    }
}
