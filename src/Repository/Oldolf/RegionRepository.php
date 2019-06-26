<?php

namespace AppBundle\Repository\Oldolf;

use AppBundle\Entity\Oldolf\Region;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class RegionRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Region::class);
    }

    public function findOneByCode(string $code): ?Region
    {
        return $this->findOneBy(['code' => $code]);
    }
}
