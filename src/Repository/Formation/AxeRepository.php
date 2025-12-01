<?php

declare(strict_types=1);

namespace App\Repository\Formation;

use App\Entity\Formation\Axe;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends \Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository<\App\Entity\Formation\Axe>
 */
class AxeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
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
