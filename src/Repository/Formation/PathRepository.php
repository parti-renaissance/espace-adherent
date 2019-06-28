<?php

namespace AppBundle\Repository\Formation;

use AppBundle\Entity\Formation\Path;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class PathRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Path::class);
    }

    /**
     * @return Path[]
     */
    public function findAllWithAxesAndModules(): array
    {
        return $this->createQueryBuilder('p')
            ->addSelect('axes', 'modules')
            ->innerJoin('p.axes', 'axes')
            ->innerJoin('axes.modules', 'modules')
            ->orderBy('p.id', 'ASC')
            ->getQuery()->getResult()
        ;
    }
}
