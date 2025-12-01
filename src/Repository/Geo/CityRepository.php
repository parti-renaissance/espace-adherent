<?php

declare(strict_types=1);

namespace App\Repository\Geo;

use App\Entity\Geo\City;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends \Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository<\App\Entity\Geo\City>
 */
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
