<?php

namespace App\Repository;

use App\Entity\BaseEventCategory;
use Doctrine\ORM\EntityRepository;

abstract class BaseEventCategoryRepository extends EntityRepository
{
    public function findAllEnabledOrderedByName(): array
    {
        return $this
            ->createQueryBuilder('c')
            ->where('c.status = :status')
            ->setParameter('status', BaseEventCategory::ENABLED)
            ->orderBy('c.name', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }
}
