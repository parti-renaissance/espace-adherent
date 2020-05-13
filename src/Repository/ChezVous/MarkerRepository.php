<?php

namespace App\Repository\ChezVous;

use App\Entity\ChezVous\City;
use App\Entity\ChezVous\Marker;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class MarkerRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Marker::class);
    }

    public function findOneByCityAndType(City $city, string $type): ?Marker
    {
        return $this->findOneBy([
            'city' => $city,
            'type' => $type,
        ]);
    }
}
