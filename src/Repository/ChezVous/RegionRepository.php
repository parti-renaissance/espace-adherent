<?php

namespace App\Repository\ChezVous;

use App\Entity\ChezVous\Region;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class RegionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Region::class);
    }

    public function findOneByCode(string $code): ?Region
    {
        return $this->findOneBy(['code' => $code]);
    }
}
