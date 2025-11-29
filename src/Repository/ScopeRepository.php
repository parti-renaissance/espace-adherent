<?php

declare(strict_types=1);

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

    public function findCodesGrantedForDataCorner(): array
    {
        $codes = $this->createQueryBuilder('scope')
            ->select('scope.code')
            ->where('FIND_IN_SET(:data_corner, scope.apps) > 0')
            ->setParameter('data_corner', AppEnum::DATA_CORNER)
            ->getQuery()
            ->getArrayResult()
        ;

        return array_map('current', $codes);
    }
}
