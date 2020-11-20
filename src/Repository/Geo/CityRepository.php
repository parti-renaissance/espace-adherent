<?php

namespace App\Repository\Geo;

use App\Entity\Geo\City;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

final class CityRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, City::class);
    }

    /**
     * @return array<string, City>
     */
    public function findAllGroupedByCode(): array
    {
        return $this->createQueryBuilder('c', 'c.code')->getQuery()->getResult();
    }
}
