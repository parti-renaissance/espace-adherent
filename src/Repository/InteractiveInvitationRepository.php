<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;

abstract class InteractiveInvitationRepository extends EntityRepository
{
    public function countForExport(): int
    {
        return (int) $this->createQueryBuilder('i')
            ->select('COUNT(i)')
            ->orderBy('i.id', 'ASC')
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    public function findPaginatedForExport(int $page, int $perPage): array
    {
        return $this->createQueryBuilder('i')
            ->select('i')
            ->orderBy('i.id', 'ASC')
            ->getQuery()
            ->setFirstResult(($page - 1) * $perPage)
            ->setMaxResults($perPage)
            ->getResult()
        ;
    }
}
