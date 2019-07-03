<?php

namespace AppBundle\Repository\ChezVous;

use AppBundle\Entity\ChezVous\City;
use AppBundle\Entity\ChezVous\Marker;
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
