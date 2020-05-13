<?php

namespace App\Repository;

use App\Entity\BaseEventCategory;

class EventGroupCategoryRepository extends BaseEventCategoryRepository
{
    public function findAllEnabledOrderedByName(): array
    {
        return $this
            ->createQueryBuilder('egc')
            ->addSelect('ec')
            ->join('egc.eventCategories', 'ec')
            ->where('egc.status = :status')
            ->andWhere('ec.status = :status')
            ->setParameter('status', BaseEventCategory::ENABLED)
            ->orderBy('egc.name', 'ASC')
            ->addOrderBy('ec.name', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }
}
