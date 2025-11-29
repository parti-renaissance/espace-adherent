<?php

declare(strict_types=1);

namespace App\Repository\Formation;

use App\Entity\Formation\Path;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class PathRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Path::class);
    }

    /**
     * @return Path[]
     */
    public function findAllWithAxesAndModules(): array
    {
        return $this->createQueryBuilder('path')
            ->addSelect('axes', 'modules')
            ->innerJoin('path.axes', 'axes')
            ->innerJoin('axes.modules', 'modules')
            ->orderBy('path.position', 'ASC')
            ->addOrderBy('path.id', 'ASC')
            ->addOrderBy('axes.position', 'ASC')
            ->addOrderBy('modules.position', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }
}
