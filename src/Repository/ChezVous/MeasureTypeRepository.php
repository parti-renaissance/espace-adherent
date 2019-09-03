<?php

namespace AppBundle\Repository\ChezVous;

use AppBundle\Entity\ChezVous\MeasureType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class MeasureTypeRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, MeasureType::class);
    }

    public function findOneByCode(string $code): ?MeasureType
    {
        return $this->findOneBy(['code' => $code]);
    }
}
