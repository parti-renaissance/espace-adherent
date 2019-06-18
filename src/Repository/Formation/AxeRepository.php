<?php

namespace AppBundle\Repository\Formation;

use AppBundle\Entity\Formation\Axe;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class AxeRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Axe::class);
    }

    /**
     * @return Axe[]
     */
    public function findAllWithModules(): array
    {
        return $this->createQueryBuilder('a')
            ->addSelect('modules')
            ->innerJoin('a.modules', 'modules')
            ->getQuery()
            ->getResult()
        ;
    }
}
