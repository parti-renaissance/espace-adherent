<?php

namespace App\Repository;

use App\Entity\Scope;
use App\Scope\AppEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ScopeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Scope::class);
    }

    public function findOneByCode(string $code): ?Scope
    {
        return $this->findOneBy(['code' => $code]);
    }

    /**
     * @return Scope[]|array
     */
    public function findGrantedForDataCorner(): array
    {
        return $this->createQueryBuilder('scope')
            ->where('FIND_IN_SET(:data_corner, scope.apps) > 0')
            ->setParameter('data_corner', AppEnum::DATA_CORNER)
            ->getQuery()
            ->getResult()
        ;
    }
}
